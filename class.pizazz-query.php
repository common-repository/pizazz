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
 * Zazzle store query engine
 */
class Pizazz_Query {

	/** Name of the store/gallery (empty for global search) */
	private $store = '';

	/** Keywords used during the search */
	private $keywords = '';

	/** Square size for the thumbnails */
	private $thumbsize = 125;

	/** Thumbnail background color in non-hash-prefixed hex format */
	private $thumbback = 'ffffff';

	/** Associate ID to attach to the products */
	private $associate = '';

	/**
	 * Constructor. Initializes the product with basic attributes.
	 *
	 * @param string $store     Store name
	 * @param string $keywords  Keywords
	 * @param int    $thumbsize Thumbnail size
	 * @param string $thumbback Thumbnail background
	 * @param string $associate Zazzle associate ID
	 */
	function __construct( $store, $keywords, $thumbsize, $thumbback, $associate ) {
		$this->store = $store;
		$this->keywords = $keywords;
		$this->thumbsize = $thumbsize;
		$this->thumbback = $thumbback;
		$this->associate = $associate;
	}

	/**
	 * Process the query and get the resulting products.
	 *
	 * @param max Number or products that should be returned
	 * @return Array of resulting products (associate IDs already
	 *         applied) or a string containing an error message
	 */
	function process( $max ) {
		global $pizazz_zazzle_com;
		global $pizazz_shelf_life;

		// pull out variables
		$store = urlencode( $this->store );
		$keywords = urlencode( $this->keywords );
		$thumbsize = intval( $this->thumbsize );
		$thumbback = urlencode( $this->thumbback );
		$associate = $this->associate;

		$rssurl = "http://www.$pizazz_zazzle_com/$store/feed?qs=$keywords&bg=$thumbback&st=popularity";

		// download via cache if caching is enabled
		if ( $pizazz_shelf_life ) {
			// create the cache system
			$cache = new Pizazz_Cache();

			// download the Zazzle RSS feed via the cache library
			// sha1 is used to create unique IDs; collision risk negligable
			try {
				$rssxml = $cache->get_rss( sha1( $rssurl ), $rssurl );
			}
			catch( Exception $e ) {
				return $e->getMessage();
			}
		}
		// otherwise download the data directly
		else {
			try {
				$rssxml = pizazz_download_rss( $rssurl );
			}
			catch( Exception $e ) {
				return $e->getMessage();
			}
		}

		// get list of products
		$items = null;
		if( ! preg_match_all( '~<item>.*?</item>~s', $rssxml, $items ) ) {
			return 'Failed to find any products in the feed.';
		}

		// process products
		$products = array();
		$count = 0;
		foreach ( $items[0] as $item ) {
			// process the node information
			$product = new Pizazz_Product( $item );

			// configure the thumbnail size
			$product->resize_thumbnail( $thumbsize );

			// add the associate referral id if there was one
			if ( ! empty ( $associate ) )
				$product->set_associate( $associate );

			// append the product to the list
			$products[] = $product;

			// increment count
			++$count;
		}

		// ensure that the query was fruitful
		// TODO : print Store: and Keywords: information
		if ( 0 == $count )
			return 'Error: zero results found using the specified parameters.';

		// shorten the product count if there are more than the available cells
		if ( $count >= $max )
			$count = $max;

		// generate unique random numbers for products
		$wanted_product_nums = array_rand( $products, $count );

		// ensure $wanted_product_nums is an array
		if ( $count == 1 )
			$wanted_product_nums = array( $wanted_product_nums );

		// generate a final array of products
		$final_products = array();
		foreach ( $wanted_product_nums as $wanted_product_num )
			$final_products[] = $products[$wanted_product_num];

		// return the final array
		return $final_products;
	} // function process

	/**
	 * Get "See More" link target
	 *
	 * @return "See More" URL as a string
	 */
	function get_see_more_url() {
		global $pizazz_zazzle_com;

		// get and encode required strings
		$store = urlencode( $this->store );
		$keywords = urlencode( $this->keywords );
		$associate = urlencode( $this->associate );

		// return the new url
		return "http://$pizazz_zazzle_com/$store/$keywords+gifts?rf=$associate&st=popularity";
	}

	/**
	 * Get HTML table from the query
	 *
	 * @param int    $columns     Number of cells per vertical line
	 * @param int    $rows        Number of cells per horizontal line
	 * @param string $alignment   Alignment of the text (above, below, left, right)
	 * @param bool   $showprice   Price visibility ('show' or 'hide')
	 * @param bool   $showseemore "See More" link visibility ('show' or 'hide')
	 * @return String containing HTML-encoded table
	 */
	function to_html( $columns, $rows, $alignment, $showprice, $showseemore ) {
		global $pizazz_target_blank_addon; // see pizazz.php

		// assert that there is at least one column and one row
		$columns = $columns < 1 ? 1 : $columns;
		$rows < 1 ? 1 : $rows;

		// the resulting html
		$result = '';

		// process zazzle query and get results
		$products = $this->process( $rows * $columns );
		$product_count = count( $products );

		// if $products is a string, there was an error
		if ( 'string' == gettype( $products ) ) {
			$result .= "<p><strong>$products</strong></p>";
		}
		// otherwise, it was successful and should be echoed
		else {
			$result .= '<table style="width: 100%;">';

			$product = 0;
			for ( $row = 0; $row < $rows; ++$row ) {
				$result .= '<tr>';

				for ( $column = 0; $column < $columns; ++$column ) {
					$result .= '<td style="text-align: center; vertical-align: top;">';
					$result .= $products[$product++]->to_html($alignment, $showprice);
					$result .= '</td>';

					// we might not have enough products...
					if ( $product == $product_count )
						break 2;
				}

				$result .= '</tr>';
			}

			$result .= '</table>';
		}

		// add "see more" link
		if ( 'hide' != $showseemore ) {
			$seemore_url = $this->get_see_more_url();
			$seemore_txt = __( 'See More', 'pizazz' );
			$result .= "<div style='text-align: center;'><em><a href='$seemore_url' $pizazz_target_blank_addon>$seemore_txt</a></em></div>";
		}

		return $result;
	}

} // class Pizazz_Query
