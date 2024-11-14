<?php
/**
 *  Plugin Updates
 * 
 * foursite_wordpress_promotion_get_latest_release_info : fetches plugin information
 * foursite_wordpress_promotion_add_remote_plugin_version : fetches the latst remote release version and sets it in the WP plugin versions transient
 * foursite_wordpress_promotion_plugin_popup : returns the array of plugin info to the WP site
 * foursite_wordpress_promotion_after_update : ensures that the plugin is enabled if it was enabled prior to the update process
 * 
 **/

if(!defined('ABSPATH')) { exit; }

define('FWP_RELEASES_URL', 'https://api.github.com/repos/4site-interactive-studios/4site-wordpress-promotions/releases');

add_filter('pre_set_site_transient_update_plugins', 'foursite_wordpress_promotion_add_remote_plugin_version', 10, 1);
add_filter('plugins_api', 'foursite_wordpress_promotion_plugin_popup', 10, 3);
add_filter('upgrader_post_install', 'foursite_wordpress_promotion_after_update', 10, 3);

function foursite_wordpress_promotion_get_latest_release_info() {
    $request_uri = FWP_RELEASES_URL;
    $response = json_decode(wp_remote_retrieve_body(wp_remote_get($request_uri)), true);
    if(is_array($response)) {
        $response = current($response);
    }
    return $response;
}

function foursite_wordpress_promotion_add_remote_plugin_version($transient) {
    if(property_exists($transient, 'checked')) {
        if($checked = $transient->checked) {
            $response = foursite_wordpress_promotion_get_latest_release_info();
            $plugin_basename = foursite_wordpress_promotion_plugin_basename();
            if(!empty($response['tag_name']) && isset($checked[$plugin_basename])) {
                $out_of_date = version_compare($response['tag_name'], $checked[$plugin_basename], 'gt');
                if($out_of_date) {
                    $zipball_url = $response['zipball_url'];
                    $slug = current(explode('/', $plugin_basename));
                    $plugin_data = foursite_wordpress_promotion_plugin_data();
                    $plugin = [
                        'url' => $plugin_data['PluginURI'],
                        'slug' => $slug,
                        'package' => $zipball_url,
                        'new_version' => $response['tag_name']
                    ];
                    $transient->response[$plugin_basename] = (object) $plugin;
                }
            }                
        }
    }
    return $transient;
}

function foursite_wordpress_promotion_plugin_popup($result, $action, $args) {
    if(!empty($args->slug)) {
        $plugin_basename = foursite_wordpress_promotion_plugin_basename();
        if($args->slug == current(explode('/', $plugin_basename))) {
            $response = foursite_wordpress_promotion_get_latest_release_info();
            $plugin = [
                'name' => $plugin_data['Name'],
                'slug' => $plugin_basename,
                'version' => $response['tag_name'],
                'author' => $plugin_data['AuthorName'],
                'author_profile' => $plugin_data['AuthorURI'],
                'last_updated' => $response['published_at'],
                'homepage' => plugin_data['PluginURI'],
                'short_description' => $plugin_data['Description'],
                'sections' => [
                    'Description' => $plugin_data['Description'],
                    'Updates' => $response['body']
                ],
                'download_link' => $response['zipball_url']
            ];
            return (object) $plugin;
        }
    }
    return $result;
}

function foursite_wordpress_promotion_after_update($response, $hook_extra, $result) {
    global $wp_filesystem;

    $install_directory = foursite_wordpress_promotion_plugin_path();
    $wp_filesystem->move($result['destination'], $install_directory);
    $result['destination'] = $install_directory;

    $plugin_basename = foursite_wordpress_promotion_plugin_basename();
    if(is_plugin_active($plugin_basename)) {
        activate_plugin($plugin_basename);
    }

    return $result;
}
