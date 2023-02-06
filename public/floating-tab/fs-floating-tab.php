<?php

/*
Plugin Name: 	4Site Floating Tab
Version: 		1.0
Description: 	Provides a configurable floating tab. Requires Advanced Custom Fields to be installed.
Author:         4Site Interactive Studios
Author URI:     https://www.4sitestudios.com
*/

if(function_exists('acf_add_options_page')) {
	acf_add_options_page([
		'page_title' => 'Floating Tab',
		'menu_title' => 'Floating Tab',
		'menu_slug' => 'fs-en-floating-tab',
		'capability' => 'edit_posts',
		'icon_url' => 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjEiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAyMSAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICAgIDxwYXRoIGQ9Ik0xNi4yODMgMTkuNzM4Yy0uMTUzLS4wNjYtLjE3NS0uNTUtLjI2My0uNTUtLjEzMiAwLS4xOTguNDQtLjQ2Mi41MDYtLjI2NC4wNDQtLjY1OS0uMjg2LS43NjktLjE5OC0uMTEuMDg4LjI0Mi43MDQtLjM1MS43MDQtLjE3NiAwLS4yNjQtLjE5OC0uMjY0LS4zNTJ2LTQuNzY5cy4wMjIgMCAuMjg2LjA2NmMuMTc1LjA0NC4zNTEuMTMyLjM1MS4zNzQgMCAuMTMyLS4wNDQuMzk1LjA0NC40NC4wODguMDQzLjE1NC0uMDQ1LjE5OC0uMDY3LjgxMy0uNTkzIDEuMDEuNDYyIDEuMTg2LjQ2Mi4xNTQgMCAuMTMyLS43MjUuMzUyLS43MjUuMTc2LS4wNjYgMS44NDYuNzQ3IDEuODQ2IDEuMjc0IDAgLjExLS4yMi4xMzItLjI0Mi4yNDItLjAyMi4xNTQuMzk2LjIyLjMzLjUwNS0uMDIyLjE3Ni0uNDE4LjM5Ni0uMzA4LjUwNi4xMS4xMS42MzgtLjExLjY4MS0uMTEuMTEgMCAuMTMyLjE3Ni4xMzIuMjQyIDAgLjk0NS0yLjI0MSAxLjY3LTIuNzQ3IDEuNDV6bS01Ljk5OS43N2MtLjEzMi0uMjItLjA2Ni0uNTk0LS4xOTgtLjU5NC0uMDY1IDAtLjQxNy41MjctLjYxNS41MjdzLS41MjctLjM3My0uNjYtLjM3M2MtLjA4NyAwIC4wODkuNTI3LS4wODcuNzQ3LS4xMS4xNTQtMS4xNjUuMjYzLTEuMTY1LjEzMnYtNy40MjhjMC0uMzUxLjI2NC0uNDgzLjUwNi0uNDgzaDMuOTExYy40ODQgMCAuNjgxLjE1NC42ODEuNjU5djYuNjU4Yy4wMjMuMjItMi4yODUuMzA4LTIuMzczLjE1NHptMi4zOTYgOS40MDRjMCAuNDYyLS4xNTQuNTcyLS41NS40ODRsLTQuMTc1LS44MzVjLS4yNDItLjA0NC0uMzc0LS4xOTgtLjM3NC0uNDYyVjIxLjU0YzAtLjE5OC42ODItLjIyLjc5MS0uMTMyLjE5OC4xNzYuMTMyLjgxMy4yNjQuODEzLjE3NiAwIC42MzctLjc2OS44MzUtLjc2OS40MTggMCAuNzAzLjI0Mi43OTEuMTk4LjA4OC0uMDQ0LjA4OC0uMzk2LjE5OC0uNDg0LjE1NC0uMTEuNjE1LS4xMS44NTctLjEzMS4zOTYtLjAyMiAxLjEyLS4wODggMS4xMi0uMDg4LjI4NyAwIC4yNjQuMTk3LjI2NC42MTV2OC4zNWgtLjAyMXpNOC4xNzUgOC4wMDNjMC0zLjAxIDQuNDYtNC43NDYgNC43NjgtNi4yNjIuMzMuMzA3LjM3NC44MTMuMzc0IDEuMjUyIDAgMS44MjQtMy4xODcgNC45MjMtMy4xODcgNy4xNDIgMCAuMjQyLjAyMi4zMDguMDIyLjQ2MiAwIC4yMi0uMDg3LjI2My0uMTMxLjI2My0uNTI4LS4wMjItMS44NDYtMS4wNTUtMS44NDYtMi44NTd6TTUuNzEzIDI2LjgzNmMtLjM1MSAwLS41Ny0uNzAzLS42NTktLjY4MS0uMTEuMDIyLjAyMi41OTMtLjExLjY4LS4xMS4xMS0yLjQxNy0uOTY2LTIuNTkzLTEuMTQyLS4xNTQtLjE1NC4wNjYtLjM3MyAwLS41MjctLjA0NC0uMTMyLS4zOTUtLjA4OC0uNTA1LS4xNTQtLjA4OC0uMDg4LjExLS40ODMtLjAyMi0uNTcxLS4xMS0uMDY2LS4zNTIuMzk1LS41MDYuMTc1LS4wNjUtLjA4Ny0uMDg3LS4yNDEtLjA4Ny0uNDQgMC0xLjM0IDEuNDI4LTEuODY3IDIuMzczLTIuMTk3LjM5NS0uMTMxLjI2NC4zNzQuMzk1LjM5Ni4xNTQuMDQ0LjE3Ni0uMjQyLjM1Mi0uMjQyLjEzMiAwIC42MzcuMjQyLjc0Ny4xNTQuMDg4LS4wNjYtLjI2NC0uMzk1LS4xNTQtLjUyNy4xMzItLjExIDEuMTQzLS4zMDggMS4xNDMtLjA0NHY0LjY1OGMwIC41MDYtLjM3NC40NjItLjM3NC40NjJ6bTEzLjcxMy04LjA0M2MuMjQyLS40MTcuMjQyLS45MjMuMzMtMS4xMi4xMzEtLjIyLjYxNS0uMTc2LjYxNS0uMzMgMC0uMDY2LS4zNTItLjE1NC0uNDg0LS4yNDItLjI2My0uMTU0LS4xNzUtLjk0NS0uMzA3LS45NDUtLjE5OCAwLS4zOTYuNTUtLjU5NC4zMDgtLjM1MS0uNDQtMS43OC0xLjI3NS0yLjQzOS0xLjUxNi0uMjQyLS4wODguMTc2LS44MTQuMDY2LS44OC0uMTEtLjA2NS0uMzk1LjQ0LS44MTMuMzUyLS4zMy0uMDY2LS4xNzYtLjY2LS4zNTItLjY2LS4wODggMC0uNDE3LjgzNi0uNjU5Ljc3bC0uNjE1LS4xNTR2LTIuMTc1YzAtLjM3NC0uMjQyLS42Ni0uNTcyLS42NmgtMi4zNWMtLjY2IDAtLjYxNi0uNDgzLS42MTYtLjU5MyAwLTIuOSA0LjIxOS0zLjM4NCA0LjIxOS03LjE4NiAwLTEuNjctMS4wNTUtMi43NjktMi4yNjMtMy43NTctLjA2Ni0uMDQ0IDAgLjI0MSAwIC4zNTEgMCAxLjc1OC02LjU3IDIuNjgxLTYuNTcgNi43NjggMCAzLjU2IDMuNTM3IDMuNzE0IDMuNTM3IDQuMjY0IDAgLjExLS4yMi4xNTMtLjI0Mi4xNTNINi42MTRjLS4yODUgMC0uNTI3LjE3Ni0uNTI3LjY2djIuMTk3Yy0uMjY0LjA4OC0uNjE1LS4zMDctLjc5MS0uMzA3LS4wODggMC0uMDQ0LjY4LS4xOTguNzY5LS4xMzIuMDY2LTIuNTkzLjk0NS0yLjgxMy45NjYtLjM1MS4wNjYtLjI2My0uNTQ5LS40NjEtLjU0OS0uMTc2IDAtLjE3Ni42MTUtLjYxNS42MTUtLjI2NCAwLS40ODQtLjI0MS0uNjgyLS4yNDEtLjEzMSAwIC4yODYuNTA1LjI4Ni42NTkgMCAuNDE3LS44MTMuNTcxLS44MTMuODU3IDAgLjE1NC43Ny0uMDY2Ljg3OS0uMDY2LjM5NiAwIC4zNTIuNDYyLjUwNS40NjIuMTU0IDAgLjI2NC0uNTI4LjM5Ni0uNjE2LjI2NC0uMTU0LjU3MS4xMS43MDMuMTEuMjIgMC0uMTc2LS41MjcuMDIyLS42MTUgMCAwIDIuNjYtMS4xNDMgMi44MzUtMS4wNTUuMTU0LjA4OC4xMzIuMzUyLjMwOC40NC4xNzUuMDg3LjQzOS0uNjE2LjQzOS0uMTc2djUuMzYyYy0uMzA4LjE5Ny0uOTY3LjMwNy0xLjMxOC4zMDctLjE3NiAwLS4wODgtLjU3MS0uMTk4LS41NzEtLjE3NiAwLS4zNTIuNDgzLS42MTYuNDgzLS4yNDEgMC0uNTkzLS41OTMtLjgzNS0uNTkzLS4xNTMgMCAuMjg2Ljc0Ny4wMjIuODc5LTEuMDEuNTA1LTIuNTkzLjk4OS0yLjU5MyAyLjU3MSAwIC4zMy4xNzYuNzI1LjExLjgzNS0uMDY2LjE1NC0uNDE3LjQ2Mi0uMzk1LjU5MyAwIC4xMzIuNjU5LjA4OC43OS4yODYuMDY3LjExLjExLjU5My4yNDMuNTkzLjExIDAgLjM5NS0uMzczLjYzNy0uMjQxLjc2OS40NjEgMi4wODcuOTY3IDIuNzAzIDEuMjk2LjE1NC4wODguMDY2LjU3Mi4xNTQuNjE2LjE3NS4wODcuNTI3LS40NjIgMS4wNzYtLjE1NC4xMS4wNDQuMjIuMjIuMjIuNDE3djEuNzhjMCAuMzMuMTU0LjQxOC4zMy40NjJsNi41MDQgMS4yOTZzLjU3Mi4xMzIuNzcuMTMyYy4yMiAwIC41MDUgMCAuNDgzLS41NXYtNC4wNjVjMC0uMDQ0LjA0NC0uMTMyLjEzMi0uMTMyLjM3MyAwIC4xOTcgMS4xLjI4NSAxLjEuMTc2IDAgLjI4Ni0uNzQ4LjUwNi0uNzQ4LjMwNyAwIC41MDUuMjg2LjYzNy4yMi4xMS0uMDY2LS4wNjYtLjQxOC0uMDY2LS42MzcgMC0uMTk4LjU3Mi0uMjg2IDEuMjc1LS41NS42MTUtLjIyIDEuMTItLjQ2MSAxLjMxOC0uNDYxLjE1NCAwIC4yNjQuNDE3LjM5Ni40MTcuMTEgMCAuMTMxLS41NS4zNzMtLjU1LjE5OCAwIC42MzcuMzA4Ljc0Ny4yMi4wODgtLjA2NS0uMjItLjQ4My4wMjItLjY1OS4xNzYtLjExLjU5NC0uMzczLjU5NC0uNDgzIDAtLjA2Ni0uODEzLS4wODgtLjg1Ny0uMDg4LS4xNTQtLjA0NC0uMzMtLjUyNy0uNDQtLjUyNy0uMTMyIDAtLjEzMi40ODMtLjMzLjYzNy0uMTc1LjEzMi0uNjE1LS40NjItLjgxMy0uNDYyLS4xMSAwIC4xMzIuNDQuMTMyLjc5MiAwIC4zNTEtMi4xMSAxLjAzMi0yLjM5NSAxLjAzMi0uNDQgMC0uNDYxLS41NS0uNTUtLjU1LS4xMzEgMC0uMTMxLjY2LS42MTUuNjYtLjI0MSAwLS4zMy0uMTc2LS4zMy0uMzA4di01LjI1MmMwLS4xNTMuMDQ1LS4zNTEuMzMtLjM3My4zNTItLjAyMi4zNzQuNzkxLjUwNi43OTEuMjQyIDAgLjM3My0uODEzLjgzNS0uODEzLjM1MiAwIC42MzcuNDgzLjg1Ny40ODMuMTU0IDAtLjI2NC0uNTA1LS4yNjQtLjc0Ny0uMDIyLS4yMiAyLjEzMi0uMTMyIDIuOTY3LTEuNjA0eiIgZmlsbD0iY3VycmVudENvbG9yIi8+Cjwvc3ZnPg==',
		'redirect' => false
	]);
}

add_filter('acf/settings/load_json', function($paths) {
	$paths[] = plugin_dir_path(__FILE__) . '/acf-json';
	return $paths;
});

add_action('wp_enqueue_scripts', function() {
	wp_enqueue_style('fs-floating-tab', plugins_url('fs-floating-tab.css', __FILE__));
});

add_action('wp_footer', function() {
	$colors = get_field('fsft_color', 'option');
	$radius = get_field('fsft_radius', 'option');
	$location = get_field('fsft_location', 'option');
	$image = get_field('fsft_image', 'option');
	$link = get_field('fsft_link', 'option');
	$id = 'fs-donation-tab';

	$style = '';
	if(!empty($colors['foreground'])) $style .= "color: {$colors['foreground']};";
	if(!empty($colors['background'])) $style .= "background-color: {$colors['background']};";
	if(!empty($radius)) $style .= "border-radius: {$radius} {$radius} 0 0;";

	$classes = "{$location} {$image}";

	if(!empty($svg_markup)) $svg_markup = " <div class='svg-wrapper'>{$svg_markup}</div>";

	$attributes = '';
	if(is_array($link['attributes'])) {
		for($i = 0; $i < count($link['attributes']); $i++) {
			$key = $link['attributes'][$i]['key'];
			$value = $link['attributes'][$i]['value'];

			if(stripos($key, 'class') !== false) {
				$classes .= " {$value}";				
			} else if(stripos($key, 'id') !== false) {
				$id = $value;
			} else if(stripos($key, 'href') !== false) {
				// ignore this key -- we set href via the $link['url'] field
			} else {
				$attributes .= "{$key}='{$value}' ";				
			}
		}
	}

	$svg_markup = "
		<div class='candle'>
			<div class='candle-flame'>
				<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12.5 24.978'><g data-name='Layer 2'><g data-name='flame b'><path d='M0 15.157c0 6.897 6.25 9.821 6.25 9.821s6.25-2.924 6.25-9.821S6.25 0 6.25 0 0 8.26 0 15.157z' style='fill:#ff4700'></path><path d='M3.375 18.383a6.768 6.768 0 0 0 2.875 5.763 6.768 6.768 0 0 0 2.875-5.763c0-4.047-2.875-8.893-2.875-8.893s-2.875 4.846-2.875 8.893z' style='fill:#ffbf00'></path></g></g></svg>
			</div>
			<div class='candle-base'>
				<span class='spark'></span>
				<span class='spark'></span>
				<span class='spark'></span>
				<span class='spark'></span>
				<span class='spark'></span>
				<span class='spark'></span>
				<span class='spark'></span>
				<span class='spark'></span>
				<span class='spark'></span>
				<span class='spark'></span>
				<span class='spark'></span>
				<span class='spark'></span>
				<span class='spark'></span>
				<span class='spark'></span>
				<span class='spark'></span>
				<svg xmlns='http://www.w3.org/2000/svg' width='30' height='40' fill='none'><path xmlns='http://www.w3.org/2000/svg' transform='translate(-18,-12)' d='M38.843 35.815c-.202-.085-.224-.702-.336-.702-.171 0-.258.561-.588.644-.33.082-.845-.365-.99-.254-.145.11.318.913-.44.913-.226 0-.342-.257-.342-.454v-6.137c.123.012.245.037.364.074.225.059.45.17.45.481 0 .165-.06.503.055.562.114.058.2-.059.253-.087 1.038-.756 1.292.59 1.517.59.226 0 .17-.926.451-.926.225-.086 2.388.954 2.388 1.63 0 .14-.281.167-.306.31-.028.201.506.28.419.648-.03.225-.533.507-.402.644.13.136.819-.137.875-.137.139 0 .165.221.165.306 0 1.239-2.893 2.169-3.541 1.885l.008.01zm-7.713.97c-.169-.276-.084-.757-.251-.757-.085 0-.533.676-.789.676-.255 0-.672-.479-.84-.479-.114 0 .112.676-.114.954-.142.201-1.49.344-1.49.173v-9.561a.604.604 0 01.645-.618h5.03c.616 0 .872.201.872.843v8.58c0 .28-2.956.392-3.069.19l.006-.001zm3.075 12.119c0 .593-.201.73-.702.62l-5.37-1.07a.537.537 0 01-.48-.589V38.14c0-.254.872-.282 1.007-.17.253.226.169 1.041.336 1.041.227 0 .817-.986 1.07-.986.535 0 .897.31 1.006.254.109-.057.115-.507.254-.62.2-.135.786-.135 1.096-.169.507-.028 1.435-.113 1.435-.113.366 0 .338.258.338.791l.01 10.736zM28.412 20.68c0-3.881 5.736-6.102 6.129-8.07.422.402.479 1.04.479 1.61 0 2.36-4.105 6.323-4.105 9.193 0 .31.028.402.028.59 0 .279-.112.337-.167.337-.678 0-2.364-1.322-2.364-3.654v-.006zm-3.177 24.262c-.449 0-.73-.902-.843-.871-.113.03.03.758-.139.87-.169.113-3.12-1.237-3.346-1.458-.225-.221.085-.479 0-.676-.084-.197-.509-.115-.646-.201-.137-.087.137-.62-.028-.729-.165-.108-.45.507-.648.222a.964.964 0 01-.11-.56c0-1.716 1.824-2.414 3.064-2.839.503-.17.332.477.503.505.17.028.225-.308.45-.308.172 0 .817.308.956.202.113-.083-.336-.505-.2-.674.134-.17 1.474-.387 1.462-.05v6.005c0 .63-.483.568-.483.568l.008-.006zM42.885 34.6c.31-.532.31-1.18.422-1.433.165-.28.785-.225.785-.422 0-.083-.448-.202-.62-.308-.334-.201-.22-1.208-.39-1.208-.255 0-.507.7-.763.403-.446-.564-2.303-1.632-3.146-1.942-.31-.113.225-1.04.086-1.127-.139-.086-.507.566-1.042.453-.42-.086-.225-.845-.45-.845-.114 0-.536 1.069-.846.984-.31-.085-.783-.201-.783-.201v-2.821c0-.477-.322-.847-.734-.845h-3.028c-.843 0-.787-.618-.787-.759 0-3.738 5.433-4.356 5.433-9.255 0-2.14-1.348-3.572-2.924-4.84-.086-.054 0 .313 0 .451 0 2.278-8.463 3.46-8.463 8.717 0 4.581 4.556 4.778 4.556 5.482 0 .14-.28.202-.308.202H26.39c-.366 0-.682.225-.678.839v2.817c-.34.112-.787-.403-1.006-.403-.113 0-.055.873-.254.986-.169.083-3.344 1.207-3.621 1.238-.445.082-.332-.705-.586-.705-.253 0-.227.787-.789.787-.338 0-.617-.31-.869-.31-.169 0 .362.648.362.843 0 .535-1.036.73-1.036 1.099 0 .201.98-.085 1.123-.085.505 0 .45.592.644.592.193 0 .338-.678.509-.789.334-.201.73.141.9.141.279 0-.226-.676.027-.789 0 0 3.42-1.458 3.654-1.348.201.115.171.45.403.564.231.112.563-.787.563-.228v6.89c-.402.25-1.24.402-1.69.402-.223 0-.113-.732-.254-.732-.225 0-.45.617-.786.617s-.759-.756-1.065-.756c-.201 0 .362.953.024 1.122-1.295.648-3.346 1.268-3.346 3.316 0 .425.228.934.14 1.069-.081.201-.53.591-.504.76.026.17.843.111 1.006.369.083.136.139.756.31.756.137 0 .503-.477.815-.31.982.592 2.696 1.236 3.487 1.662.201.113.08.73.201.787.227.113.676-.592 1.382-.201a.604.604 0 01.282.537v2.3c0 .42.201.531.423.587l8.382 1.668c.328.077.66.133.996.167.292.012.654 0 .634-.706v-5.231a.201.201 0 01.179-.181c.493 0 .245 1.408.356 1.408.223 0 .364-.956.648-.956.402 0 .65.367.805.282.154-.084-.08-.535-.08-.817 0-.251.727-.364 1.629-.702.787-.278 1.432-.59 1.688-.59.201 0 .332.534.505.534.139 0 .167-.7.477-.7.251 0 .805.39.958.277.11-.08-.282-.618.024-.845.227-.139.76-.473.76-.616 0-.082-1.04-.114-1.096-.114-.201-.055-.42-.672-.566-.672-.145 0-.16.617-.418.814-.226.167-.787-.591-1.036-.591-.145 0 .165.563.165 1.006 0 .443-2.725 1.32-3.09 1.32-.564 0-.593-.704-.705-.704-.17 0-.165.859-.787.859a.403.403 0 01-.42-.403V37.5a.402.402 0 01.42-.47c.463-.023.475 1.023.644 1.023.31 0 .48-1.038 1.07-1.038.45 0 .815.62 1.097.62.201 0-.336-.648-.336-.958 0-.31 2.783-.201 3.851-2.082' fill='currentColor'/></svg>
			</div>
		</div>
	";


	echo "<a href='{$link['url']}' id='{$id}' style='{$style}' class='{$classes}' {$attributes}>{$link['label']}{$svg_markup}</a>";
});

register_activation_hook(__FILE__, function() {
	if(!function_exists( 'is_plugin_active_for_network')) {
		include_once(ABSPATH . '/wp-admin/includes/plugin.php');
	}
	if(current_user_can('activate_plugins') && !class_exists('ACF')) {
		deactivate_plugins(plugin_basename( __FILE__ ));
		$error_message = __('This plugin requires', 'fsft') . ' Advanced Custom Fields ' . __('to be active.', 'fsft');
		die("<p style='font-family:-apple-system,BlinkMacSystemFont,\"Segoe UI\",Roboto,Oxygen-Sans,Ubuntu,Cantarell,\"Helvetica Neue\",sans-serif;font-size: 13px;line-height: 1.5;color:#444;'>{$error_message}</p>");
	}
});