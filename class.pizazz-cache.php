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

/** Default directory for cached files */
define( 'PIZAZZ_DEFAULT_CACHE', dirname( __FILE__ ) . '/cache' );

/**
 * Cache management library
 */
class Pizazz_Cache {

	/** Directory where cached data is to be stored */
	private $dir = '/tmp';

	/**
	 * Constructor. Initialize with specified cache directory.
	 *
	 * @param string $dir Directory where cached data is to be stored
	 */
	function __construct( $dir = PIZAZZ_DEFAULT_CACHE ) {
		// save the directory attribute (naturally)
		$this->dir = $dir;

		// clear out expired data; this seems to be a good place to
		// do it because it should only be called once per widget
		// directory and will be called before get_rss()
		$this->clear_expired();
	}

	/**
	 * Clear out all expired data.
	 */
	function clear_expired() {
		global $pizazz_shelf_life;

		$dir = $this->dir;

		// iterate through files and delete lethally old ones
		if ( $dhandle = opendir( $dir ) )
			while ( ( $node = readdir( $dhandle ) ) !== false ) {
				$cache = "$dir/$node";
				if ( $node != '.' and $node != '..' and filemtime( $cache ) < (time() - $pizazz_shelf_life) )
					unlink( $cache );
			}
	}

	/**
	 * Clear out all cached data, expired or otherwise.
	 */
	function clear_all() {
		$dir = $this->dir;

		if ( $dhandle = opendir( $dir ) )
			while ( ( $node = readdir( $dhandle ) ) !== false ) {
				$cache = "$dir/$node";
				if ( $node != '.' and $node != '..' )
					unlink( $cache );
			}
	}

	/**
	 * Clear out specified RSS entry, expired or not.
	 *
	 * @param string $id Unique ID associated with the cache entry
	 */
	function clear_rss( $id ) {
		$cache = $this->dir . '/' . $id . '.rss';
		if ( @file_exists( $cache ) )
			unlink( $cache );
	}

	/**
	 * Get an RSS entry from the specified cache.
	 *
	 * @param string $id  Unique ID associated with the cache entry
	 * @param string $url URL where the RSS feed originates
	 * @return string containing the rss data
	 */
	function get_rss( $id, $url ) {
		global $pizazz_shelf_life;
		global $pizazz_retry_count;

		// get the directory name into a local variable
		$dir = $this->dir;

		// ensure that the directory exists
		if ( ! is_dir( $this->dir ) )
			throw new Exception( "Error: cache '$dir' is not a directory." );

		// ensure that the directory is writable
		if ( ! is_writable( $this->dir ) )
			throw new Exception( "Error: cache '$dir' is not writable." );

		// determine the path of the cache file
		$cache = "$dir/$id.rss";

		// check if there is already a cached file
		$already_exists = @file_exists( $cache );

		// if the file does not exist or has expired, try to update it
		if ( ! $already_exists or filemtime( $cache ) < (time() - $pizazz_shelf_life) )
		{
			// whether or not there was a successful try
			// true if it did, an error message otherwise
			$succeeded = 'Error: internal error: for-loop malformed.';

			// attempt to download and parse the RSS RETRY times
			for ( $i = 0; $i < $pizazz_retry_count; ++$i ) {
				// try to download the feed
				try {
					$data = pizazz_download_rss( $url );
				}
				catch( Exception $e ) {
					$succeeded = $e->getMessage();
					continue;
				}

				// open up the cache file
				$handle = @fopen( $cache, 'wb' );
				if( ! $handle ) {
					$succeeded = "Error: Failed to open '$cache' for writing.";
					continue;
				}

				// write out the data to the cache file
				if ( ! @fwrite( $handle, $data ) ) {
					$succeeded = "Error: Failed to write data to '$cache'.";
					@fclose( $handle );
					continue;
				}

				// close the cache file
				if ( ! @fclose( $handle ) ) {
					$succeded = "Error: Failed to save '$cache' to disk.";
					continue;
				}

				// we made it through!
				$succeeded = true;
				break;
			}

			// die if we didn't succeed and there is no cached data
			if ( $succeeded !== true and ! $already_exists )
				throw new Exception( $succeeded );
		}

		// load the data and return it
		return file_get_contents( $cache );
	} // function get_rss

} // class Pizazz_Cache
