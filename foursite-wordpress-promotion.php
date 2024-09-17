<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.4sitestudios.com
 * @since             1.0.0
 * @package           foursite_wordpress_promotion
 *
 * @wordpress-plugin
 * Plugin Name:       Foursite Wordpress Promotion
 * Plugin URI:        https://www.4sitestudios.com/foursite-wordpress-promotion/
 * Description:       Add Foursite Wordpress Promotion Form to your WordPress site.
 * Version:           1.7.2
 * Author:            4Site Studios
 * Author URI:        https://www.4sitestudios.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       foursite-wordpress-promotion
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
if ( defined( 'foursite_wordpress_promotion_VERSION' ) ) {
    error_log('fwpv already defined');
}
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'foursite_wordpress_promotion_VERSION', '1.7.2' );

// Gutenberg Block
function promotions_en_form_block() {
    register_block_type(__DIR__ . '/blocks/en-form');
}
add_action( 'init', 'promotions_en_form_block' );



// Add template custom post status for promos
add_action('init', 'fwp_register_template_post_status');
add_action('admin_footer-edit.php','fwp_template_add_in_quick_edit');
add_action('admin_footer-post.php', 'fwp_template_add_in_post_page');
add_action('admin_footer-post-new.php', 'fwp_template_add_in_post_page');
function fwp_register_template_post_status() {
    register_post_status('template', [
        'label'                     => _x('Template', 'promotion'),
        'public'                    => true,
        'exclude_from_search'       => true,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Template <span class="count">(%s)</span>', 'Templates <span class="count">(%s)</span>')
    ]);
}
function fwp_template_add_in_quick_edit() {
    if(get_current_screen()->post_type === 'wordpress_promotion') {
        echo "<script>
        jQuery(document).ready( function() {
            jQuery( 'select[name=\"_status\"]' ).append( '<option value=\"template\">Template</option>' );      
        }); 
        </script>";    
    }
}
function fwp_template_add_in_post_page() {
    if(get_current_screen()->post_type === 'wordpress_promotion') {
        echo "<script>
        jQuery(document).ready( function() {        
            jQuery( 'select[name=\"post_status\"]' ).append( '<option value=\"template\">Template</option>' );
        });
        </script>";
    }
}




// Handle sorting & filtering & generation of a custom "status" & "scheduled" columns for the promos list screen
global $pagenow;
if(is_admin() && 'edit.php' == $pagenow && !empty($_GET['post_type']) && 'wordpress_promotion' == $_GET['post_type']) {
    add_filter('manage_wordpress_promotion_posts_columns', 'fwp_add_columns');
    add_filter('manage_edit-wordpress_promotion_sortable_columns', 'fwp_set_sortable_columns');
    add_action('manage_wordpress_promotion_posts_custom_column', 'fwp_populate_columns', 10, 2);
    add_action('pre_get_posts', 'fwp_sort_query_orderby');

    function fwp_sort_query_orderby($query) {
        $orderby = $query->get('orderby');
        switch($orderby) {
            case 'fwp_schedule_start':
                $query->set('meta_key', 'engrid_start_date');
                $query->set('orderby', 'meta_value');
                break;
            case 'fwp_schedule_end':
                $query->set('meta_key', 'engrid_end_date');
                $query->set('orderby', 'meta_value');
                break;
            default;
                break;
        }
    }

    function fwp_set_sortable_columns($columns) {
        //$columns['fwp_status'] = 'fwp_status';
        $columns['fwp_schedule_start'] = 'fwp_schedule_start';
        $columns['fwp_schedule_end'] = 'fwp_schedule_end';
        return $columns;
    }
    function fwp_add_columns($columns) {
        $columns['fwp_status'] = __('Status', ' foursite-wordpress-promotion');
        $columns['fwp_schedule_start'] = __('Start', ' foursite-wordpress-promotion');
        $columns['fwp_schedule_end'] = __('End', ' foursite-wordpress-promotion');
        $columns['fwp_pages'] = __('Pages', ' foursite-wordpress-promotion');
        return $columns;
    }
    function fwp_populate_columns($column, $post_id) {
        if($column == 'fwp_status') {
            $fwp_status = '';
    
            $post_status = get_post_status($post_id);
            if(!in_array($post_status, ['publish', 'template'])) {
                $fwp_status = "Off - Not Published";
            } else {
                $status = get_field('engrid_lightbox_display', $post_id);
                if($status == 'scheduled') {
                    $start_date = get_field('engrid_start_date', $post_id);
                    $end_date = get_field('engrid_end_date', $post_id);
        
                    $current_date = new DateTime('now', new DateTimeZone('America/New_York'));
                    $current_date = $current_date->format('Y-m-d H:i:s');
        
                    if($start_date > $current_date) {
                        $fwp_status = "Scheduled - Upcoming";
                    } else if($end_date < $current_date) {
                        $fwp_status = "Scheduled - Expired";
                    } else {
                        $fwp_status = "Scheduled - Active";
                    }
                } else if($status == 'turned-on') {
                    $fwp_status = 'On';
                } else {
                    $fwp_status = 'Off';
                }
            }
    
            if($post_status == 'template') {
                $fwp_status = "Template";
            }
            echo $fwp_status;
        } else if($column == 'fwp_schedule_start') {
            $fwp_schedule = "--";
            $start_date = get_field('engrid_start_date', $post_id);
            if($start_date) {
                $fwp_schedule = date('Y/m/d g:i a', strtotime($start_date)) . " ET";
                $status = get_field('engrid_lightbox_display', $post_id);
                if($status != 'scheduled') {
                    $fwp_schedule = "--";
                }
            }
            echo $fwp_schedule;
        } else if($column == 'fwp_schedule_end') {
            $fwp_schedule = "--";
            $end_date = get_field('engrid_end_date', $post_id);
            if($end_date) {
                $fwp_schedule = date('Y/m/d g:i a', strtotime($end_date)) . " ET";
                $status = get_field('engrid_lightbox_display', $post_id);
                if($status != 'scheduled') {
                    $fwp_schedule = "--";
                }
            }

            echo $fwp_schedule;
        } else if($column == 'fwp_pages') {
            $whitelist = get_field('engrid_whitelist', $post_id);
            $blacklist = get_field('engrid_blacklist', $post_id);
            $show_on_page_ids = get_field('engrid_show_on', $post_id);
            $hide_on_page_ids = get_field('engrid_hide_on', $post_id);

            $fwp_pages = '';
            $fwp_show_on = '';
            $fwp_hide_on = '';

            if($whitelist) {
                $fwp_show_on .= $whitelist;
            }
            if($blacklist) {
                $fwp_hide_on .= $blacklist;
            }             
            if($show_on_page_ids) {
                if($fwp_show_on) {
                    $fwp_show_on .= "<br>";
                }
                $page_ids = implode(',', $show_on_page_ids);
                global $wpdb;
                $results = $wpdb->get_results("SELECT post_title, ID FROM {$wpdb->posts} WHERE ID IN ({$page_ids})");
                foreach($results as $result) {
                    $page_url = get_permalink($result->ID);
                    $fwp_show_on .= "<a href='" . $page_url . "' target='_blank'>{$result->post_title}</a><br>";
                }
            }
            
            if($hide_on_page_ids) {
                if($fwp_hide_on) {
                    $fwp_hide_on .= "<br>";
                }
                $page_ids = implode(', ', $hide_on_page_ids);
                global $wpdb;
                $results = $wpdb->get_results("SELECT post_title, ID FROM {$wpdb->posts} WHERE ID IN ({$page_ids})");
                foreach($results as $result) {
                    $page_url = get_permalink($result->ID);
                    $fwp_hide_on .= "<a href='" . $page_url . "' target='_blank'>{$result->post_title}</a><br>";
                }
            }

            $fwp_pages = '';
            if($fwp_show_on) {
                $fwp_pages .= "<strong>Show on:</strong><br>{$fwp_show_on}<br>";
            }
            if($fwp_hide_on) {
                $fwp_pages .= "<strong>Hide on:</strong><br>{$fwp_hide_on}";
            }
            if(!$fwp_pages) {
                $fwp_pages = '<strong>Show on:</strong><br>All Pages';
            }

            echo $fwp_pages;
        }
    }
    
    function fwp_sort_column_query($query) {
        $orderby = $query->get('orderby');
        if('fwp_status' == $orderby) {
            $meta_query = array(
                'relation' => 'OR',
                array(
                    'key' => 'engrid_lightbox_display',
                    'compare' => 'NOT EXISTS',
                ),
                array(
                    'key' => 'engrid_lightbox_display',
                ),
            );
    
            $query->set('meta_query', $meta_query);
            $query->set('orderby', 'meta_value');
        }
    }

    add_filter('parse_query', 'fwp_filter_request_query' , 10);
    function fwp_filter_request_query($query) {
        if(!(is_admin() AND $query->is_main_query())){ 
            return $query;
        }
        if(!('wordpress_promotion' === $query->query['post_type'] AND !empty($_REQUEST['fwp_status']) ) ){
            return $query;
        }

        $post_ids = [];
        if('on-scheduled' == $_REQUEST['fwp_status']) {
            $post_ids = array_merge($post_ids, fwp_get_post_ids_for_status('active'));
            $post_ids = array_merge($post_ids, fwp_get_post_ids_for_status('upcoming'));
            $post_ids = array_merge($post_ids, fwp_get_post_ids_for_status('on'));
        } else if('off-expired' == $_REQUEST['fwp_status']) {
            $post_ids = array_merge($post_ids, fwp_get_post_ids_for_status('expired'));
            $post_ids = array_merge($post_ids, fwp_get_post_ids_for_status('off'));
        } else {
            $post_ids = fwp_get_post_ids_for_status($_REQUEST['fwp_status']);
        }
        $post_ids = array_unique($post_ids);
        if(count($post_ids) == 0) $post_ids = [0];
        $query->query_vars['post__in'] = $post_ids;
        $query->query_vars['orderby'] = 'post__in';
        return $query;
    }

    function fwp_get_post_ids_for_status($status) {
        global $wpdb;
        $post_ids = [];

        $current_date = new DateTime('now', new DateTimeZone('America/New_York'));
        $current_date = $current_date->format('Y-m-d H:i:s');

        $joins = '';
        $conditions = '';

        if($status == 'expired') {
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
        } else if($status == 'on') {
            $joins = "
                join {$wpdb->postmeta} start_date_pm on p.ID = start_date_pm.post_id
                join {$wpdb->postmeta} end_date_pm on p.ID = end_date_pm.post_id
            ";
            $conditions = "
                and p.post_status in ('publish')
                and active_status_pm.meta_key = 'engrid_lightbox_display'
                and active_status_pm.meta_value = 'turned-on'
            ";
        } else if($status == 'off') {
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
        } else if($status == 'active') {
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
        } else if($status == 'upcoming') {
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
        if(empty($post_ids)) $post_ids = [];
        return $post_ids;
    }

    function fwp_format_option($label, $key, $selected_key, $disabled = false) {
        $selected_string = (!$disabled && $key == $selected_key) ? " selected" : "";
        $disabled_string = ($disabled) ? ' disabled' : '';
        return "<option value='{$key}'{$selected_string}{$disabled_string}>{$label}</option>";
    }

    add_action('restrict_manage_posts', 'fwp_table_filtering' );
    function fwp_table_filtering($post_type) {
        if($post_type !== 'wordpress_promotion' ) {
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
}




// Add custom CSS to force the type & visibility promo fields to group as desired
function fwp_add_acf_field_css() {
    echo "
        <style>
        .acf-fields > .acf-field[data-key='field_65d51492d5b94'],
        .acf-fields > .acf-field[data-key='field_630676e57bb3b'],
        .acf-fields > .acf-field[data-key='field_63694582ec47e'],
        .acf-fields > .acf-field[data-key='field_6420da53c4965'],
        .acf-fields > .acf-field[data-key='field_61f1856cc8652'], 
        .acf-fields > .acf-field[data-key='field_61f185aac8653'],
        .acf-fields > .acf-field[data-key='field_65f24985b7149'] {
            border-top-style: unset;
            padding-top: 0px;
            padding-bottom: 0px;
            min-height: unset!important;
        }
        .acf-field[data-width][data-key='field_6420da53c4965'] + .acf-field[data-width][data-key='field_65f24985b7149'],
        .acf-field[data-width][data-key='field_61f1856cc8652'] + .acf-field[data-width][data-key='field_61f185aac8653'] {
            border-left: none;
        }
        .acf-fields > .acf-field[data-key='field_65d51492d5b94'] {
            padding-top: 15px;
        }
        .acf-fields > .acf-field[data-key='field_630676e57bb3b'] {
            padding-bottom: 10px;	
        }
        .acf-fields > .acf-field[data-key='field_63694582ec47e'] {
            padding-bottom: 15px;
        }
        .acf-fields > .acf-field[data-key='field_6420da53c4965'] {
            padding-bottom: 15px;
        }
        .acf-fields > .acf-field[data-key='field_61f1856cc8652'] {
            padding-bottom: 15px;
        } 
        .acf-fields > .acf-field[data-key='field_61f185aac8653'] {
            padding-bottom: 15px;
        }
        </style>
    ";
}
add_action('admin_head', 'fwp_add_acf_field_css');


function fwp_generate_en_form_shortcode($atts) {
    wp_enqueue_script('en-form-parent'); // Only load the script when the shortcode is used
    $shortcode_atts = shortcode_atts(
        array(
            'url' => '',
            'form-color' => '#f26722',
            'height' => '500px',
            'border-radius' => '5px',
            'loading-color' => '#E5E6E8',
            'bounce-color' => '#16233f',
            'append-url-params' => 'true',
            'frame-title' => ''
        ),
        $atts,
        'en-form'
    );

    // Extract the shortcode attributes
    $url = $shortcode_atts['url'];
    $form_color = $shortcode_atts['form-color'];
    $height = $shortcode_atts['height'];
    $border_radius = $shortcode_atts['border-radius'];
    $loading_color = $shortcode_atts['loading-color'];
    $bounce_color = $shortcode_atts['bounce-color'];
    $append_url_params = $shortcode_atts['append-url-params'];
    $frame_title = $shortcode_atts['frame-title'];

    // Generate the iframe shortcode string
    $shortcode = '<iframe class="promo-form-iframe" ';
    $shortcode .= 'data-src="' . esc_url($url) . '" ';
    $shortcode .= 'data-form_color="' . esc_attr($form_color) . '" ';
    $shortcode .= 'data-height="' . esc_attr($height) . '" ';
    $shortcode .= 'data-border_radius="' . esc_attr($border_radius) . '" ';
    $shortcode .= 'data-loading_color="' . esc_attr($loading_color) . '" ';
    $shortcode .= 'data-bounce_color="' . esc_attr($bounce_color) . '" ';
    $shortcode .= 'title="' . esc_attr($frame_title) . '" ';
    $shortcode .= 'data-append_url_params="' . esc_attr($append_url_params) . '"></iframe>';

    return $shortcode;
}
add_shortcode('en-form', 'fwp_generate_en_form_shortcode');

function promotions_en_form_wp_enqueue_scripts() {
    wp_register_script( 'en-form-parent', plugins_url( '/en-form/dist/en-form-parent.js', __FILE__ ), array(), foursite_wordpress_promotion_VERSION, 'all' );
}
add_action( 'wp_enqueue_scripts', 'promotions_en_form_wp_enqueue_scripts' );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-foursite-wordpress-promotion-activator.php
 */
function activate_foursite_wordpress_promotion() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-foursite-wordpress-promotion-activator.php';
    foursite_wordpress_promotion_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-foursite-wordpress-promotion-deactivator.php
 */
function deactivate_foursite_wordpress_promotion() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-foursite-wordpress-promotion-deactivator.php';
    foursite_wordpress_promotion_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_foursite_wordpress_promotion' );
register_deactivation_hook( __FILE__, 'deactivate_foursite_wordpress_promotion' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-foursite-wordpress-promotion.php';



function foursite_wordpress_promotion_schedule_initial_cron() {
    $tomorrow = new DateTime('tomorrow');
    $tomorrow->setTime(0,1);
    $result = wp_schedule_event($tomorrow->getTimestamp(), 'daily', 'fs_wp_promo_hook', [], true);
    if(is_wp_error($result)) {
        $messages = $result->getErrorMessages();
        //error_log('DEBUG: error scheduling event ' . print_r($messages,true));
    }
}

add_action('fs_wp_promo_hook', 'foursite_wordpress_promotion_cron_exec');
function foursite_wordpress_promotion_cron_exec() {
    //error_log('DEBUG: foursite_wordpress_promotion_cron_exec START');
    $today = date('Y-m-d');
    $current_schedule = get_option('fs_wp_promo_cron_schedules', []);
    $clear_cache = false;
    foreach($current_schedule as $post_id => $dates) {
        foreach($dates as $idx => $date) {
            if($date < $today) {
                $clear_cache = true;
                unset($current_schedule[$post_id][$idx]);
            }
        }
    }
    foreach($current_schedule as $post_id => $dates) {
        if(count($dates) == 0) {
            unset($current_schedule[$post_id]);
        }
    }
    if(count($current_schedule) == 0) {
        $next_scheduled_cron_run = wp_next_scheduled('fs_wp_promo_hook');
        if($next_scheduled_cron_run) {
            //error_log('DEBUG: unscheduling fs_wp_promo_hook');
            wp_unschedule_event($next_scheduled_cron_run, 'fs_wp_promo_hook');
        }
    }
    if($clear_cache) {
        //error_log('DEBUG: Triggering cloudflare cache clear.  Updating cache clear schedule: ' . print_r($current_schedule,true));
        do_action('engrid_wpm_clear_cloudflare_all');
        update_option('fs_wp_promo_cron_schedules', $current_schedule); 
    }
}

add_action('acf/save_post', 'foursite_wordpress_promotion_save_post');
function foursite_wordpress_promotion_save_post( $post_id ) {
    //error_log('DEBUG: foursite_wordpress_promotion_save_post start');
    // Detect when the options page for this plugin is being saved
    if($post_id == 'options' && isset($_POST['acf']['field_61f180fb94e9c'])) {
        // Clear the cloudflare caches. This requires both the
        // Foursite Wordpress Promotion Cloudflare Addon plugin and 
        // the Cloudflare plugin.
        do_action('engrid_wpm_clear_cloudflare_all');
        //error_log('DEBUG: foursite_wordpress_promotion_save_post Options updated. Clearing cache...');
    } else {        
        $post_type = get_post_type($post_id);
        if($post_type == 'wordpress_promotion') {
            //error_log('DEBUG: foursite_wordpress_promotion_save_post wordpress_promotion updated. Clearing cache...');
            foursite_wordpress_promotion_update_schedule($post_id);
            do_action('engrid_wpm_clear_cloudflare_all');
        }
    }
}

function foursite_wordpress_promotion_update_schedule($post_id) {
    //error_log('DEBUG: foursite_wordpress_promotion_update_schedule start');
    $cron_clear_dates = [];
    $values = get_fields($post_id);
    $current_schedule = get_option('fs_wp_promo_cron_schedules', []);
    if(isset($current_schedule[$post_id])) {
        unset($current_schedule[$post_id]);
    }
    if($values['engrid_lightbox_display'] == 'scheduled') {
        $start_date = date('Y-m-d', strtotime($values['engrid_start_date']));
        $end_date = date('Y-m-d', strtotime($values['engrid_end_date']));
        $current_date = date('Y-m-d');
        if($start_date >= $current_date) {
            $cron_clear_dates[] = $start_date;
        }
        if($end_date >= $current_date && $start_date != $end_date) {
            $cron_clear_dates[] = $end_date;
        }
        if($end_date < $current_date) {
            //error_log('DEBUG: Past end date for scheduled promo detected; triggering cache clear.');
            do_action('engrid_wpm_clear_cloudflare_all');
        }
    }
    if(count($cron_clear_dates)) {
        $current_schedule[$post_id] = $cron_clear_dates;
    }
    //error_log('DEBUG: Setting promo cache clear schedule for ' . $post_id . ' : ' . print_r($current_schedule,true));
    update_option('fs_wp_promo_cron_schedules', $current_schedule);

    // set cron run for every day at midnight, if we have events to clear cache for; otherwise, remove the scheduled cron run
    $next_scheduled_cron_run = wp_next_scheduled('fs_wp_promo_hook');
    if(count($current_schedule)) {
        if(!$next_scheduled_cron_run) {
            //error_log('DEBUG: No scheduled cron run for fs_wp_promo_hook.  Initializing it, now.');
            foursite_wordpress_promotion_schedule_initial_cron();
        }
    } else if($next_scheduled_cron_run) {
        //error_log('DEBUG: No need for a scheduled cron run for fs_wp_promo_hook.  Removing it, now.');        
        wp_unschedule_event($next_scheduled_cron_run, 'fs_wp_promo_hook');
    }
}




/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_foursite_wordpress_promotion() {
    $plugin = new foursite_wordpress_promotion();
    $plugin->run();
}
run_foursite_wordpress_promotion();
