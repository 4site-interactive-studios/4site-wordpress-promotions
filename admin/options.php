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

// Add new columns to list page
add_filter('manage_wordpress_promotion_posts_columns', 'smashing_add_new_columns');
function smashing_add_new_columns($columns)
{
  unset($columns['date']);
  $columns['custom_date'] = __('Published', 'smashing');
  $columns['status'] = __('Status', 'smashing');
  $columns['post_id'] = __('Post ID', 'smashing');
  $columns['promotion_type'] = __('Type', 'smashing');
  $columns['trigger'] = __('Trigger', 'smashing');
  return $columns;
}

add_action('manage_wordpress_promotion_posts_custom_column', 'smashing_wordpress_promotion_column', 10, 2);
function smashing_wordpress_promotion_column($column, $post_id)
{
  $status = get_post_meta($post_id, 'engrid_lightbox_display', true);

  if ($column == 'status') {
    echo implode(" ", array_map("ucfirst", explode("-", $status)));
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
}

add_filter('manage_edit-wordpress_promotion_sortable_columns', 'smashing_wordpress_promotion_sortable_columns');
function smashing_wordpress_promotion_sortable_columns($columns)
{
  $columns['custom_date'] = 'custom_date';
  $columns['status'] = 'engrid_lightbox_display';
  $columns['post_id'] = 'post_id';

  return $columns;
}

add_action('pre_get_posts', 'smashing_posts_orderby');
function smashing_posts_orderby($query)
{
  if (!is_admin() || !$query->is_main_query()) {
    return;
  }

  if ('engrid_lightbox_display' === $query->get('orderby')) {
    $query->set('orderby', 'meta_value');
    $query->set('meta_key', 'engrid_lightbox_display');
  }

  if ('engrid_start_date' === $query->get('orderby')) {
    $query->set('orderby', 'meta_value');
    $query->set('meta_key', 'engrid_start_date');
    $query->set('meta_type', 'date');
  }

  if ('engrid_end_date' === $query->get('orderby')) {
    $query->set('orderby', 'meta_value');
    $query->set('meta_key', 'engrid_end_date');
    $query->set('meta_type', 'date');
  }

  if ('post_id' === $query->get('orderby')) {
    $query->set('orderby', 'ID');
  }

  if ('custom_date' === $query->get('orderby')) {
    $query->set('orderby', 'date');
  }
}
