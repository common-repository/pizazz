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

/**
 * Pizazz widget class
 */
class Pizazz_Widget extends WP_Widget {

	/**
	 * Constructor. Initializes the widget and finalizes its
	 * registration with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'pizazz_widget',
			__( 'Pizazz Widget', 'pizazz' ),
			array( 'description' => __( 'Zazzle feed widget', 'pizazz' ) )
		);
	}

	/**
	 * Output a property for the widget's form.
	 *
	 * @param array  $instance Widget instance datas
	 * @param string $name     Internal name of the property
	 * @param string $title    Translated title of the property (HTML)
	 * @param mixed  $default  Default value for the propery
	 * @param array  $combo    Combo box values (defaults to null for text box)
	 */
	function form_property( $instance, $name, $title, $default, $combo = null ) {
		// get the property's value (or the default if unset)
		if ( isset ( $instance[$name] ) )
			$value = esc_attr( $instance[$name] );
		else
			$value = esc_attr( $default );

		// get the property's html id and name
		$html_id = $this->get_field_id( $name );
		$html_name = $this->get_field_name( $name );

		echo '<p>';
		echo "<label for='$html_id'>$title</label>";

		// combo box if $combo is a table, otherwise it is a text box
		if ( $combo ) {
			echo "<select class='widefat' id='$html_id' name='$html_name'>";
			foreach ($combo as $key => $name) {
				$selected = '';
				if ( $key == $value ) {
					$selected = 'selected="selected"';
				}

				echo "<option value='$key' $selected>$name</option>";
			}
			echo '</select>';
		}
		else {
			echo "<input class='widefat' id='$html_id' name='$html_name' type='text' value='$value' />";
		}

		echo '</p>';
	}

	/**
	 * Output the options form for administrator.
	 *
	 * @param array $instance Values associated with the widget instance
	 */
	function form( $instance ) {
		echo '<p><em>', __( 'All fields below may be left blank.', 'pizazz' ), '</em></p>';
		echo '<p><em>', __( 'Set the associate ID under <a href="options-general.php?page=pizazz">Settings->General</a>.', 'pizazz' ), '</em></p>';

		$this->form_property( $instance, 'title', __( 'Title:', 'pizazz' ), __( 'Zazzle Products', 'pizazz' ) );
		$this->form_property( $instance, 'store', __( 'Store:', 'pizazz' ), '' );
		$this->form_property( $instance, 'keywords', __( 'Keywords (i.e. "christmas shirt"):', 'pizazz' ), '' );
		$this->form_property( $instance, 'thumbsize', __( 'Thumbnail Size (set to 0 if you do not want one):', 'pizazz' ), 125 );
		$this->form_property( $instance, 'thumbback', __( 'Thumbnail Color (<a href="http://en.wikipedia.org/wiki/Hex_colors#Hex_triplet" target="_blank">6-digit hex format</a> without "#")', 'pizazz' ), 'ffffff' );
		$this->form_property( $instance, 'columns', __( 'Column Count (must be at least 1):', 'pizazz' ), 1 );
		$this->form_property( $instance, 'rows', __( 'Row Count (must be at least 1):', 'pizazz' ), 3 );
		$this->form_property( $instance, 'alignment', __( 'Text Alignment', 'pizazz' ), 'below', array( 'above' => 'Above', 'below' => 'Below', 'left' => 'Left', 'right' => 'Right' ) );
		$this->form_property( $instance, 'price', __( 'Price Visibility', 'pizazz' ), 'hide', array( 'show' => 'Shown', 'hide' => 'Hidden' ) );
		$this->form_property( $instance, 'seemore', __( '"See More" Link Visibility', 'pizazz' ), 'show', array( 'show' => 'Shown', 'hide' => 'Hidden' ) );
	}

	/**
	 * Generate an array representing the widget's saved state.
	 *
	 * @param array $new_instance Data from the new widget instance
	 * @param array $old_instance Data from the old widget instance
	 * @return Array of values associated with the saved widget
	 */
	function update( $new_instance, $old_instance ) {
		global $pizazz_zazzle_com;

		// pull in old instance's rss query parameters
		$ostore = $old_instance['store'];
		$okeywords = $old_instance['keywords'];
		$othumbback = $old_instance['thumbback'];

		// if rss query parameter changed, clear old cache entry
		// the chances of someone having multiple instances of the
		// query parameters on multiple widgets and not wanting to
		// keep them constantly sync'd is unlikely, so the overhead
		// of deleting a shared cache is negligible
		if( $new_instance['store'] != $ostore or
		    $new_instance['keywords'] != $okeywords or
		    $new_instance['thumbback'] != $othumbback ) {
			$cache = new Pizazz_Cache();
			$cache->clear_rss( sha1( "http://www.$pizazz_zazzle_com/$ostore/feed?qs=$okeywords&bg=$othumbback&st=popularity" ) );
		}

		// return the resulting array
		return array(
			'title' => strip_tags( $new_instance['title'] ),
			'store' => strip_tags( $new_instance['store'] ),
			'keywords' => strip_tags( $new_instance['keywords'] ),
			'thumbsize' => intval( $new_instance['thumbsize'] ),
			'thumbback' => strip_tags( $new_instance['thumbback'] ),
			'columns' => intval( $new_instance['columns'] ),
			'rows' => intval( $new_instance['rows'] ),
			'alignment' => strip_tags( $new_instance['alignment'] ),
			'price' => strip_tags( $new_instance['price'] ),
			'seemore' => strip_tags( $new_instance['seemore'] )
		);
	}

	/**
	 * Output the widget to the WordPress website.
	 *
	 * @param array $args     Arguments effecting the widget's output
	 * @param array $instance Values associated with the widget
	 */
	function widget( $args, $instance ) {
		global $pizazz_associate_id;

		// begin the widget
		echo $args['before_widget'];

		// echo the title, if any
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'];
			echo esc_html( $instance['title'] );
			echo $args['after_title'];
		}

		// get columns/rows, alignment and price/seemore visibility
		$columns = intval( $instance['columns'] );
		$rows = intval( $instance['rows'] );
		$alignment = $instance['alignment'];
		$price = $instance['price'];
		$seemore = $instance['seemore'];

		// generate the query
		$query = new Pizazz_Query(
			$instance['store'],
			$instance['keywords'],
			$instance['thumbsize'],
			$instance['thumbback'],
			$pizazz_associate_id
		);

		// output tabular version of query
		echo $query->to_html( $columns, $rows, $alignment, $price, $seemore );

		// end the widget
		echo $args['after_widget'];
	} // function widget

} // class Pizazz_Widget
