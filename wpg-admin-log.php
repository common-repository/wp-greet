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
function wpg_admin_log() {
	global $wpg_options;

	// base url for links
	$thisform = 'admin.php?page=wp-greet/wpg-admin-log.php';
	// get sql object
	$wpdb =& $GLOBALS['wpdb'];

	// wp-greet optionen aus datenbank lesen
	$wpg_options = wpgreet_get_options();

	// get translation
	load_plugin_textdomain( 'wp-greet', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	// if this is a POST call, save new values
	if ( isset( $_POST['clear_log'] ) ) {
		$sql     = 'delete from ' . $wpdb->prefix . 'wpgreet_stats;';
		$results = $wpdb->query( $sql );

		// put message after update
		echo "<div class='updated'><p><strong>Log cleared.</strong></p></div>";
	}

	// get parameters for page navigation
	$sql = 'select count(*) as anz from  ' . $wpdb->prefix . 'wpgreet_stats;';
	$res = $wpdb->get_row( $sql );

	$all_lines = $res->anz;

	if ( isset( $_POST['lines_per_page'] ) ) {
		$lines_per_page                       = (int) $_POST['lines_per_page'];
		$wpg_options['wp-greet-linesperpage'] = $lines_per_page;
		update_option( 'wp-greet-linesperpage', $lines_per_page );
	} else {
		$lines_per_page = $wpg_options['wp-greet-linesperpage'];
	}

	// just in case option is not yet set
	if ( ! $lines_per_page > 0 ) {
		$lines_per_page = 10;
	}

	$maxpage = ( $all_lines / $lines_per_page );
	if ( $all_lines % $lines_per_page > 0 ) {
		$maxpage += 1;
	}

	if ( isset( $_GET['activepage'] ) ) {
		$active_page = (int) $_GET['activepage'];
	} else {
		$active_page = 1;
	}

	//
	// output log table
	//
	$out  = '';
	$out .= '<div class="wrap">';
	$out .= tl_add_supp();
	$out .= '<h2>' . __( 'Greetcard Log', 'wp-greet' ) . "</h2>\n";

	// output naviagtion
	$out .= '<div style="font-size:11px">';
	$out .= "<form name='linesperpage' id='linesperpage' method='post' action='#'>";
	$out .= "<a href=\"$thisform&amp;activepage=0\">" . __( 'Show all', 'wp-greet' ) . '</a>&nbsp;&nbsp;';
	$out .= "<a href=\"$thisform&amp;activepage=" . (string) ( $active_page - 1 < 1 ? 1 : $active_page - 1 ) . '">&lt;</a>&nbsp;';
	for ( $i = 1;$i < ( $all_lines / $lines_per_page ) + 1;$i++ ) {
		if ( $active_page == $i ) {
			$out .= '<b>' . $i . '</b>&nbsp;';
		} else {
			$out .= "<a href=\"$thisform&amp;activepage=$i\">" . $i . '</a>&nbsp;';
		}
	}
	$out .= "<a href=\"$thisform&amp;activepage=" . (string) ( $active_page + 1 > $maxpage ? $active_page : $active_page + 1 ) . '">&gt;</a>&nbsp;&nbsp;';
	$out .= __( 'Lines per Page:', 'wp-greet' ) . "<input style=\"font-size:10px\" type='text' name='lines_per_page' value='" . $lines_per_page . "' size='4' /></form></div>";

	// output table
	$out .= "<table class=\"widefat\"><thead><tr>\n";
	$out .= '<th scope="col">' . __( 'No.', 'wp-greet' ) . '</th>' . "\n";
	$out .= '<th scope="col">' . __( 'Date/Time', 'wp-greet' ) . '</th>' . "\n";
	$out .= '<th scope="col">' . __( 'From', 'wp-greet' ) . '</th>' . "\n";
	$out .= '<th scope="col">' . __( 'To', 'wp-greet' ) . '</th>' . "\n";
	$out .= '<th scope="col">' . __( 'Image', 'wp-greet' ) . '</th>';
	$out .= '<th scope="col">' . __( 'IP-Adress', 'wp-greet' ) . '</th>';
	$out .= '<th scope="col">' . __( 'Message', 'wp-greet' ) . '</th>' . "</tr></thead>\n";
	// log loop
	$sql = 'select * from  ' . $wpdb->prefix . 'wpgreet_stats order by mid DESC ';
	// limit sql to wanted page
	if ( $active_page > 0 ) {
		$lstart = ( $active_page - 1 ) * $lines_per_page;
		$lcount = $lines_per_page;
		$sql   .= " limit $lstart,$lcount";
	}
	$results = $wpdb->get_results( $sql );
	if ( empty( $results ) ) {
		$out .= "<tr><td colspan='6'>&nbsp;</td></tr>";
	}
	foreach ( $results as $res ) {
		$out .= "<tr><td class='td-admin'>" . $res->mid . '</td>';
		$out .= '<td>' . $res->senttime . '</td>';
		$out .= '<td>' . $res->frommail . '</td>';
		$out .= '<td>' . $res->tomail . '</td>';
		if ( trim( $res->picture ) != '' ) {
			$out .= "<td><img src='" . $res->picture . "' width='60' alt='" . $res->picture . "' /></td>";
		} else {
			$out .= '<td>&nbsp;</td>';
		}
		$out .= '<td>' . $res->remote_ip . '</td>';
		$out .= '<td>' . esc_attr( $res->mailbody ) . "</td></tr>\n";

	}
	$out .= '</table></div>' . "\n";
	// output clear log form
	$out .= "<div class='submit'><form name='clearlog' id='clearlog' method='post' action='#'><input type='submit' name='clear_log' value='" . __( 'Clear Log', 'wp-greet' ) . " »' /></form></div>";
	echo $out;

}

