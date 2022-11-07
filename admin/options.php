
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
function engrid_wordpress_multistep_menu_pages()
{
    acf_add_options_page(array(
        'page_title' => 'Multistep Lightbox',
        'menu_title' => 'Multistep Lightbox',
        'menu_slug' => 'multistep-lightbox',
        'capability' => 'manage_options',
        'position' => 61.1,
        'redirect' => true,
        'icon_url' => 'dashicons-admin-customizer',
        'update_button' => 'Save',
        'updated_message' => 'Multistep Lightbox Saved',
    ));
}


/**
 * Hook: acf/init.
 *
 * @uses add_action() https://developer.wordpress.org/reference/functions/add_action/
 * @uses acf/init https://www.advancedcustomfields.com/resources/acf-init/
 */
// add_action('acf/init', 'engrid_wordpress_multistep_menu_pages');


// Register Multistep Lightbox Post Type
function register_multistep_lightbox_post_type() {
    $labels = array(
        'name'                  => _x( 'Multistep Lightboxes', 'Post Type General Name', 'engrid-wordpress-multistep' ),
        'singular_name'         => _x( 'Multistep Lightbox', 'Post Type Singular Name', 'engrid-wordpress-multistep' ),
        'menu_name'             => __( 'Multistep Lightboxes', 'engrid-wordpress-multistep' ),
        'name_admin_bar'        => __( 'Multistep Lightbox', 'engrid-wordpress-multistep' ),
        'archives'              => __( 'Multistep Lightbox Archives', 'engrid-wordpress-multistep' ),
        'attributes'            => __( 'Multistep Lightbox Attributes', 'engrid-wordpress-multistep' ),
        'parent_item_colon'     => __( 'Parent Multistep Lightbox:', 'engrid-wordpress-multistep' ),
        'all_items'             => __( 'All Multistep Lightboxes', 'engrid-wordpress-multistep' ),
        'add_new_item'          => __( 'Add New Multistep Lightbox', 'engrid-wordpress-multistep' ),
        'add_new'               => __( 'Add New', 'engrid-wordpress-multistep' ),
        'new_item'              => __( 'New Multistep Lightbox', 'engrid-wordpress-multistep' ),
        'edit_item'             => __( 'Edit Multistep Lightbox', 'engrid-wordpress-multistep' ),
        'update_item'           => __( 'Update Multistep Lightbox', 'engrid-wordpress-multistep' ),
        'view_item'             => __( 'View Multistep Lightbox', 'engrid-wordpress-multistep' ),
        'view_items'            => __( 'View Multistep Lightboxes', 'engrid-wordpress-multistep' ),
        'search_items'          => __( 'Search Multistep Lightbox', 'engrid-wordpress-multistep' ),
        'not_found'             => __( 'Not found', 'engrid-wordpress-multistep' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'engrid-wordpress-multistep' ),
    );
    $args = array(
        'label'                 => __( 'Multistep Lightbox', 'engrid-wordpress-multistep' ),
        'description'           => __( 'Multistep Lightbox', 'engrid-wordpress-multistep' ),
        'labels'                => $labels,
		'supports'              => array( 'title' ),
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
    register_post_type( 'multistep_lightbox', $args );
}

add_action( 'init', 'register_multistep_lightbox_post_type', 0 );

// Add new columns to list page
add_filter( 'manage_multistep_lightbox_posts_columns', 'smashing_add_new_columns' );

function smashing_add_new_columns( $columns ) {
    $columns['status'] = __( 'Status', 'smashing' );
    $columns['engrid_start_date'] = __( 'Start Date', 'smashing' );
    $columns['engrid_end_date'] = __( 'End Date', 'smashing' );
    $columns['trigger'] = __( 'Trigger', 'smashing' );
    return $columns;
}

add_action( 'manage_multistep_lightbox_posts_custom_column', 'smashing_multistep_lightbox_column', 10, 2);
function smashing_multistep_lightbox_column( $column, $post_id ) {
  $status = get_post_meta( $post_id, 'engrid_lightbox_display', true);

  if ( $column == 'status' ) {
    echo implode(" ", array_map("ucfirst", explode("-", $status)));
  }
  
  if ( 'engrid_start_date' === $column ) {
    $start_date = strtotime(get_post_meta( $post_id, 'engrid_start_date', true ));

    if($status == "scheduled") {
      echo date("m/d/Y", $start_date);
    } else {
      echo "--";
    }
  }
  
  if ( 'engrid_end_date' === $column ) {
    $end_date = strtotime(get_post_meta( $post_id, 'engrid_end_date', true ));

    if($status == "scheduled") {
      echo date("m/d/Y", $end_date);
    } else {
      echo "--";
    }
  }
  
  if ( 'trigger' === $column ) {
    $trigger = get_post_meta( $post_id, 'engrid_trigger_type', true );
    $pixels = get_post_meta( $post_id, 'engrid_trigger_scroll_pixels', true );
    $seconds = get_post_meta( $post_id, 'engrid_trigger_seconds', true );
    $percentage = get_post_meta( $post_id, 'engrid_trigger_scroll_percentage', true );

    switch($trigger) {
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
    }
  }
}

add_filter( 'manage_edit-multistep_lightbox_sortable_columns', 'smashing_multistep_lightbox_sortable_columns');
function smashing_multistep_lightbox_sortable_columns( $columns ) {
  $columns['status'] = 'engrid_lightbox_display';
  $columns['engrid_start_date'] = 'engrid_start_date';
  $columns['engrid_end_date'] = 'engrid_end_date';

  return $columns;
}

add_action( 'pre_get_posts', 'smashing_posts_orderby' );
function smashing_posts_orderby( $query ) {
  if( ! is_admin() || ! $query->is_main_query() ) {
    return;
  }

  if ( 'engrid_lightbox_display' === $query->get( 'orderby') ) {
    $query->set( 'orderby', 'meta_value' );
    $query->set( 'meta_key', 'engrid_lightbox_display' );
  }
  
  if ( 'engrid_start_date' === $query->get( 'orderby') ) {
    $query->set( 'orderby', 'meta_value' );
    $query->set( 'meta_key', 'engrid_start_date' );
    $query->set( 'meta_type', 'date' );
  }

  if ( 'engrid_end_date' === $query->get( 'orderby') ) {
    $query->set( 'orderby', 'meta_value' );
    $query->set( 'meta_key', 'engrid_end_date' );
    $query->set( 'meta_type', 'date' );
  }
}