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
 * Download an external RSS resoruce.
 *
 * @param string $url   URL of the RSS resource
 * @return Either a string with the exact download or a parsed SimpleXMLNode version
 * @throw Exception
 */
function pizazz_download_rss( $url ) {
	global $pizazz_retry_count;

	// last error message
	$last_error = 'Error: there were no errors, yet there was an error. Reality has been destroyed.';

	// attempt to download and parse the rss $pizazz_retry_count times
	for ( $i = 0; $i < $pizazz_retry_count; ++$i ) {
		// download the Zazzle RSS feed
		$request = wp_remote_get( $url, array( 'redirection' => false ) );

		// ensure the get was successful
		if ( is_wp_error ( $request ) ) {
			// get error message
			$error = $request->get_error_message();

			// if it was redirection, then the store does not exist and we should abort
			if ( strpos ( $error, 'redirect' ) ) {
				throw new Exception( "Error: the store '$store' cannot be found. Either it does not exist or Zazzle was inaccessible." );
			}
			// otherwise, it was an http exception and we should retry
			else {
				$last_error = "Error: $error";
				continue;
			}
		}

		// downloaded and verified! now return...
		return $request['body'];
	}

	// all tries failed; raise the last error message
	throw new Exception( $last_error );
} // function pizazz_download_rss
