<?php

/*
Pizazz: Zazzle feed widget for WordPress
Copyright (C) 2012 Entertaining Software, Inc.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

// prevent people from trying to access this file directly
if ( ! function_exists( 'add_action' ) ) {
	echo "'Ello, 'ello, what's all this then? This is a WordPress plug-in, you can't call it directly.";
	exit;
}

// assert that we are in the dashboard
if ( ! is_admin() ) {
	echo "'Ello, 'ello, what's all this then? This file should only be imported in administrative panels!";
	exit;
}

/** Register Pizazz's settings fields. */
function pizazz_register_settings() {
	register_setting( 'pizazz-settings-group', 'pizazz_shelf_life', 'intval' );
	register_setting( 'pizazz-settings-group', 'pizazz_retry_count', 'intval' );
	register_setting( 'pizazz-settings-group', 'pizazz_associate_id' ); // sanitized by Pizazz_Product
	register_setting( 'pizazz-settings-group', 'pizazz_target_blank' ); // TODO : sanitize?
	register_setting( 'pizazz-settings-group', 'pizazz_country' );
}

/** Create the custom plugin menu for Pizazz. */
function pizazz_create_menu() {
	// create new top-level menu
	$settings_page = add_options_page(
		__( 'Pizazz Settings', 'pizazz' ),
		__( 'Pizazz', 'pizazz' ),
		'manage_options',
		'pizazz',
		'pizazz_settings_page'
	);

	// register the individual settings when the options page is loaded
	add_action( "load-$settings_page", 'pizazz_load_settings' );
}

/** Register settings for the plugin menu of Pizazz. */
function pizazz_load_settings() {
	// register the main settings section
	add_settings_section(
		'pizazz_main_section',
		__( 'Main Settings', 'pizazz' ),
		'pizazz_main_section_text',
		'pizazz'
	);

	// register the shelf-life setting
	add_settings_field(
		'pizazz_shelf_life',
		__( 'Time until Pizazz cache expires in seconds (0 disables caching)', 'pizazz' ),
		'pizazz_shelf_life_string',
		'pizazz',
		'pizazz_main_section'
	);

	// register the download-retry-count setting
	add_settings_field(
		'pizazz_retry_count',
		__( 'Number of times to try and download the Zazzle feed before giving up', 'pizazz' ),
		'pizazz_retry_count_string',
		'pizazz',
		'pizazz_main_section'
	);

	// register the associate id setting
	add_settings_field(
		'pizazz_associate_id',
		__( 'Zazzle Associate ID (gives you a nice cut of the sales!)', 'pizazz' ),
		'pizazz_associate_id_string',
		'pizazz',
		'pizazz_main_section'
	);

	// register target=_blank setting
	add_settings_field(
		'pizazz_target_blank',
		__( 'Open links in a new window?', 'pizazz' ),
		'pizazz_target_blank_string',
		'pizazz',
		'pizazz_main_section'
	);

	// register zazzle-country setting
	add_settings_field(
		'pizazz_country',
		__( 'Country (used for, e.g., prices):', 'pizazz' ),
		'pizazz_country_string',
		'pizazz',
		'pizazz_main_section'
	);

	// register settings section with shortcode manual
	add_settings_section(
		'pizazz_shortcode_help_section',
		__( 'Shortcode Help', 'pizazz' ),
		'pizazz_shortcode_help_section_text',
		'pizazz'
	);
}

/** Output the settings page. */
function pizazz_settings_page() {
	?>
	<div class="wrap">
		<h2>Pizazz Settings</h2>
		<p>Global settings for <a href="http://entertainingsoftware.com/wordpress/pizazz" target="_blank">Pizazz</a>.</p>
		<p>Settings for the Pizazz plugin. <a href="javascript:pizazz_clear_settings_click();">Clear Cache</a></p>

		<form action="options.php" method="post">
			<?php settings_fields( 'pizazz-settings-group' ); ?>
			<?php do_settings_sections( 'pizazz' ); ?>

			<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
		</form>
	</div>
	<?php
}

/** Output the text describing the "Main Section" settings area. */
function pizazz_main_section_text() {
	echo "<p>General settings for Pizazz.</p>";
}

/** Output the input zone for pizazz_shelf_life. */
function pizazz_shelf_life_string() {
	global $pizazz_shelf_life;
	echo "<input name='pizazz_shelf_life' type='text' value='$pizazz_shelf_life' />";
}

/** Output the input zone for pizazz_retry_count. */
function pizazz_retry_count_string() {
	global $pizazz_retry_count;
	echo "<input name='pizazz_retry_count' type='text' value='$pizazz_retry_count' />";
}

/** Output the input zone for pizazz_associate_id. */
function pizazz_associate_id_string() {
	global $pizazz_associate_id;
	echo "<input name='pizazz_associate_id' type='text' value='$pizazz_associate_id' />";
}

/** Output the input zone for pizazz_target_blank. */
function pizazz_target_blank_string() {
	global $pizazz_target_blank;
	$checked = $pizazz_target_blank ? 'checked="checked"' : '';
	echo "<input name='pizazz_target_blank' type='checkbox' $checked />";
}

/** Output the input zone for pizazz_country. */
function pizazz_country_string() {
	global $pizazz_country;

	$countries = array(
		'us' => 'USA',
		'uk' => 'UK',
		'ca' => 'Canada',
		'au' => 'Australia',
		'jp' => 'Japan',
		'de' => 'Germany',
		'es' => 'Spain',
		'br' => 'Brazil',
		'se' => 'Sweden',
		'fr' => 'France'
	);

	echo "<select name='pizazz_country'>";

	foreach ( $countries as $id => $name ) {
		$selected = '';
		if ( $id == $pizazz_country ) {
			$selected = 'selected="selected"';
		}

		echo "<option value='$id' $selected>$name</option>";
	}

	echo '</select>';
}

/** Output documentation on Pizazz's shortcodes. */
function pizazz_shortcode_help_section_text() {
	?>
	<p>Pizazz supports embedding the widget into posts and pages via the
	<code>[pizazz]</code> shortcode. It can be written directly into posts
	and pages with the <code>[pizazz parameter1=value1 parameter2=value2
	etc=yeah]</code> format. The following parameters are accepted:</p>
	<table class='widefat'>
	<tr>
		<th>Name</th>
		<th>Description</th>
		<th>Default</th>
	</tr>
	<tr>
		<td><code>store</code></td>
		<td>Specifies the Zazzle store (gallery) to search.</td>
		<td>All Stores</td>
	</tr>
	<tr>
		<td><code>keywords</code></td>
		<td>Specifies the search query to pass to Zazzle's search engine.</td>
		<td>None</td>
	</tr>
	<tr>
		<td><code>thumbsize</code></td>
		<td>Specifies the width for thumbnails. The height is always the same as the width.</td>
		<td>125</td>
	</tr>
	<tr>
		<td><code>thumbback</code></td>
		<td>Thumbnail background color as a <a href='http://en.wikipedia.org/wiki/Web_colors#Hex_triplet'>hex triplet</a> (no #).</td>
		<td><code>ffffff</code></td>
	</tr>
	<tr>
		<td><code>columns</code></td>
		<td>Number of products to display per line.</td>
		<td>2</td>
	</tr>
	<tr>
		<td><code>rows</code></td>
		<td>Number of lines of products to display.</td>
		<td>2</td>
	</tr>
	<tr>
		<td><code>alignment</code></td>
		<td>Alignment of the text (<code>above, below, left, right</code>).</td>
		<td><code>below</code></td>
	</tr>
	<tr>
		<td><code>price</code></td>
		<td>Price visibility (<code>show</code> or <code>hide</code>).</td>
		<td><code>hide</code></td>
	</tr>
	<tr>
		<td><code>seemore</code></td>
		<td>"See More" link visibility (<code>show</code> or <code>hide</code>).</td>
		<td><code>show</code></td>
	</tr>
	</table>
	<p>Here's an example outputting a 2x5 table of products by HolidayBug
	relating to asparagus with 64x64 thumbnails:</p>
	<blockquote style='font-family:monospace;'>
		[pizazz store=holidaybug keywords=asparagus thumbsize=64 rows=5]
	</blockquote>
	<p>This text can be put directly into your pages and posts regardless of
	current editing mode. For more information see the
	<a href='http://codex.wordpress.org/Shortcode'>WordPress Codex</a>.</p>
	<?php
}

/** Output the JavaScript used in the settings page. */
function pizazz_settings_javascript() {
	?>
	<script type="text/javascript">
		function pizazz_clear_settings_click() {
			var data = {
				action: 'pizazz_clear_cache'
			};

			jQuery.post( ajaxurl, data, function( response ) {
				alert( response );
			});
		}
	</script>
	<?php
}

/** Clear Pizazz's cache by request via AJAX. */
function pizazz_ajax_clear_cache() {
	// clear the cache
	$cache = new Pizazz_Cache();
	$cache->clear_all();

	// print out response for javscript alert() and return
	echo 'Cache cleared successfully!';
	die();
}

/** Output JavaScript for closing the associate banner. */
function pizazz_associate_banner_javascript() {
	?>
	<script type="text/javascript">
		function pizazz_close_associate_banner() {
			var data = {
				action: 'pizazz_close_associate_banner'
			};

			jQuery.post( ajaxurl, data );

			document.getElementById( 'pizazz-associate-banner' ).style.display = 'none';
		}
	</script>
	<?php
}

/** Output the banner notifying the user that they should set an associate ID. */
function pizazz_associate_banner_text() {
	?>
	<div id='pizazz-associate-banner' class='updated' style='background-color:#00cd83;border-color:#006742;'><p>
	Don't forget to set your Zazzle associate ID for extra mula!
	Visit <a href='options-general.php?page=pizazz'>Settings->Pizazz</a>.
	<a href='javascript:pizazz_close_associate_banner();'>[Stop Showing This Message]</a>
	</p></div>
	<?php
}

/** Permanently close the banner output in the previous function. */
function pizazz_close_associate_banner() {
	update_option( 'pizazz_display_associate_banner', false );

	// print out response for javscript alert() and return
	echo 'This banner will never be shown again! Unless you reinstall Pizazz, that is, as that resets the settings.';
	die();
}

// register action hooks
add_action( 'admin_init', 'pizazz_register_settings' );
add_action( 'admin_menu', 'pizazz_create_menu' );
add_action( 'admin_head', 'pizazz_settings_javascript' );
add_action( 'wp_ajax_pizazz_clear_cache', 'pizazz_ajax_clear_cache' );

// register associate id hook if no associate ID and not disabled
global $pizazz_associate_id;
global $pizazz_display_associate_banner;
if ( $pizazz_associate_id == '' and $pizazz_display_associate_banner ) {
	add_action( 'admin_head', 'pizazz_associate_banner_javascript' );
	add_action( 'admin_notices', 'pizazz_associate_banner_text' );
	add_action( 'wp_ajax_pizazz_close_associate_banner', 'pizazz_close_associate_banner' );
}
