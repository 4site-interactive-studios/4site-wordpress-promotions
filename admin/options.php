<?php

/**
 * add_acf_menu_pages.
 *
 * Add custom option pages to the WordPRess admin with Acf
 *
 * @uses acf https://www.advancedcustomfields.com/
 * @uses acf_add_options_page https://www.advancedcustomfields.com/resources/acf_add_options_page/
 * @uses acf_add_options_sub_page https://www.advancedcustomfields.com/resources/acf_add_options_sub_page/
 */
function engrid_wordpress_promotion_menu_pages()
{
  acf_add_options_sub_page(array(
    'page_title' => 'Promotions Settings',
    'menu_title' => 'Settings',
    'parent_slug' => 'edit.php?post_type=wordpress_promotion',
    'update_button' => 'Save',
    'updated_message' => 'Global Promotions Settings Saved',
  ));
}
add_action('acf/init', 'engrid_wordpress_promotion_menu_pages', 1);
//wp-admin/edit.php?post_type=wordpress_promotion
/**
 * Hook: acf/init.
 *
 * @uses add_action() https://developer.wordpress.org/reference/functions/add_action/
 * @uses acf/init https://www.advancedcustomfields.com/resources/acf-init/
 */
// add_action('acf/init', 'foursite_wordpress_promotion_menu_pages');


// Register Promotion Post Type
function register_wordpress_promotion_post_type()
{
  $labels = array(
    'name'                  => _x('Promotions', 'Post Type General Name', '4site-wordpress-promotion'),
    'singular_name'         => _x('Promotion', 'Post Type Singular Name', '4site-wordpress-promotion'),
    'menu_name'             => __('Promotions', '4site-wordpress-promotion'),
    'name_admin_bar'        => __('Promotion', '4site-wordpress-promotion'),
    'archives'              => __('Promotion Archives', '4site-wordpress-promotion'),
    'attributes'            => __('Promotion Attributes', '4site-wordpress-promotion'),
    'parent_item_colon'     => __('Parent Promotion:', '4site-wordpress-promotion'),
    'all_items'             => __('All Promotions', '4site-wordpress-promotion'),
    'add_new_item'          => __('Add New Promotion', '4site-wordpress-promotion'),
    'add_new'               => __('Add New', '4site-wordpress-promotion'),
    'new_item'              => __('New Promotion', '4site-wordpress-promotion'),
    'edit_item'             => __('Edit Promotion', '4site-wordpress-promotion'),
    'update_item'           => __('Update Promotion', '4site-wordpress-promotion'),
    'view_item'             => __('View Promotion', '4site-wordpress-promotion'),
    'view_items'            => __('View Promotions', '4site-wordpress-promotion'),
    'search_items'          => __('Search Promotion', '4site-wordpress-promotion'),
    'not_found'             => __('Not found', '4site-wordpress-promotion'),
    'not_found_in_trash'    => __('Not found in Trash', '4site-wordpress-promotion'),
  );
  $args = array(
    'label'                 => __('Promotion', '4site-wordpress-promotion'),
    'description'           => __('Promotion', '4site-wordpress-promotion'),
    'labels'                => $labels,
    'supports'              => array('title'),
    'hierarchical'          => false,
    'public'                => false,
    'show_ui'               => true,
    'show_in_menu'          => true,
    'menu_position'         => 20,
    'menu_icon'             => 'dashicons-lightbulb',
    'show_in_admin_bar'     => true,
    'show_in_nav_menus'     => false,
    'can_export'            => false,
    'has_archive'           => false,
    'exclude_from_search'   => true,
    'publicly_queryable'    => false,
    'capability_type'       => 'page',
    'show_in_rest'          => false,
  );
  register_post_type('wordpress_promotion', $args);
}

add_action('init', 'register_wordpress_promotion_post_type', 0);

// Show raw titles on the promotions listing — straight quotes/dashes/ellipses
// often encode meaning (A/B variants, naming conventions) that wptexturize mangles.
add_action('current_screen', function ($screen) {
  if ($screen && $screen->id === 'edit-wordpress_promotion') {
    remove_filter('the_title', 'wptexturize');
  }
});

// Admin listing columns, sorting, and filtering for wordpress_promotion post type
add_filter('manage_wordpress_promotion_posts_columns', 'smashing_add_new_columns');
function smashing_add_new_columns($columns)
{
  unset($columns['date']);
  $columns['fwp_status'] = __('Status', 'smashing');
  $columns['fwp_schedule_start'] = __('Start', 'smashing');
  $columns['fwp_schedule_end'] = __('End', 'smashing');
  $columns['fwp_pages'] = __('Pages', 'smashing');
  $columns['custom_date'] = __('Published', 'smashing');
  $columns['post_id'] = __('Post ID', 'smashing');
  $columns['promotion_type'] = __('Type', 'smashing');
  $columns['trigger'] = __('Trigger', 'smashing');
  $columns['fwp_references'] = __('References', 'smashing');
  return $columns;
}

add_action('manage_wordpress_promotion_posts_custom_column', 'smashing_wordpress_promotion_column', 10, 2);
function smashing_wordpress_promotion_column($column, $post_id)
{
  $status = get_post_meta($post_id, 'engrid_lightbox_display', true);

  if ('fwp_status' === $column) {
    $fwp_status = '';
    $post_status = get_post_status($post_id);
    if (!in_array($post_status, ['publish', 'template'])) {
      $fwp_status = "Off - Not Published";
    } else {
      $display = get_field('engrid_lightbox_display', $post_id);
      if ($display == 'scheduled') {
        $start_date = get_field('engrid_start_date', $post_id);
        $end_date = get_field('engrid_end_date', $post_id);

        $current_date = new DateTime('now', new DateTimeZone('America/New_York'));
        $current_date = $current_date->format('Y-m-d H:i:s');

        if ($start_date > $current_date) {
          $fwp_status = "Scheduled - Upcoming";
        } else if ($end_date < $current_date) {
          $fwp_status = "Scheduled - Expired";
        } else {
          $fwp_status = "Scheduled - Active";
        }
      } else if ($display == 'turned-on') {
        $fwp_status = 'On';
      } else {
        $fwp_status = 'Off';
      }
    }

    if ($post_status == 'template') {
      $fwp_status = "Template";
    }
    echo $fwp_status;
  }

  if ('fwp_schedule_start' === $column) {
    $fwp_schedule = "--";
    $start_date = get_field('engrid_start_date', $post_id);
    if ($start_date) {
      $fwp_schedule = date('Y/m/d g:i a', strtotime($start_date));
      $display = get_field('engrid_lightbox_display', $post_id);
      if ($display != 'scheduled') {
        $fwp_schedule = "--";
      }
    }
    echo $fwp_schedule;
  }

  if ('fwp_schedule_end' === $column) {
    $fwp_schedule = "--";
    $end_date = get_field('engrid_end_date', $post_id);
    if ($end_date) {
      $fwp_schedule = date('Y/m/d g:i a', strtotime($end_date));
      $display = get_field('engrid_lightbox_display', $post_id);
      if ($display != 'scheduled') {
        $fwp_schedule = "--";
      }
    }
    echo $fwp_schedule;
  }

  if ('fwp_pages' === $column) {
    $whitelist = get_field('engrid_whitelist', $post_id);
    $blacklist = get_field('engrid_blacklist', $post_id);
    $show_on_page_ids = get_field('engrid_show_on', $post_id);
    $hide_on_page_ids = get_field('engrid_hide_on', $post_id);

    $fwp_pages = '';
    $fwp_show_on = '';
    $fwp_hide_on = '';

    if ($whitelist) {
      $fwp_show_on .= $whitelist;
    }
    if ($blacklist) {
      $fwp_hide_on .= $blacklist;
    }
    if ($show_on_page_ids) {
      if ($fwp_show_on) {
        $fwp_show_on .= "<br>";
      }
      $page_ids = implode(',', $show_on_page_ids);
      global $wpdb;
      $results = $wpdb->get_results("SELECT post_title, ID FROM {$wpdb->posts} WHERE ID IN ({$page_ids})");
      foreach ($results as $result) {
        $page_url = get_permalink($result->ID);
        $fwp_show_on .= "<a href='" . $page_url . "' target='_blank'>{$result->post_title}</a><br>";
      }
    }

    if ($hide_on_page_ids) {
      if ($fwp_hide_on) {
        $fwp_hide_on .= "<br>";
      }
      $page_ids = implode(', ', $hide_on_page_ids);
      global $wpdb;
      $results = $wpdb->get_results("SELECT post_title, ID FROM {$wpdb->posts} WHERE ID IN ({$page_ids})");
      foreach ($results as $result) {
        $page_url = get_permalink($result->ID);
        $fwp_hide_on .= "<a href='" . $page_url . "' target='_blank'>{$result->post_title}</a><br>";
      }
    }

    $fwp_pages = '';
    if ($fwp_show_on) {
      $fwp_pages .= "<strong>Show on:</strong><br>{$fwp_show_on}<br>";
    }
    if ($fwp_hide_on) {
      $fwp_pages .= "<strong>Hide on:</strong><br>{$fwp_hide_on}";
    }
    if (!$fwp_pages) {
      $fwp_pages = '<strong>Show on:</strong><br>All Pages';
    }

    echo $fwp_pages;
  }

  if ('engrid_start_date' === $column) {
    $start_date = strtotime(get_post_meta($post_id, 'engrid_start_date', true));

    if ($status == "scheduled") {
      echo date("m/d/Y", $start_date);
    } else {
      echo "--";
    }
  }

  if ('engrid_end_date' === $column) {
    $end_date = strtotime(get_post_meta($post_id, 'engrid_end_date', true));

    if ($status == "scheduled") {
      echo date("m/d/Y", $end_date);
    } else {
      echo "--";
    }
  }

  if ('promotion_type' === $column) {
    $promotion_type = get_post_meta($post_id, 'engrid_promotion_type', true);

    echo implode(" ", array_map("ucfirst", explode("_", $promotion_type)));
  }

  if ('trigger' === $column) {
    $trigger = get_post_meta($post_id, 'engrid_trigger_type', true);
    $pixels = get_post_meta($post_id, 'engrid_trigger_scroll_pixels', true);
    $seconds = get_post_meta($post_id, 'engrid_trigger_seconds', true);
    $percentage = get_post_meta($post_id, 'engrid_trigger_scroll_percentage', true);

    $promotion_type = get_post_meta($post_id, 'engrid_promotion_type', true);
    if ($promotion_type == "signup_lightbox") {
      $trigger = $seconds == 0 ? "0" : "seconds";
    }

    switch ($trigger) {
      case "0":
        echo "Immediately";
        break;
      case "seconds":
        echo "After $seconds seconds";
        break;
      case "px":
        echo "After scrolling $pixels pixels";
        break;
      case "%":
        echo "After scrolling $percentage% of the page";
        break;
      case "exit":
        echo "On exit";
        break;
      case "js":
        echo "Javascript Trigger";
        break;
    }
  }

  if ('custom_date' === $column) {
    echo get_the_date('Y/m/d') . ' at ' . get_the_date('g:i a');
  }

  if ('post_id' === $column) {
    echo $post_id;
  }

  if ('fwp_references' === $column) {
    global $wpdb;
    $referencing_ids = $wpdb->get_col($wpdb->prepare("
      SELECT DISTINCT pm.post_id
      FROM {$wpdb->postmeta} pm
      JOIN {$wpdb->postmeta} type_pm
        ON type_pm.post_id = pm.post_id
        AND type_pm.meta_key = 'engrid_promotion_type'
        AND type_pm.meta_value = 'ab_test'
      JOIN {$wpdb->posts} p
        ON p.ID = pm.post_id
        AND p.post_status != 'trash'
      WHERE pm.meta_value = %s
      AND pm.meta_key REGEXP '^ab_promotions_[0-9]+_(promotion|ad_blocker_promotion)$'
      AND pm.post_id != %d
    ", $post_id, $post_id));

    if (empty($referencing_ids)) {
      echo '--';
    } else {
      $links = [];
      foreach ($referencing_ids as $ref_id) {
        $title = get_the_title($ref_id);
        if (!$title) {
          $title = '(no title)';
        }
        $edit_link = get_edit_post_link($ref_id);
        $links[] = "<a href='" . esc_url($edit_link) . "'>" . esc_html($title) . "</a>";
      }
      echo implode('<br>', $links);
    }
  }
}

add_filter('manage_edit-wordpress_promotion_sortable_columns', 'smashing_wordpress_promotion_sortable_columns');
function smashing_wordpress_promotion_sortable_columns($columns)
{
  $columns['fwp_schedule_start'] = 'fwp_schedule_start';
  $columns['fwp_schedule_end'] = 'fwp_schedule_end';
  $columns['custom_date'] = 'custom_date';
  $columns['post_id'] = 'post_id';

  return $columns;
}

add_action('pre_get_posts', 'smashing_posts_orderby');
function smashing_posts_orderby($query)
{
  if (!is_admin() || !$query->is_main_query()) {
    return;
  }

  $orderby = $query->get('orderby');

  if ('fwp_schedule_start' === $orderby) {
    $query->set('meta_key', 'engrid_start_date');
    $query->set('orderby', 'meta_value');
  }

  if ('fwp_schedule_end' === $orderby) {
    $query->set('meta_key', 'engrid_end_date');
    $query->set('orderby', 'meta_value');
  }

  if ('engrid_lightbox_display' === $orderby) {
    $query->set('orderby', 'meta_value');
    $query->set('meta_key', 'engrid_lightbox_display');
  }

  if ('engrid_start_date' === $orderby) {
    $query->set('orderby', 'meta_value');
    $query->set('meta_key', 'engrid_start_date');
    $query->set('meta_type', 'date');
  }

  if ('engrid_end_date' === $orderby) {
    $query->set('orderby', 'meta_value');
    $query->set('meta_key', 'engrid_end_date');
    $query->set('meta_type', 'date');
  }

  if ('post_id' === $orderby) {
    $query->set('orderby', 'ID');
  }

  if ('custom_date' === $orderby) {
    $query->set('orderby', 'date');
  }
}

add_filter('posts_search', 'fwp_extend_search_to_references', 10, 2);
function fwp_extend_search_to_references($search, $query)
{
  if (empty($search) || !is_admin() || !$query->is_main_query()) {
    return $search;
  }
  if ($query->get('post_type') !== 'wordpress_promotion') {
    return $search;
  }
  $search_term = $query->get('s');
  if (empty($search_term)) {
    return $search;
  }
  global $wpdb;
  $like = '%' . $wpdb->esc_like(wp_unslash($search_term)) . '%';
  $subquery = $wpdb->prepare("
    SELECT DISTINCT pm_val.meta_value + 0
    FROM {$wpdb->postmeta} pm_val
    INNER JOIN {$wpdb->postmeta} pm_type ON pm_type.post_id = pm_val.post_id
      AND pm_type.meta_key = 'engrid_promotion_type'
      AND pm_type.meta_value = 'ab_test'
    INNER JOIN {$wpdb->posts} p_ref ON p_ref.ID = pm_val.post_id
      AND p_ref.post_status != 'trash'
      AND p_ref.post_title LIKE %s
    WHERE pm_val.meta_key REGEXP '^ab_promotions_[0-9]+_(promotion|ad_blocker_promotion)\$'
  ", $like);
  $extension = " OR ({$wpdb->posts}.ID IN ({$subquery}))";
  return preg_replace('/\)\s*$/', $extension . ') ', $search, 1);
}

add_filter('parse_query', 'fwp_filter_request_query', 10);
function fwp_filter_request_query($query)
{
  if (!(is_admin() && $query->is_main_query())) {
    return $query;
  }
  if (!('wordpress_promotion' === $query->query['post_type'] && !empty($_REQUEST['fwp_status']))) {
    return $query;
  }

  $post_ids = [];
  if ('on-scheduled' == $_REQUEST['fwp_status']) {
    $post_ids = array_merge($post_ids, fwp_get_post_ids_for_status('active'));
    $post_ids = array_merge($post_ids, fwp_get_post_ids_for_status('upcoming'));
    $post_ids = array_merge($post_ids, fwp_get_post_ids_for_status('on'));
  } else if ('off-expired' == $_REQUEST['fwp_status']) {
    $post_ids = array_merge($post_ids, fwp_get_post_ids_for_status('expired'));
    $post_ids = array_merge($post_ids, fwp_get_post_ids_for_status('off'));
  } else {
    $post_ids = fwp_get_post_ids_for_status($_REQUEST['fwp_status']);
  }
  $post_ids = array_unique($post_ids);
  if (count($post_ids) == 0) $post_ids = [0];
  $query->query_vars['post__in'] = $post_ids;
  $query->query_vars['orderby'] = 'post__in';
  return $query;
}

function fwp_get_post_ids_for_status($status)
{
  global $wpdb;
  $post_ids = [];

  $current_date = new DateTime('now', new DateTimeZone('America/New_York'));
  $current_date = $current_date->format('Y-m-d H:i:s');

  $joins = '';
  $conditions = '';

  if ($status == 'expired') {
    $joins = "
      join {$wpdb->postmeta} end_date_pm on p.ID = end_date_pm.post_id
    ";
    $conditions = "
      and p.post_status in ('publish')
      and active_status_pm.meta_key = 'engrid_lightbox_display'
      and active_status_pm.meta_value = 'scheduled'
      and end_date_pm.meta_key = 'engrid_end_date'
      and end_date_pm.meta_value < '{$current_date}'
    ";
  } else if ($status == 'on') {
    $joins = "
      join {$wpdb->postmeta} start_date_pm on p.ID = start_date_pm.post_id
      join {$wpdb->postmeta} end_date_pm on p.ID = end_date_pm.post_id
    ";
    $conditions = "
      and p.post_status in ('publish')
      and active_status_pm.meta_key = 'engrid_lightbox_display'
      and active_status_pm.meta_value = 'turned-on'
    ";
  } else if ($status == 'off') {
    $joins = "
      join {$wpdb->postmeta} start_date_pm on p.ID = start_date_pm.post_id
      join {$wpdb->postmeta} end_date_pm on p.ID = end_date_pm.post_id
    ";
    $conditions = "
      and active_status_pm.meta_key = 'engrid_lightbox_display'
      and (
        active_status_pm.meta_value = 'turned-off'
        or p.post_status not in ('publish', 'template')
      )
      and p.post_status <> 'template'
    ";
  } else if ($status == 'active') {
    $joins = "
      join {$wpdb->postmeta} start_date_pm on p.ID = start_date_pm.post_id
      join {$wpdb->postmeta} end_date_pm on p.ID = end_date_pm.post_id
    ";
    $conditions = "
      and p.post_status in ('publish')
      and active_status_pm.meta_key = 'engrid_lightbox_display'
      and active_status_pm.meta_value = 'scheduled'
      and start_date_pm.meta_key = 'engrid_start_date'
      and start_date_pm.meta_value < '{$current_date}'
      and end_date_pm.meta_key = 'engrid_end_date'
      and end_date_pm.meta_value > '{$current_date}'
    ";
  } else if ($status == 'upcoming') {
    $joins = "
      join {$wpdb->postmeta} start_date_pm on p.ID = start_date_pm.post_id
    ";
    $conditions = "
      and p.post_status in ('publish')
      and active_status_pm.meta_key = 'engrid_lightbox_display'
      and active_status_pm.meta_value = 'scheduled'
      and start_date_pm.meta_key = 'engrid_start_date'
      and start_date_pm.meta_value <> ''
      and start_date_pm.meta_value > '{$current_date}'
    ";
  }

  $query = "
    select distinct ID
    from {$wpdb->posts} p
    join {$wpdb->postmeta} active_status_pm on p.ID = active_status_pm.post_id
    {$joins}
    where p.post_type = 'wordpress_promotion'
    {$conditions}
  ";
  $post_ids = $wpdb->get_col($query);
  if (empty($post_ids)) $post_ids = [];
  return $post_ids;
}

function fwp_format_option($label, $key, $selected_key, $disabled = false)
{
  $selected_string = (!$disabled && $key == $selected_key) ? " selected" : "";
  $disabled_string = ($disabled) ? ' disabled' : '';
  return "<option value='{$key}'{$selected_string}{$disabled_string}>{$label}</option>";
}

add_action('restrict_manage_posts', 'fwp_table_filtering');
function fwp_table_filtering($post_type)
{
  if ($post_type !== 'wordpress_promotion') {
    return;
  }

  $current_fwp_status = (!empty($_GET['fwp_status'])) ? $_GET['fwp_status'] : '';
  $options = [];
  $options[] = fwp_format_option(__('Show all Promotions', 'foursite-wordpress-promotions'), '', $current_fwp_status);
  $options[] = fwp_format_option(__('On & Scheduled', 'foursite-wordpress-promotions'), 'on-scheduled', $current_fwp_status);
  $options[] = fwp_format_option(__('Off & Expired', 'foursite-wordpress-promotions'), 'off-expired', $current_fwp_status);
  $options[] = fwp_format_option('<hr><br><br>', '', $current_fwp_status, true);
  $options[] = fwp_format_option(__('Off', 'foursite-wordpress-promotions'), 'off', $current_fwp_status);
  $options[] = fwp_format_option(__('On', 'foursite-wordpress-promotions'), 'on', $current_fwp_status);
  $options[] = fwp_format_option(__('Scheduled - Expired', 'foursite-wordpress-promotions'), 'expired', $current_fwp_status);
  $options[] = fwp_format_option(__('Scheduled - Upcoming', 'foursite-wordpress-promotions'), 'upcoming', $current_fwp_status);
  $options[] = fwp_format_option(__('Scheduled - Active', 'foursite-wordpress-promotions'), 'active', $current_fwp_status);
  $options_string = implode('', $options);
  echo "<select class='' id='' name='fwp_status'>{$options_string}</select>";
}
