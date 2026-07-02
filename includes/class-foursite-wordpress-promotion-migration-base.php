<?php

/**
 * Shared logic for promotion import/export.
 *
 * Field discovery is driven by ACF itself: we ask ACF for the field groups
 * attached to the promotion post type and read each field's type. That single
 * source of truth tells us the full field list AND how each value must be
 * treated on migration (media reference, page reference, or container to
 * recurse into) -- so there is no hardcoded field-name list to maintain. Add a
 * field in the ACF UI and it is exported automatically.
 */
class Foursite_Wordpress_Promotion_Migration_Base {
	const POST_TYPE       = 'wordpress_promotion';
	const EXPORT_FORMAT   = 'fswp-export';
	const FORMAT_VERSION  = 2;
	const ASSET_SUBDIR    = 'fwp-assets/';

	/**
	 * Top-level ACF field definitions for the promotion post type.
	 * Cached per request. Returns [] if ACF is unavailable.
	 */
	protected function schema_fields() {
		static $cache = null;
		if ($cache !== null) {
			return $cache;
		}
		if (!function_exists('acf_get_field_groups') || !function_exists('acf_get_fields')) {
			$cache = [];
			return $cache;
		}

		$fields = [];
		$groups = acf_get_field_groups(['post_type' => self::POST_TYPE]);
		foreach ($groups as $group) {
			$group_fields = acf_get_fields($group['key']);
			if (is_array($group_fields)) {
				$fields = array_merge($fields, $group_fields);
			}
		}
		$cache = $fields;
		return $cache;
	}

	/** Top-level field defs indexed by field name. */
	protected function schema_fields_by_name() {
		$by_name = [];
		foreach ($this->schema_fields() as $field) {
			if (!empty($field['name'])) {
				$by_name[$field['name']] = $field;
			}
		}
		return $by_name;
	}

	protected function is_media_type($type) {
		return in_array($type, ['image', 'file', 'gallery'], true);
	}

	protected function is_page_type($type) {
		// Fields that store references to other posts/pages by ID.
		return in_array($type, ['relationship', 'post_object', 'page_link'], true);
	}

	protected function is_container_type($type) {
		return in_array($type, ['group', 'repeater'], true);
	}

	/**
	 * A reference field that specifically targets other promotions (e.g. the AB
	 * Test candidate fields). These must be remapped against the promos imported
	 * in the same batch, not against pages -- the referenced promo's ID changes
	 * on import, so it can only be resolved after every promo has been created.
	 */
	protected function is_promo_reference_field($field) {
		if (!$this->is_page_type($field['type'] ?? '')) {
			return false;
		}
		$post_types = $field['post_type'] ?? [];
		if (is_string($post_types)) {
			$post_types = $post_types === '' ? [] : [$post_types];
		}
		return in_array(self::POST_TYPE, (array) $post_types, true);
	}

	/** Whether a field, or anything nested inside it, references a promotion. */
	protected function field_contains_promo_ref($field) {
		if ($this->is_promo_reference_field($field)) {
			return true;
		}
		if ($this->is_container_type($field['type'] ?? '')) {
			foreach ($field['sub_fields'] ?? [] as $sub) {
				if ($this->field_contains_promo_ref($sub)) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Normalize an ACF value (raw or formatted) to a flat list of integer IDs.
	 * Handles: scalar ID, array of IDs, formatted ['ID' => n], array of those.
	 */
	protected function value_to_ids($value) {
		if (is_array($value)) {
			if (isset($value['ID'])) {
				return [(int) $value['ID']];
			}
			$ids = [];
			foreach ($value as $item) {
				if (is_array($item) && isset($item['ID'])) {
					$ids[] = (int) $item['ID'];
				} else if (is_numeric($item)) {
					$ids[] = (int) $item;
				}
			}
			return $ids;
		}
		return is_numeric($value) ? [(int) $value] : [];
	}

	/**
	 * Locate a field's value within a container. Raw ACF values (read with
	 * format_value = false) key top-level fields by name but nested group/
	 * repeater sub-values by field KEY, so we accept either.
	 */
	protected function value_key_for($field, $container) {
		if (!is_array($container)) {
			return null;
		}
		$name = $field['name'] ?? '';
		if ($name !== '' && array_key_exists($name, $container)) {
			return $name;
		}
		$key = $field['key'] ?? '';
		if ($key !== '' && array_key_exists($key, $container)) {
			return $key;
		}
		return null;
	}

	/**
	 * Walk a promotion's assembled ACF values (against the field defs) and
	 * collect every referenced media ID and page ID. Recurses into groups and
	 * repeaters, so an image nested inside a repeater row is found.
	 */
	protected function collect_reference_ids($fields, $values, array &$media_ids, array &$page_ids) {
		if (!is_array($values)) {
			return;
		}
		foreach ($fields as $field) {
			$k = $this->value_key_for($field, $values);
			if ($k === null) {
				continue;
			}
			$this->collect_field_ids($field, $values[$k], $media_ids, $page_ids);
		}
	}

	protected function collect_field_ids($field, $value, array &$media_ids, array &$page_ids) {
		$type = $field['type'] ?? '';

		if ($this->is_media_type($type)) {
			foreach ($this->value_to_ids($value) as $id) {
				$media_ids[$id] = true;
			}
		} else if ($this->is_page_type($type)) {
			foreach ($this->value_to_ids($value) as $id) {
				$page_ids[$id] = true;
			}
		} else if ($type === 'group' && is_array($value)) {
			$this->collect_reference_ids($field['sub_fields'] ?? [], $value, $media_ids, $page_ids);
		} else if ($type === 'repeater' && is_array($value)) {
			foreach ($value as $row) {
				if (is_array($row)) {
					$this->collect_reference_ids($field['sub_fields'] ?? [], $row, $media_ids, $page_ids);
				}
			}
		}
	}

	/**
	 * Return a copy of a promotion's ACF values with reference IDs remapped to
	 * local IDs (or dropped where no mapping exists). Each map is source_id =>
	 * local_id|null; passing null for a map leaves that category of field
	 * untouched -- used to remap media/pages first and promo references in a
	 * later pass, once every promo in the batch has a local ID.
	 */
	protected function remap_reference_ids($fields_by_name, $values, $media_map, $page_map, $promo_map = null) {
		if (!is_array($values)) {
			return $values;
		}
		foreach ($values as $name => $value) {
			if (isset($fields_by_name[$name])) {
				$values[$name] = $this->remap_field($fields_by_name[$name], $value, $media_map, $page_map, $promo_map);
			}
		}
		return $values;
	}

	protected function remap_field($field, $value, $media_map, $page_map, $promo_map = null) {
		$type = $field['type'] ?? '';

		if ($this->is_media_type($type)) {
			return $media_map === null ? $value : $this->remap_value($value, $media_map);
		}
		// Promo references must be checked before the generic page test, since
		// they are also post_object/relationship fields.
		if ($this->is_promo_reference_field($field)) {
			return $promo_map === null ? $value : $this->remap_value($value, $promo_map);
		}
		if ($this->is_page_type($type)) {
			return $page_map === null ? $value : $this->remap_value($value, $page_map);
		}
		if ($type === 'group' && is_array($value)) {
			foreach ($field['sub_fields'] ?? [] as $sub) {
				$k = $this->value_key_for($sub, $value);
				if ($k !== null) {
					$value[$k] = $this->remap_field($sub, $value[$k], $media_map, $page_map, $promo_map);
				}
			}
			return $value;
		}
		if ($type === 'repeater' && is_array($value)) {
			foreach ($value as $i => $row) {
				if (!is_array($row)) {
					continue;
				}
				foreach ($field['sub_fields'] ?? [] as $sub) {
					$k = $this->value_key_for($sub, $row);
					if ($k !== null) {
						$value[$i][$k] = $this->remap_field($sub, $row[$k], $media_map, $page_map, $promo_map);
					}
				}
			}
			return $value;
		}
		return $value;
	}

	/** Collect the source promo IDs referenced by promo-reference fields. */
	protected function collect_promo_ref_ids($fields, $values, array &$ids) {
		if (!is_array($values)) {
			return;
		}
		foreach ($fields as $field) {
			$k = $this->value_key_for($field, $values);
			if ($k === null) {
				continue;
			}
			$value = $values[$k];
			if ($this->is_promo_reference_field($field)) {
				foreach ($this->value_to_ids($value) as $id) {
					$ids[$id] = true;
				}
			} else if ($this->is_container_type($field['type'] ?? '') && is_array($value)) {
				$rows = ($field['type'] === 'repeater') ? $value : [$value];
				foreach ($rows as $row) {
					if (is_array($row)) {
						$this->collect_promo_ref_ids($field['sub_fields'] ?? [], $row, $ids);
					}
				}
			}
		}
	}

	/**
	 * Remap a single field's ID value(s). Preserves the shape (scalar stays
	 * scalar, list stays list) and drops references that could not be mapped.
	 */
	protected function remap_value($value, array $map) {
		if (is_array($value)) {
			$out = [];
			foreach ($this->value_to_ids($value) as $id) {
				$mapped = $map[$id] ?? null;
				if ($mapped) {
					$out[] = $mapped;
				}
			}
			return $out;
		}
		if (is_numeric($value)) {
			return $map[(int) $value] ?? null;
		}
		return $value;
	}

	protected function assets_dir() {
		$ud = wp_upload_dir();
		return trailingslashit($ud['basedir']) . self::ASSET_SUBDIR;
	}
}
