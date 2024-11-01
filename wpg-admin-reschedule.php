<?php
 /* This file is part of the wp-greet plugin for WordPress */

/*
  Copyright 2015  Hans Matzen  (email : webmaster at tuxlog.de)

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

// include WordPress stuff

// Kleine Routine zu Ermittlung wo wor wp-load.php finden
// Wird wp-load.php nicht gefunden, dann wird ein Fehler ausgegeben.
//

// Wenn man das Verzeichnis wp-content ausserhalb der normalen Verzeichnissstruktur angelegt hat
// dann muss man die Variable wppath auf diesen Pfad einstellen
$wppath = '';

// Prüfen ob der load path schon definiert ist
if ( ! defined( 'WP_LOAD_PATH' ) ) {
	// hier ligt wp-load.php, bei der Standardinstallation
	$std_path = dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/';

	if ( file_exists( $std_path . 'wp-load.php' ) ) {
		require_once $std_path . 'wp-load.php';
	} elseif ( file_exists( $wppath . 'wp-load.php' ) ) {
		require_once $wppath . '/' . 'wp-load.php';
	} else {
		exit( 'wp-load.php not found. Please set path in wpg-admin-reschedule.php' );
	}
}

// get translation
load_plugin_textdomain( 'wp-greet', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

// get sql object
global $wpdb;
$wpdb->show_errors( true );

//
// reschedule starten
//
$start = '';
if ( array_key_exists( 'startreschedule', $_POST ) ) {
	$start = $_POST['startreschedule'];
}

if ( ( $start == 1 ) ) {
	// wp-greet optionen aus datenbank lesen
	$wpg_options = wpgreet_get_options();

	_e( 'Deleting active wp-greet events...', 'wp-greet' );
	wp_clear_scheduled_hook( 'wpgreet_sendcard_link' );
	wp_clear_scheduled_hook( 'wpgreet_sendcard_mail' );

	$crons = _get_cron_array();
	if ( empty( $crons ) ) {
		return;
	}

	foreach ( $crons as $times => $entry ) {
		if ( isset( $entry['wpgreet_sendcard_link'] ) ) {
			unset( $crons[ $times ]['wpgreet_sendcard_link'] );
		}

		if ( isset( $entry['wpgreet_sendcard_mail'] ) ) {
			unset( $crons[ $times ]['wpgreet_sendcard_mail'] );
		}
	}
	_set_cron_array( $crons );

	_e( 'done.', 'wp-greet' );
	echo "\n";

	_e( 'Reschedule future cards...', 'wp-greet' );
	$sql = 'select * from ' . $wpdb->prefix . "wpgreet_cards where future_send<>'0000-00-00 00:00:00' and card_sent='0000-00-00 00:00:00';";
	$res = $wpdb->get_results( $sql );

	foreach ( $res as $r ) {
		if ( $r->fetchcode != '' ) {
			wp_schedule_single_event(
				mysql2date( 'U', $r->future_send ),
				'wpgreet_sendcard_link',
				array(
					$r->mid,
					$r->frommail,
					$r->fromname,
					$r->tomail,
					$r->toname,
					$wpg_options['wp-greet-ocduration'],
					$r->fetchcode,
					$r->cc2from,
					false,
				)
			);
		} else {
			wp_schedule_single_event(
				mysql2date( 'U', $r->future_send ),
				'wpgreet_sendcard_mail',
				array(
					$r->mid,
					$r->frommail,
					$r->fromname,
					$r->tomail,
					$r->toname,
					$r->subject,
					$r->mailbody,
					$r->picture,
					$r->cc2from,
					false,
				)
			);
		}
	}

	_e( 'done.', 'wp-greet' );
	echo "\n";

	// you must end here to stop the displaying of the html below
	exit( 0 );
}

//
// dialog ausgeben ===================================================
//
$out = '';

// add log area style
$out .= '<style>#message {margin:20px; padding:20px; background:#cccccc; color:#cc0000;}</style>';

$out .= '<div id="rescheduleform" class="wrap" >';
$out .= '<h2>wp-greet ' . __( 'Reschedule Future Cards', 'wp-greet' ) . '</h2><br/>';

// add submit button to form
$out .= '<p class="submit">';
$out .= '<input type="submit" name="startreschedule" id="startreschedule" value="' .
	__( 'Start', 'wp-greet' ) . ' &raquo;" onclick="submit_this(\'reschedule\')" />';
$out .= '&nbsp;&nbsp;&nbsp;';
$out .= '<input type="submit" name="cancelreschedule" id="cancelreschedule" value="' .
	__( 'Close', 'wp-greet' ) . '" onclick="tb_remove();" />';
$out .= '</p>' . "\n";
$out .= '<hr />' . "\n";

// div container fuer das verarbeitungs log
$out .= '<textarea name="message" id="message" cols="55" rows="5">&nbsp;</textarea>';
echo $out;

