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

// ensure we actually want to uninstall
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	echo "'Ello, 'ello, what's all this then? Uninstallation must be done from the WordPress dashboard!";
	exit;
}

// delete options
delete_option( 'pizazz_shelf_life' );
delete_option( 'pizazz_retry_count' );
delete_option( 'pizazz_display_associate_banner' );
delete_option( 'pizazz_associate_id' );
