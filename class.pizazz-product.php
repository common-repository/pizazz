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
 * Zazzle product parser and storage class
 */
class Pizazz_Product extends WP_Widget {

	/** Product title (defaults to "(Plugin Error)") */
	private $title = '(Plugin Error)';

	/** Product price (defaults to "$?.??") */
	private $price = '$?.??';

	/** Product link (defaults to example.com) */
	private $link = 'http://example.com';

	/** Thumbnail URI (defaults to Zazzle's 1x1 blank image) */
	private $thumbnail = 'http://asset.zcache.com/assets/graphics/design/err/blank.gif';

	/**
	 * Constructor. Initializes the product with a SimpleXML node.
	 *
	 * @param string $item rss text from <item> to </item>
	 */
	function __construct( $item ) {
		$matches = null;

		// TODO : handle missing attributes MUCH better

		// extract the title
		if ( preg_match( '~<title><!\[CDATA\[(.*?)]]></title>~', $item, $matches ) )
			$this->title = $matches[1];

		// extract the price
		if ( preg_match( '~<price>(.*?)</price>~', $item, $matches ) )
			$this->price = $matches[1];

		// extract the link
		if ( preg_match( '~<link>(.*?)</link>~', $item, $matches ) )
			$this->link = $matches[1];

		// extract the thumbnail
		if ( preg_match( '~<media:thumbnail url="(.*?)" />~', $item, $matches ) )
			$this->thumbnail = $matches[1];
	}

	/**
	 * Resize the thumbnail by adjusting its URI. Note that the width
	 * and height are always equal, so only one dimension is given.
	 *
	 * @param int $thumbsize Size of the thumbnail
	 */
	function resize_thumbnail( $thumbsize ) {
		if ( 0 == $thumbsize ) {
			// do not use a thumbnail
			$this->thumbnail = '';
		}
		else {
			// the digits after the last underscore indicate the size (width always equals height)
			$matches = null;
			if ( preg_match( '~(.*)_[^_]+\.jpg(.*)~', $this->thumbnail, $matches ) )
				$this->thumbnail = $matches[1] . "_$thumbsize.jpg" . $matches[2];
		}
	}

	/**
	 * Set associate ID for referrals by adjusting product link. This is
	 * a very important feature: whoever gets that referral earns 15% of
	 * all sales made through the link.
	 *
	 * @param string $associate Zazzle 18-digit associate ID
	 */
	function set_associate( $associate ) {
		// encode the associate id
		$assoc = urlencode( $associate );

		// append rf=$assoc, using a ? if it is the lone parameter and & if there is/are other(s)
		if ( strpos ( $this->link, '?' ) ) // there are other parameters
			$this->link .= "&rf=$assoc";
		else // this is the lone parameter
			$this->link .= "?rf=$assoc";
	}

	/**
	 * Get the HTML equivalent of the product.
	 *
	 * @param string $alignment Alignment of the text (above, below, left, right, defaults to below)
	 * @param string $showprice Price visibility ('show' or 'hide')
	 * @return String with the HTML equivalent of the product
	 */
	function to_html($alignment, $showprice) {
		global $pizazz_target_blank_addon; // see pizazz.php

		$result = '';

		// localize properties for simpler echo commands
		$title = $this->title;
		$price = $this->price;
		$link = $this->link;
		$thumb = $this->thumbnail;

		// force $alignment to a valid value
		if ( $alignment != 'above' && $alignment != 'below' && $alignment != 'left' && $alignment != 'right' )
			$alignment = 'below';

		// add price to title if wanted
		if ( 'show' == $showprice )
			$title .= " (<em>$price</em>)";

		// get css float value (if any)
		$float = '';
		if ( 'left' == $alignment or 'right' == $alignment ) {
			$float = "float: $alignment; width: 50%;";
		}

		// text that will go above or below the thumbnail
		$text = "<div style='$float'><a href='$link' $pizazz_target_blank_addon>$title</a></div>";

		// output the textual link (if above)
		if ( 'above' == $alignment or 'left' == $alignment ) {
			$result .= $text;
		}

		// output the thumbnail if one was wanted
		if ( $thumb != '' ) {
			$result .= "<div style='$float'><a href='$link' $pizazz_target_blank_addon><img src='$thumb' alt='$title' /></a></div>";
		}

		// output the textual link (if not above)
		if ( 'below' == $alignment or 'right' == $alignment ) {
			$result .= $text;
		}

		return $result;
	}

}; // class Pizazz_Product
