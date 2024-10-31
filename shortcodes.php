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

/** Register the shortcodes defined by Pizazz. */
function pizazz_register_shortcodes() {
	add_shortcode( 'pizazz', 'pizazz_function' );
}

/**
 * Callback for the [pizazz] shortcode.
 *
 * @param array $atts Attributes specified by the user
 * @return String result of the shortcode
 */
function pizazz_function( $atts ) {
	global $pizazz_associate_id;

	// extract the attributes from the shortcode
	extract( shortcode_atts( array(
		'store' => '',
		'keywords' => '',
		'thumbsize' => 125,
		'thumbback' => 'ffffff',
		'columns' => 2,
		'rows' => 2,
		'alignment' => 'below',
		'price' => 'hide',
		'seemore' => 'show'
	), $atts ) );

	$query = new Pizazz_Query( $store, $keywords, $thumbsize, $thumbback, $pizazz_associate_id );
	return $query->to_html( intval( $columns ), intval( $rows ), $alignment, $price, $seemore );
}

// queue up shortcode registration
add_action( 'init', 'pizazz_register_shortcodes' );
