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
	 * Walk a promotion's assembled ACF values (against the field defs) and
	 * collect every referenced media ID and page ID. Recurses into groups and
	 * repeaters, so an image nested inside a repeater row is found.
	 */
	protected function collect_reference_ids($fields, $values, array &$media_ids, array &$page_ids) {
		if (!is_array($values)) {
			return;
		}
		foreach ($fields as $field) {
			$name = $field['name'] ?? '';
			if ($name === '' || !array_key_exists($name, $values)) {
				continue;
			}
			$this->collect_field_ids($field, $values[$name], $media_ids, $page_ids);
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
	 * Return a copy of a promotion's ACF values with every media/page ID
	 * remapped to the local site's IDs (or dropped where no mapping exists).
	 * $media_map / $page_map are source_id => local_id|null.
	 */
	protected function remap_reference_ids($fields_by_name, $values, array $media_map, array $page_map) {
		if (!is_array($values)) {
			return $values;
		}
		foreach ($values as $name => $value) {
			if (isset($fields_by_name[$name])) {
				$values[$name] = $this->remap_field($fields_by_name[$name], $value, $media_map, $page_map);
			}
		}
		return $values;
	}

	protected function remap_field($field, $value, array $media_map, array $page_map) {
		$type = $field['type'] ?? '';

		if ($this->is_media_type($type)) {
			return $this->remap_value($value, $media_map);
		}
		if ($this->is_page_type($type)) {
			return $this->remap_value($value, $page_map);
		}
		if ($type === 'group' && is_array($value)) {
			foreach ($field['sub_fields'] ?? [] as $sub) {
				$sub_name = $sub['name'] ?? '';
				if ($sub_name !== '' && array_key_exists($sub_name, $value)) {
					$value[$sub_name] = $this->remap_field($sub, $value[$sub_name], $media_map, $page_map);
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
					$sub_name = $sub['name'] ?? '';
					if ($sub_name !== '' && array_key_exists($sub_name, $row)) {
						$value[$i][$sub_name] = $this->remap_field($sub, $row[$sub_name], $media_map, $page_map);
					}
				}
			}
			return $value;
		}
		return $value;
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
