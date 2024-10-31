<?php
/*
Plugin Name: Pizazz
Plugin URI: http://entertainingsoftware.com/wordpress/pizazz/
Description: Zazzle feed widget; to edit global settings, see <a href="options-general.php?page=pizazz">Settings->Pizazz</a>
Version: 1.3.1
Author: Luiji Maryo
Author URI: http://profiles.wordpress.org/luiji
License: GPLv2 or later
Text Domain: pizazz
Domain Path: /languages/
*/

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

/**
 * @package   Pizazz
 * @author    Luiji <luiji@users.sourceforge.net>
 * @copyright 2012 Entertaining Software, Inc.
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GPLv2+
 *
 * Pizazz Zazzle feed plugin
 */

// prevent people from trying to access this file directly
if ( ! function_exists( 'add_action' ) ) {
	echo "'Ello, 'ello, what's all this then? This is a WordPress plug-in, you can't call it directly.";
	exit;
}

/** Import a relative PHP file. */
function pizazz_require( $path ) {
	require( dirname( __FILE__ ) . '/' . $path );
}

// import non-optional php files
pizazz_require( 'download.php' );
pizazz_require( 'shortcodes.php' );
pizazz_require( 'class.pizazz-product.php' );
pizazz_require( 'class.pizazz-query.php' );
pizazz_require( 'class.pizazz-widget.php' );

/** Register the Pizazz widget with WordPress. */
function pizazz_register_widget() {
	register_widget( 'Pizazz_Widget' );
}

/** Initialize Pizazz. */
function pizazz_init() {
	global $pizazz_shelf_life;
	global $pizazz_retry_count;
	global $pizazz_display_associate_banner;
	global $pizazz_associate_id;
	global $pizazz_target_blank;
	global $pizazz_target_blank_addon;
	global $pizazz_country;
	global $pizazz_zazzle_com;

	// load up any translation data
	load_plugin_textdomain( 'pizazz', false, basename( dirname( __FILE__ ) ) . '/languages' );

	// register global pizazz options
	add_option( 'pizazz_shelf_life', 3600 );
	add_option( 'pizazz_retry_count', 5 );
	add_option( 'pizazz_display_associate_banner', true );
	add_option( 'pizazz_associate_id', '' );
	add_option( 'pizazz_target_blank', false );
	add_option( 'pizazz_country', 'us' );

	// pull in shelf life and correct
	$pizazz_shelf_life = intval( get_option( 'pizazz_shelf_life' ) );
	if ( 0 > $pizazz_shelf_life )
		$pizazz_shelf_life = 0;

	// pull in retry count and correct
	$pizazz_retry_count = intval( get_option( 'pizazz_retry_count' ) );
	if ( 0 >= $pizazz_retry_count )
		$pizazz_retry_count = 1;

	// pull in 'display associate banner', associate id and whether or not links should target _blank
	$pizazz_display_associate_banner = (boolean) get_option( 'pizazz_display_associate_banner' );
	$pizazz_associate_id = (string) get_option( 'pizazz_associate_id' );
	$pizazz_target_blank = (boolean) get_option( 'pizazz_target_blank' );

	// this string should be input to all links to support target _blank
	$pizazz_target_blank_addon = $pizazz_target_blank ? 'target="_blank"' : '';

	// pull in country (not sanitized!)
	$pizazz_country = (string) get_option( 'pizazz_country' );

	// generate zazzle URL
	$pizazz_zazzle_com = 'zazzle.com';
	switch( $pizazz_country ) {
		case 'uk': $pizazz_zazzle_com = 'zazzle.co.uk'; break;
		case 'ca': $pizazz_zazzle_com = 'zazzle.ca'; break;
		case 'au': $pizazz_zazzle_com = 'zazzle.com.au'; break;
		case 'jp': $pizazz_zazzle_com = 'zazzle.co.jp'; break;
		case 'de': $pizazz_zazzle_com = 'zazzle.de'; break;
		case 'es': $pizazz_zazzle_com = 'zazzle.es'; break;
		case 'br': $pizazz_zazzle_com = 'zazzle.com.br'; break;
		case 'se': $pizazz_zazzle_com = 'zazzle.se'; break;
		case 'fr': $pizazz_zazzle_com = 'zazzle.fr'; break;
	}

	// should settings.php be loaded?
	$load_settings_php = ( is_admin() and ( ! is_network_admin() ) );

	// import the caching system if caching is enabled
	// it is also enabled in admin mode due to the "Clear Cache" button
	if( $pizazz_shelf_life or $load_settings_php )
		pizazz_require( 'class.pizazz-cache.php' );

	// import administrative external source files when needed
	if ( $load_settings_php )
		pizazz_require( 'admin.php' );
}

// queue initialization of the widget _after_ all functions are initialized
add_action( 'init', 'pizazz_init' );

// queue the widget to be registered _after_ the widget subsystem is initialized
add_action( 'widgets_init', 'pizazz_register_widget' );
