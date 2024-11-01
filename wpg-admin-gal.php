<?php
/* This file is part of the wp-greet plugin for WordPress */

/*
  Copyright 2008-2014  Hans Matzen  (email : webmaster at tuxlog.de)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	die( 'You are not allowed to call this page directly.' ); }


// generic functions
require_once 'wpg-func.php';
require_once 'supp/supp.php';

//
// form handler for the admin dialog
//
function wpg_admin_gal() {
	global $wpg_options;
	// base url for links
	$thisform = 'admin.php?page=wp-greet/wpg_admin_gal.php';
	// get sql object
	$wpdb =& $GLOBALS['wpdb'];

	// wp-greet optionen aus datenbank lesen
	$wpg_options = wpgreet_get_options();

	// get translation
	load_plugin_textdomain( 'wp-greet', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	// check for ngg selected
	if ( $wpg_options['wp-greet-gallery'] == '-' ) {
		echo "<div class='updated'><p><strong>" . __( 'No gallery plugin selected.', 'wp-greet' ) . '</strong></p></div>';
		exit( 0 );
	}

	// if this is a POST call, save new values
	if ( isset( $_POST['savegals'] ) ) {
		$wpg_options['wp-greet-galarr'] = array();

		if ( $wpg_options['wp-greet-gallery'] == 'wp' ) {
			$wpdb    =& $GLOBALS['wpdb'];
			 $sql    = 'SELECT id,post_title FROM ' . $wpdb->prefix . "posts WHERE post_type in ('page','post') and ( post_content like '%[gallery%]%' or post_content like '%<!-- wp:gallery%-->%') order by id;";
			$pagearr = $wpdb->get_results( $sql );
			foreach ( $pagearr as $res ) {
				if ( $_POST[ 'gal_' . $res->id ] == '1' ) {
					$wpg_options['wp-greet-galarr'][] = $res->id;
				}
			}
		}  if ( $wpg_options['wp-greet-gallery'] == 'ngg' ) {
			$sql     = 'select gid from  ' . $wpdb->prefix . 'ngg_gallery order by gid;';
			$results = $wpdb->get_results( $sql );

			foreach ( $results as $res ) {
				if ( $_POST[ 'gal_' . $res->gid ] == '1' ) {
					  $wpg_options['wp-greet-galarr'][] = $res->gid;
				}
			}
		}
		wpgreet_set_options();

		// put message after update
		echo "<div class='updated'><p><strong>" . __( 'Settings successfully updated', 'wp-greet' ) . '</strong></p></div>';
	}

	//
	// output galleries table
	//
	$out  = '';
	$out .= '<div class="wrap">';
	$out .= tl_add_supp();
	$out .= '<h2>' . __( 'Galleries', 'wp-greet' ) . "</h2>\n";
	$out .= "<form name='savegals' id='savegals' method='post' action='#'>\n";
	$out .= "<table class=\"widefat\"><thead><tr>\n";
	$out .= '<th scope="col" style="width: 30px;">' . __( 'Active', 'wp-greet' ) . '</th>' . "\n";
	$out .= '<th scope="col" style="width: 30px;">' . __( 'ID', 'wp-greet' ) . '</th>' . "\n";
	$out .= '<th scope="col">' . __( 'Name', 'wp-greet' ) . '</th>' . "</tr></thead>\n";

	// gallery loop
	if ( $wpg_options['wp-greet-gallery'] == 'wp' ) {
		// load possible gallery pages
		$wpdb    =& $GLOBALS['wpdb'];
		$sql     = 'SELECT id,post_title FROM ' . $wpdb->prefix . "posts WHERE post_type in ('page','post') and ( post_content like '%[gallery%]%' or post_content like '%<!-- wp:gallery%-->%') order by id;";
		$pagearr = $wpdb->get_results( $sql );
		foreach ( $pagearr as $res ) {
			$out .= "<tr><td><input type='checkbox' name='gal_" . $res->id . "' value='1' ";

			if ( array_search( $res->id, $wpg_options['wp-greet-galarr'] ) !== false ) {
				$out .= 'checked="checked" ';
			}
			$out .= '/></td>';
			$out .= '<td>' . $res->id . '</td>';
			$out .= '<td>' . $res->post_title . "</td></tr>\n";
		}
	} elseif ( $wpg_options['wp-greet-gallery'] == 'ngg' ) {

		  $sql   = 'select name,gid from  ' . $wpdb->prefix . 'ngg_gallery order by name;';
		$results = $wpdb->get_results( $sql );
		foreach ( $results as $res ) {
			$out .= "<tr><td><input type='checkbox' name='gal_" . $res->gid . "' value='1' ";

			if ( array_search( $res->gid, $wpg_options['wp-greet-galarr'] ) !== false ) {
				$out .= 'checked="checked" ';
			}
			$out .= '/></td>';
			$out .= '<td>' . $res->gid . '</td>';
			$out .= '<td>' . $res->name . "</td></tr>\n";

		}
	}
	$out .= '</table>' . "\n";
	$out .= "<div class='submit'><input type='submit' name='savegals' value='" . __( 'Save', 'wp-greet' ) . " »' /></div></form></div>";
	echo $out;

}

