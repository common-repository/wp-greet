<?php
/* This file is part of the wp-greet plugin for WordPress */

/*
  Copyright 2008-2016  Hans Matzen  (email : webmaster at tuxlog dot de)

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
	die(
		'You 
are not allowed to call this page directly.'
	); }


//
// reads all wp-greet options from the database
//
function wpgreet_get_options() {

	// the following parameters are supported by wp-greet
	// wp-greet-version - the version of wp-greet used
	// wp-greet-minseclevel - the minimal security level needed to send a card
	// wp-greet-captcha - use captcha to prevent spaming? true, false
	// wp-greet-mailreturnpath - the email address uses as the default return path
	// wp-greet-staticsender - the email address will always be used as sender
	// wp-greet-autofillform - if set to true, the fields are filled from the profile of the logged in user
	// wp-greet-bcc - send bcc to this adress
	// wp-greet-imgattach - dont send a link, send inline image (true,false)
	// wp-greet-default-title - default title for mail
	// wp-greet-default-header - default header for email
	// wp-greet-default-footer - default footer for email
	// wp-greet-logging - enables logging of sent cards
	// wp-greet-imagewidth - sets fixed width for the image
	// wp-greet-gallery - the used gallery plugin
	// wp-greet-formpage - the pageid of the form page
	// wp-greet-galarr - the selected galleries for redirection to wp-greet
	// as array
	// wp-greet-smilies - switch to activate smiley support with greeting form
	// wp-greet-linesperpage - count of lines to show on each page of log
	// wp-greet-usesmtp - which method to use for mail transfer 1=smtp, 0=php mail
	// wp-greet-touswitch - activates terms of usage feature 1=yes, 0=no
	// wp-greet-touswitch-text - the text/label for the terms of use checkbox
	// wp-greet-termsofusage - contains the html text for the terms of usage
	// wp-greet-mailconfirm - activates the confirmation mail feature 1=yes, 0=no
	// wp-greet-mctext - text for the confirmation mail
	// wp-greet-mcduration - valid time of the confirmation link
	// wp-greet-onlinecard - dont get cards via email, fetch it online, yes=1, no=0
	// wp-greet-fields - a string of 0 and 1 describing the mandatory fields in the form
	// wp-greet-show-ngg-desc - if active displays the description from  ngg below the image
	// wp-greet-external-link - if active uses external links from WordPress media for the link target of the images
	// wp-greet disable-css - if checked disables the load of the wp-greet.css file
	// wp-greet-use-wpml-lang - if checked uses the language of the gallery page for the form, works with WPML only
	// wp-greet-smtp-host - servername of SMTP server
	// wp-greet-smtp-port - port to use for SMTP connections (default:25)
	// wp-greet-smtp-ssl - if enabled uses SSL encryption for SMTP transfer
	// wp-greet-smtp-user - username for SMTP AUTH
	// wp-greet-smtp-pass - password for SMTP AUTH
	// wp-greet-sent-message - message to display when a card was sent
	// wp-greet-mail-from - email address to use as SMTP FROM address
	// wp-greet-mail-fromname - name to use as SMTP FROM NAME
	// wp-greet-mail-replyto - email address to use for reply-to SMTP header
	// wp-greet-confirm-sent-message - message to display when a confirmation mail was sent
	// wp-greet-audio - if enbaled offers background music for card
	// wp-greet-offerstamp - if enabled offers a list in the form to select different stamps from
	// wp-greet-offerstamp-buddypress - adds the BuddyPress group logo to the above
	// wp-greet-offerstamp-buddypress-adminonly - allows BuddyPress logo only for BuddyPress group admin

	$options = array(
		'wp-greet-version'                           => '',
		'wp-greet-minseclevel'                       => '',
		'wp-greet-captcha'                           => '',
		'wp-greet-mailreturnpath'                    => '',
		'wp-greet-staticsender'                      => '',
		'wp-greet-autofillform'                      => '',
		'wp-greet-bcc'                               => '',
		'wp-greet-imgattach'                         => '',
		'wp-greet-default-title'                     => '',
		'wp-greet-default-header'                    => '',
		'wp-greet-default-footer'                    => '',
		'wp-greet-imagewidth'                        => '',
		'wp-greet-logging'                           => '',
		'wp-greet-gallery'                           => '',
		'wp-greet-formpage'                          => '',
		'wp-greet-galarr'                            => array(),
		'wp-greet-smilies'                           => '',
		'wp-greet-linesperpage'                      => '',
		'wp-greet-usesmtp'                           => '',
		'wp-greet-stampimage'                        => '',
		'wp-greet-stamppercent'                      => '',
		'wp-greet-mailconfirm'                       => '',
		'wp-greet-mcduration'                        => '',
		'wp-greet-mctext'                            => '',
		'wp-greet-touswitch'                         => '',
		'wp-greet-termsofusage'                      => '',
		'wp-greet-touswitch-text'                    => '',
		'wp-greet-onlinecard'                        => '',
		'wp-greet-ocduration'                        => '',
		'wp-greet-octext'                            => '',
		'wp-greet-logdays'                           => '',
		'wp-greet-carddays'                          => '',
		'wp-greet-fields'                            => '',
		'wp-greet-show-ngg-desc'                     => '',
		'wp-greet-enable-confirm'                    => '',
		'wp-greet-future-send'                       => '',
		'wp-greet-multi-recipients'                  => '',
		'wp-greet-multi-recipients-bcc'              => '',
		'wp-greet-ectext'                            => '',
		'wp-greet-offerresend'                       => '',
		'wp-greet-tinymce'                           => '',
		'wp-greet-external-link'                     => '',
		'wp-greet-disable-css'                       => '',
		'wp-greet-use-wpml-lang'                     => '',
		'wp-greet-smtp-host'                         => '',
		'wp-greet-smtp-port'                         => '',
		'wp-greet-smtp-ssl'                          => '',
		'wp-greet-smtp-user'                         => '',
		'wp-greet-smtp-pass'                         => '',
		'wp-greet-sent-message'                      => '',
		'wp-greet-mail-from'                         => '',
		'wp-greet-mail-fromname'                     => '',
		'wp-greet-mail-replyto'                      => '',
		'wp-greet-confirm-sent-message'              => '',
		'wp-greet-audio'                             => '',
		'wp-greet-offer-stamp'                       => '',
		'wp-greet-offer-stamp-opacity'               => '',
		'wp-greet-offer-stamps'                      => '',
		'wp-greet-offer-stamps-buddypress'           => '',
		'wp-greet-offer-stamps-buddypress-adminonly' => '',
	);

	reset( $options );
	foreach ( $options as $key => $val ) {
		if ( $key != 'wp-greet-galarr' ) {
			$options[ $key ] = get_option( $key );
		} else {
			$options['wp-greet-galarr'] = unserialize( get_option( 'wp-greet-galarr' ) );
			if ( $options['wp-greet-galarr'] == false ) {
				$options['wp-greet-galarr'] = array();
			}
		}
	}

	return $options;
}


//
// writes the current options to the wp-options table
//
function wpgreet_set_options() {
	global $wpg_options;
	reset( $wpg_options );
	foreach ( $wpg_options as $key => $val ) {
		if ( is_array( $val ) ) {
			update_option( $key, serialize( $val ) );
		} else {
			update_option( $key, $val );
		}
	}
}


//
// function to check if an email adress is valid
// checks format and existance of mx record for mail host
//
function wpg_check_email( $email ) {
	// Leading and following whitespaces are ignored
	$email = trim( $email );
	// Email-address is set to lower case
	$email = strtolower( $email );
	// First, we check that there's one @ symbol,
	// and that the lengths are right.
	if ( ! preg_match( '/^[^@]{1,64}@[^@]{1,255}$/', $email ) ) {
		// Email invalid because wrong number of characters
		// in one section or wrong number of @ symbols.
		return false;
	}
	// Split it into sections to make life easier
	$email_array = explode( '@', $email );
	$local_array = explode( '.', $email_array[0] );
	for ( $i = 0; $i < sizeof( $local_array ); $i++ ) {
		if ( ! preg_match( "/^(([A-Za-z0-9!#$%&'*+\/=?^_`{|}~-][A-Za-z0-9!#$%&'*+\/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$/", $local_array[ $i ] ) ) {
			return false;
		}
	}
	// Check if domain is IP. If not,
	// it should be valid domain name
	if ( ! preg_match( '/^\[?[0-9\.]+\]?$/', $email_array[1] ) ) {
		$domain_array = explode( '.', $email_array[1] );
		if ( sizeof( $domain_array ) < 2 ) {
			return false; // Not enough parts to domain
		}
		for ( $i = 0; $i < sizeof( $domain_array ); $i++ ) {
			if ( ! preg_match( '/^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$/', $domain_array[ $i ] ) ) {
				return false;
			}
		}
	}

	// check for domain existence
	if ( function_exists( 'checkdnsrr' ) ) {
		if ( ! checkdnsrr( $email_array[1], 'MX' ) ) {
			return false;
		}
	}

	// no error found it must be a valid domain
	return true;
}



//
// ermittelt anhand der file extension den zugehoerigen mimetype
//
function get_mimetype( $fname ) {
	$ext = substr( $fname, strrpos( $fname, '.' ) + 1 );

	switch ( $ext ) {
		case 'jpeg':
		case 'jpg':
		case 'jpe':
		case 'JPG':
			$mtype = 'image/jpeg';
			break;
		case 'png':
			$mtype = 'image/png';
			break;
		case 'gif':
			$mtype = 'image/gif';
			break;
		case 'tiff':
		case 'tif':
			$mtype = 'image/tiff';
			break;
		default:
			$mtype = 'application/octet-stream';
			break;
	}
	return $mtype;
}

//
// erzeugt einen eintrag in der log tabelle mit den in den parametern angegeben werten
//
function log_greetcard( $to, $from, $pic, $msg ) {
	global $wpdb;

	$now = gmdate( 'Y-m-d H:i:s', time() + ( get_option( 'gmt_offset' ) * 60 * 60 ) );

	$sql = 'insert into ' . $wpdb->prefix . "wpgreet_stats values (0,'" . $now . "', '" . $from . "','" . $to . "','" . $pic . "','" . esc_sql( $msg ) . "','" . $_SERVER['REMOTE_ADDR'] . "');";

	$wpdb->query( $sql );
}


//
// fuegt die capability wp-greet-send zu allen rollen >= $role hinzu
//
function set_permissions( $role ) {
	global $wp_roles;

	$all_roles = $wp_roles->role_names;

	// das recht fuer alle rollen entfernen
	foreach ( $all_roles as $key => $value ) {
		$drole = get_role( $key );
		if ( ( $drole !== null ) and $drole->has_cap( 'wp-greet-send' ) ) {
			$drole->remove_cap( 'wp-greet-send' );
		}
	}

	foreach ( $all_roles as $key => $value ) {
		$crole = get_role( $key );
		if ( $crole !== null ) {
			$crole->add_cap( 'wp-greet-send' );
		}

		if ( $key == $role ) {
			break;
		}
	}
}

//
// verbindet wp-greet mit ngg, es wird die url angepasst
//
function ngg_connect( $link = '', $picture = '' ) {
	// fix ngg 2.0x new var names
	if ( isset( $picture->galleryid ) ) {
		$picture->gid = $picture->galleryid;
	}

	$wpdb =& $GLOBALS['wpdb'];
	// wp-greet optionen aus datenbank lesen
	$wpg_options = wpgreet_get_options();

	// pruefe ob gallery umgelenkt werden soll
	if ( array_search( $picture->gid, $wpg_options['wp-greet-galarr'] ) !== false ) {

		if ( isset( $picture->path ) ) {  // old ngg until 1.9.13
			$folder_url = get_option( 'siteurl' ) . '/' . $picture->path . '/';
		} else {
			$folder_url = get_option( 'siteurl' ) . '/';
		}

		$url_prefix = get_permalink( $wpg_options['wp-greet-formpage'] );

		// change base url if we are using WPML
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$new_url_prefix = $url_prefix;
			$lang_post_id   = icl_object_id( $wpg_options['wp-greet-formpage'], 'page', false, ICL_LANGUAGE_CODE );
			if ( is_null( $lang_post_id ) ) {
				$lang_post_id = icl_object_id( $wpg_options['wp-greet-formpage'], 'post', false, ICL_LANGUAGE_CODE );
			}

			if ( ! is_null( $lang_post_id ) ) {
				$new_url_prefix = get_permalink( $lang_post_id );
			}

			$url_prefix = $new_url_prefix;
		}

		if ( strpos( $url_prefix, '?' ) === false ) {
			$url_prefix .= '?';
		} else {
			$url_prefix .= '\&amp;';
		}

		// fix ngg 2.0x new var names
		if ( isset( $picture->path ) ) {  // old ngg until 1.9.13
			$link = $url_prefix . 'gallery=' . $picture->gid . '\&amp;image=' . $folder_url . $picture->filename;
		} else { // new ngg from 2.0.0 on
			$link = $url_prefix . 'gallery=' . $picture->gid . '\&amp;image=' . $link;
		}

		if ( defined( 'BWCARDS' ) or defined( 'AMACARDS' ) ) {
			$link .= '\&amp;pid=' . $picture->pid;
		}

		// support for WPML
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$link .= '\&amp;lang=' . ICL_LANGUAGE_CODE;
		}
	}
	return stripslashes( $link );
}

//
// entfernt den ngg thumbcode
//
function ngg_remove_thumbcode( $thumbcode, $picture ) {

	// wp-greet optionen aus datenbank lesen
	$wpg_options = wpgreet_get_options();

	// pruefe ob gallery umgelenkt werden soll
	if ( array_search( $picture->gid, $wpg_options['wp-greet-galarr'] ) !== false ) {
		$thumbcode = '';
	}
	return $thumbcode;
}

function get_dir_alphasort( $pfad ) {
	// Prüfung, ob das angegebene Verzeichnis geöffnet werden kann
	if ( ( $pointer = opendir( $pfad ) ) == false ) {
		// Im Fehlfall ist das bereits der Ausgang
		return false;
	}

	$arr = array();
	while ( $datei = readdir( $pointer ) ) {
		// Prüfung, ob es sich überhaupt um Dateien handelt
		// oder um Synonyme für das aktuelle (.) bzw.
		// das übergeordnete Verzeichnis (..)
		if ( is_dir( $pfad . '/' . $datei ) || $datei == '.' || $datei == '..' ) {
			continue;
		}

		$arr[] = $datei;
	}
	closedir( $pointer );
	array_multisort( $arr );

	return $arr;
}

function wpg_debug( $text ) {
	if ( is_array( $text ) || is_object( $text ) ) {
			error_log( print_r( $text, true ) );
	} else {
			error_log( $text );
	}
}

function test_gd() {
	$res  = '';
	$res .= 'GD support on your server: ';

	// Check if the function gd_info exists (way to know if gd is istalled)
	if ( function_exists( 'gd_info' ) ) {
		$res .= "YES\n";
		$gd   = gd_info();

		// Show status of all values that might be supported(unsupported)
		foreach ( $gd as $key => $value ) {
			$res .= $key . ': ';
			if ( $value ) {
				$re .= "YES\n";
			} else {
				$res .= "NO\n";
			}
		}
	} else {
		$res .= 'NO';
	}

	return $res;
}

//
// speichert eine karte  in der datenbank
//
function save_greetcard( $sender, $sendername, $recv, $recvname,
			$title, $message, $picurl, $cc2sender,
			$confirmuntil, $confirmcode, $fetchuntil, $fetchcode, $sendtime, $sessionid = '',
			$audiourl = '', $sendbcc = 0 ) {
	global $wpdb;
	$autoinc = -1;
	$sendbcc = intval( $sendbcc );

	$wpdb->show_errors( true );

	// convert to mysql date
	if ( $sendtime <= 0 ) {
		$sendtime = '';
	} else {
		$sendtime = date( 'Y-m-d H:i:s', $sendtime );
	}

	if ( $fetchcode == '' or $confirmcode == '' ) {

		$insertion = $wpdb->insert(
			$wpdb->prefix . 'wpgreet_cards',
			array(
				'mid'          => 0,
				'fromname'     => trim( $sendername ),
				'frommail'     => $sender,
				'toname'       => trim( $recvname ),
				'tomail'       => $recv,
				'cc2from'      => $cc2sender,
				'subject'      => $title,
				'picture'      => $picurl,
				'mailbody'     => $message,
				'confirmuntil' => $confirmuntil,
				'confirmcode'  => $confirmcode,
				'fetchuntil'   => $fetchuntil,
				'fetchcode'    => $fetchcode,
				'card_sent'    => '',
				'card_fetched' => '',
				'future_send'  => $sendtime,
				'session_id'   => $sessionid,
				'audiourl'     => $audiourl,
				'sendbcc'      => $sendbcc,
			),
			array( '%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d' )
		);

		$autoinc = $wpdb->insert_id;

	} else {
		$sql = 'select count(*) as anz from ' . $wpdb->prefix . "wpgreet_cards where confirmcode='$confirmcode';";

		$count = $wpdb->get_row( $sql );
		if ( $count->anz == 0 ) {
			$sql = 'insert into ' . $wpdb->prefix . "wpgreet_cards values (0, '$sendername', '$sender', '$recvname', '$recv', '$cc2sender', '" . esc_sql( $title ) . "', '$picurl','" . esc_sql( $message ) . "', '$confirmuntil', '$confirmcode','$fetchuntil', '$fetchcode','','','$sendtime','$sessionid','$audiourl',$sendbcc );";

			$wpdb->query( $sql );
			$autoinc = $wpdb->insert_id;
		} else {
			$sql = 'update ' . $wpdb->prefix . "wpgreet_cards set fetchuntil='$fetchuntil', fetchcode='$fetchcode' where confirmcode='$confirmcode';";
			$wpdb->query( $sql );
		}
	}
	return $autoinc;
}


//
// markiert die karte mit dem confirmcode oder fetchcode ccode als versendet
//
function mark_sentcard( $ccode ) {
	global $wpdb;
	$now = gmdate( 'Y-m-d H:i:s', time() + ( get_option( 'gmt_offset' ) * 60 * 60 ) );
	$sql = 'update ' . $wpdb->prefix . "wpgreet_cards set card_sent='$now' where confirmcode='" . $ccode . "' or fetchcode='" . $ccode . "';";
	$wpdb->query( $sql );
}

//
// markiert die karte mit der sessionid $sid als versendet
//
function bwmark_sentcard( $sid ) {
	global $wpdb;
	$now = gmdate( 'Y-m-d H:i:s', time() + ( get_option( 'gmt_offset' ) * 60 * 60 ) );
	$sql = 'update ' . $wpdb->prefix . "wpgreet_cards set card_sent='$now' where session_id='" . $sid . "';";
	$wpdb->query( $sql );
}

//
// markiert die karte mit dem fetchcode fcode als mindestens einmal abgeholt
//
function mark_fetchcard( $fcode ) {
	global $wpdb;
	$now = gmdate( 'Y-m-d H:i:s', time() + ( get_option( 'gmt_offset' ) * 60 * 60 ) );
	$sql = 'update ' . $wpdb->prefix . "wpgreet_cards set card_fetched='$now' where fetchcode='" . $fcode . "';";
	$wpdb->query( $sql );
}

//
// loescht alle karteneintraege die länger als das höchste mögliche abholdatum
// plus die die angegebene zahl an tagen sind
//
function remove_cards() {
	// wp-greet optionen aus datenbank lesen
	$wpg_options = wpgreet_get_options();

	// nichts löschen wenn der parameter auf 0 oder leer steht
	if ( $wpg_options['wp-greet-carddays'] == 0 or $wpg_options['wp-greet-carddays'] == '' ) {
		return;
	}

	// berechne höchstes gültiges  fetch datum
	$then = time() + ( get_option( 'gmt_offset' ) * 60 * 60 ) -
	( $wpg_options['wp-greet-carddays'] * 60 * 60 * 24 );
	$then = gmdate( 'Y-m-d H:i:s', $then );

	global $wpdb;
	$sql = 'delete from ' . $wpdb->prefix . "wpgreet_cards where fetchuntil < '$then';";
	$c   = $wpdb->query( $sql );

	if ( $c > 0 ) {
		log_greetcard( '', get_option( 'blogname' ), '', "Cards cleaned until $then" );
	}
}


//
// loescht alle logeinträge die länger als die vorgegebene anzahl von tagen
// in der tabelle stehen
//
function remove_logs() {
	// wp-greet optionen aus datenbank lesen
	$wpg_options = wpgreet_get_options();

	// nichts löschen wenn der parameter auf 0 oder leer steht
	if ( $wpg_options['wp-greet-logdays'] == 0 or $wpg_options['wp-greet-logdays'] == '' ) {
		return;
	}

	// berechne höchstes gültiges  fetch datum
	$then = time() + ( get_option( 'gmt_offset' ) * 60 * 60 ) -
	( $wpg_options['wp-greet-logdays'] * 60 * 60 * 24 );
	$then = gmdate( 'Y-m-d H:i:s', $then );

	global $wpdb;
	$sql = 'delete from ' . $wpdb->prefix . "wpgreet_stats where senttime < '$then';";
	$c   = $wpdb->query( $sql );

	if ( $c > 0 ) {
		log_greetcard( '', get_option( 'blogname' ), '', "Log cleaned until $then" );
	}
}

//
// wandelt ein mysql timestamp in einer zahl um, die die sekunden seit 1970
// wiedergibt. funktioniert für mysql4 und mysql5
//
function mysql2time( $m ) {
	// mysql5 2009-11-05 12:45:01
	if ( strpos( $m, ':' ) > 0 ) {
		return strtotime( $m );
	} else {
		// mysql 4 - 20091105124501
		preg_match( '/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', $m, $p );
		return mktime( $p[4], $p[5], $p[6], $p[2], $p[3], $p[1] );
	}
}

//
// erzeugt die url für das bild mit briefmarke
// $pic - url des bildes fuer die grusskarte
//
function build_stamp_url( $pic, $stamp ) {
	// wp-greet optionen aus datenbank lesen
	$wpg_options = wpgreet_get_options();

	$picpath = $pic;

	if ( $stamp != '' ) {
		$stampimg = $stamp;
	} else {
		$stampimg = $wpg_options['wp-greet-stampimage'];
	}

	if ( file_exists( $stampimg ) ) {
		$alttext = basename( $pic );
	} else {
		$alttext = __( 'Stampimage not found - Please contact your administrator', 'wp-greet' );
	}

	$link = site_url( 'wp-content/plugins/wp-greet/' ) . "wpg-stamped.php?cci=$picpath&amp;sti=" .
		$stampimg . '&amp;stw=' . $wpg_options['wp-greet-stamppercent'];

	return $link;
}

//
// generiert das img tag gemäß der eingestellten parameter
// wird verwendet für formular, voransicht und abruf
// berücksichtigt briefmarken, ngg daten einstellungen
//
function get_imgtag( $pid, $url ) {
	// do nothing without url
	if ( $url == '' ) {
		return '';
	}

	// hole optionen
	$wpg_options = wpgreet_get_options();
	$filename    = basename( $url );
	$imgtag      = '<div>';

	// wenn die URL bereits eine Briefmarke enthält verändern wir sie nicht
	if ( ! strpos( $url, 'wpg-stamped.php?' ) > 0 ) {
		// is there a ready url in the hidden part of the form, if yes take it
		if ( isset( $_POST['wp-greet-offer-stamp'] ) and trim( $_POST['wp-greet-offer-stamp'] ) != '' ) {
			$url = build_stamp_url( $url, $_POST['wp-greet-offer-stamp'] );
		} else {
			// kommt eine default briefmarke auf das bild?
			$stampit = ( trim( $wpg_options['wp-greet-stampimage'] ) != plugin_dir_url( __FILE__ ) . 'stamps/None.png' and
					   ( trim( $wpg_options['wp-greet-imgattach'] ) != '' or
						 trim( $wpg_options['wp-greet-onlinecard'] ) != '' ) );

			if ( $stampit ) {
				$url = build_stamp_url( $url, '' );
			}
		}
	}

	// wenn die URL bereits fix und fertig ist, nehmen wir die
	if ( isset( $_POST['wpg-image-url'] ) and trim( $_POST['wpg-image-url'] ) != '' ) {
		$url = $_POST['wpg-image-url'];
	}

	// breite ermitteln
	$width = '';
	if ( $wpg_options['wp-greet-imagewidth'] != '' ) {
		$width = $wpg_options['wp-greet-imagewidth'];
	}

	// nextgen gallery daten lesen
	global $nggdb;

	$ngg_desc    = '';
	$ngg_alttext = '';

	if ( ! $wpg_options['wp-greet-gallery'] == 'wp' and $wpg_options['wp-greet-show-ngg-desc'] and isset( $nggdb ) ) {
		$nggimg = $nggdb->search_for_images( substr( $url, strrpos( $url, '/' ) + 1 ) );
		// durchsuche das zurueckgelieferte array nach der richtigen Galerie, falls ein Bild in mehreren Galerien ist
		foreach ( $nggimg as $ni => $nv ) {
			if ( $nv->galleryid == $pid ) {
				if ( isset( $nggimg[ $ni ]->description ) ) {
					$ngg_desc = trim( $nggimg[0]->description );
				}
				if ( isset( $nggimg[ $ni ]->alttext ) ) {
					$ngg_alttext = trim( $nggimg[0]->alttext );
				}
			}
		}
	}

	$ext_url = '';
	if ( $pid > 0 and $wpg_options['wp-greet-external-link'] == '1' ) {
		$ext_url    = get_post_meta( $pid, 'wpgreet_external_link', true );
		$ext_target = get_post_meta( $pid, 'wpgreet_external_link_target', true );
	}

	$target = '';
	if ( isset( $ext_target ) && $ext_target == '1' ) {
			$target = "target='_blank'";
	}

	$imgtag .= '<div class="wpg_image">';
	$imgtag .= ( strlen( $ext_url ) > 0 ) ? "<a $target href='$ext_url'>" : '';
	$imgtag .= '<img id="wpg-image" src="' . $url . '" alt="';
	$imgtag .= ( strlen( $ngg_alttext ) > 0 ) ? $ngg_alttext : $filename;
	$imgtag .= '" title="';
	$imgtag .= ( strlen( $ngg_alttext ) > 0 ) ? $ngg_alttext : $filename;
	$imgtag .= '" width="' . $width . '"/>';
	$imgtag .= ( strlen( $ext_url ) > 0 ) ? '</a>' : '';
	$imgtag .= "<input type='hidden' id='wpg-image-url' name='wpg-image-url' value='" . $url . "' />";
	$imgtag .= "</div>\n";

	if ( $wpg_options['wp-greet-show-ngg-desc'] and strlen( $ngg_desc ) > 0 ) {
			$imgtag .= "<div class='wpg_image_description'>" . $ngg_desc . '</div>';
	}

	$imgtag .= '</div>';

	return $imgtag;
}

//
// If we run with a broken NGG Version >= 2.0.0 print a hint with the solution
// to give a chance ti fix it.
//
function wpg_fix_broken_ngg_hint() {
	$message      = '';
	$broken_since = '2.0.0';

	// if we do not use ngg, just return
	if ( ! is_plugin_active( 'nextgen-gallery/nggallery.php' ) ) {
		return;
	}

	global $wpg_options;
	$wpg_options = wpgreet_get_options();
	if ( $wpg_options['wp-greet-gallery'] == 'wp' ) {
		return;
	}

	$plugin_folder = plugin_dir_path( __FILE__ ) . '/../';
	$plugdata      = get_plugin_data( $plugin_folder . 'nextgen-gallery/nggallery.php', false, false );
	$ngg_version   = $plugdata['Version'];

	if ( is_plugin_active( 'nextgen-gallery/nggallery.php' ) and version_compare( $ngg_version, $broken_since, '>=' ) ) {
		// test if allready patched
		$ps = file_get_contents( $plugin_folder . 'nextgen-gallery/products/photocrati_nextgen/modules/nextgen_basic_gallery/templates/thumbnails/index.php' );

		if ( false === strpos( $ps, 'ngg_create_gallery_link' ) ) {

			$message = <<<EOL
			<h3>Urgent message from wp-greet</h3>
			<p>Unfortunately Photocrati did a major redesign of NGG and therfore the connecting filters for wp-greet were removed. 
			You can get a lot of details in the <a target="_blank" href="http://wordpress.org/support/plugin/nextgen-gallery">wordpress.org forums</a>.<br/> 
			To workaround this and make wp-greet work again please edit
			nextgen-gallery/products/photocrati_nextgen/modules/nextgen_basic_gallery/templates/thumbnails/index.php 
			<br />and change the line with</p>
		
			&lt;a href="&lt;?php echo esc_attr(\$storage->get_image_url(\$image))?>"
		
			to
		
			&lt;a href="&lt;?php echo apply_filters('ngg_create_gallery_link', esc_attr(\$storage->get_image_url(\$image)), \$image)?>"
		
			<p>Since NGG does not work with all Lightbox-Effects. Please set Gallery -> Other Options -> Lightbox Options to Shutter and do not select a template for the thumbnails,
			if you encounter problems with other settings.</p>
EOL;
		}
	}

	// print message
	if ( $message != '' ) {
		echo '<div id="message" class="error">';
		echo "<p><strong>$message</strong></p></div>";
	}
}

// functions to connect to WP native gallery
//
// create gallery with the different url filter active
//
function wpgreet_gallery_shortcode( $attr ) {
	global $post;
	$pid = $post->ID;

	// wp-greet optionen aus datenbank lesen
	$wpg_options = wpgreet_get_options();

	// check if we shall connect wp-greet
	// load connected gallery pages
	$connectus = in_array( (string) $pid, $wpg_options['wp-greet-galarr'] );

	// add the filter for attachment links:
	if ( $connectus ) {

		// remove responsive lightbox filter
		global $wp_filter;
		if ( isset( $wp_filter['wp_get_attachment_link'][1000] ) ) {
			$wp_filter['wp_get_attachment_link'][1000] = array();
		}

		add_filter( 'wp_get_attachment_link', 'wpgreet_gallery_link_filter', 10, 6 );

		// remove jetpack filters
		global $wp_filter;
		if ( isset( $wp_filter['post_gallery'][1000] ) ) {
			$wp_filter['post_gallery'][1000] = array();
		}
	}

	// get WordPress native gallery
	$gallery = gallery_shortcode( $attr );

	// Remove the filter for attachment links:
	if ( $connectus ) {
		remove_filter( 'wp_get_attachment_link', 'wpgreet_gallery_link_filter', 10 );
	}

	return $gallery;
}

//
// change link to wpgreet link
//
function wpgreet_gallery_link_filter( $full_link, $id, $size, $permalink, $icon, $text ) {
	// change the value of href to suite wp-greet form link

	// get post id
	$gid = $id;

	// extract image anchor url
	$xml = simplexml_load_string( $full_link );
	// get anchor link because this is what we want to replace later on
	$alist = $xml->xpath( '//@href' );
	$aitem = parse_url( $alist[0] );
	$aurl  = $aitem['scheme'] . '://' . $aitem['host'] . $aitem['path'];
	if ( isset( $aitem['query'] ) && strlen( $aitem['query'] ) > 0 ) {
		$aurl .= '?' . $aitem['query'];
	}

	// getting the img url this is what we want to give as a parm to wp-greet
	$url = wp_get_attachment_image_src( $id, 'full' );
	$url = $url[0];
	// get wp-greet optionen from database
	$wpg_options = wpgreet_get_options();

	// build wp-greet form-page link and add parms
	$url_prefix = get_permalink( $wpg_options['wp-greet-formpage'] );

	// change base url if we are using WPML
	if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		$new_url_prefix = $url_prefix;
		$lang_post_id   = icl_object_id( $wpg_options['wp-greet-formpage'], 'page', false, ICL_LANGUAGE_CODE );
		if ( is_null( $lang_post_id ) ) {
			$lang_post_id = icl_object_id( $wpg_options['wp-greet-formpage'], 'post', false, ICL_LANGUAGE_CODE );
		}

		if ( ! is_null( $lang_post_id ) ) {
			$new_url_prefix = get_permalink( $lang_post_id );
		}
		$url_prefix = $new_url_prefix;
	}

	if ( strpos( $url_prefix, '?' ) === false ) {
		$url_prefix .= '?';
	} else {
		$url_prefix .= '&amp;';
	}

	$link = $url_prefix . 'gallery=' . $gid . '\&amp;image=' . $url;

	// support for WPML
	if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		$link .= '\&amp;lang=' . ICL_LANGUAGE_CODE;
	}

	// replace anchor link to redirect to wp-greet
	$erg = stripslashes( str_replace( $aurl, $link, $full_link ) );

	return $erg;
}

//
// function to change the gallery html for wp-greet
//
function wpgreet_post_gallery( $val, $attr ) {
	if ( $val == '' ) {
		remove_filter( 'post_gallery', 'wpgreet_post_gallery', 9999 );
		$val = gallery_shortcode( $attr );
		add_filter( 'post_gallery', 'wpgreet_post_gallery', 9999, 2 );
	}

	global $post;
	$pid = $post->ID;

	// wp-greet optionen aus datenbank lesen
	$wpg_options = wpgreet_get_options();

	// check if we shall connect wp-greet
	// load connected gallery pages
	$connectus = in_array( (string) $pid, $wpg_options['wp-greet-galarr'] );

	// add the filter for attachment links:
	if ( $connectus ) {
		$dom = new DOMDocument();
		libxml_use_internal_errors( true );
		$e = $dom->loadHTML( $val );
		if ( $e === true ) {
			$anchors = $dom->getElementsByTagName( 'a' );
			foreach ( $anchors as $anchor ) {
				$anc    = $anchor->getAttribute( 'href' );
				$ancnew = wpgreet_post_gallery_link( $anc, $pid );
				$anchor->setAttribute( 'href', $ancnew );
			}

			// remove carousel because we want to go to the greet form
			$divs = $dom->getElementsByTagName( 'div' );
			foreach ( $divs as $div ) {
				$div->removeAttribute( 'data-carousel-extra' );
			}

			$val = $dom->saveHTML();
		}
	}
	return $val;
}

//
// function to change the gallery html from gutenberg galleries
//
function wpgreet_gutenberg_gallery( $block_content, $block ) {
	if ( 'core/gallery' !== $block['blockName'] ) {
		return $block_content;
	}

	$newhtml = wpgreet_post_gallery( $block_content, '' );
	return $newhtml;
}

//
// generate wp-greet form link
//
function wpgreet_post_gallery_link( $url, $id ) {
	// get wp-greet optionen from database
	$wpg_options = wpgreet_get_options();

	// build wp-greet form-page link and add parms
	$url_prefix = get_permalink( $wpg_options['wp-greet-formpage'] );

	// change base url if we are using WPML
	if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		$new_url_prefix = $url_prefix;
		$lang_post_id   = icl_object_id( $wpg_options['wp-greet-formpage'], 'page', false, ICL_LANGUAGE_CODE );
		if ( is_null( $lang_post_id ) ) {
			$lang_post_id = icl_object_id( $wpg_options['wp-greet-formpage'], 'post', false, ICL_LANGUAGE_CODE );
		}

		if ( ! is_null( $lang_post_id ) ) {
			$new_url_prefix = get_permalink( $lang_post_id );
		}
		$url_prefix = $new_url_prefix;
	}

	if ( strpos( $url_prefix, '?' ) === false ) {
		$url_prefix .= '?';
	} else {
		$url_prefix .= '&';
	}

	// remove photon wp cdn from jetpack if applicable
	$url_cdn = substr( $url, 0, 15 );
	if ( $url_cdn == 'http://i0.wp.com' ) {
		$url = str_replace( 'i0.wp.com/', '', $url );
	}
	if ( $url_cdn == 'http://i1.wp.com' ) {
		$url = str_replace( 'i1.wp.com/', '', $url );
	}
	if ( $url_cdn == 'http://i2.wp.com' ) {
		$url = str_replace( 'i2.wp.com/', '', $url );
	}

	$link = $url_prefix . 'gallery=' . $id . '&image=' . $url;

	// support for WPML
	if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		$link .= '&lang=' . ICL_LANGUAGE_CODE;
	}

	return $link;
}

//
// Link Feld in der Mediathek hinzufügen
// Wird in diesem Feld eine URL eingegeben, so verweist das Bild in der Grußkarte darauf.
//
// Felder definieren
function wpg_attachment_fields( $form_fields, $post ) {
	$form_fields['wpgreet-external-link'] = array(
		'label' => __( 'wp-greet external link', 'wp-greet' ),
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'wpgreet_external_link', true ),
		'helps' => __( 'If provided, wp-greet will link the image to this URL', 'wp-greet' ),
	);

	$wpg_elt                                     = get_post_meta( $post->ID, 'wpgreet_external_link_target', true );
	$form_fields['wpgreet-external-link-target'] = array(
		'label' => __( 'wp-greet open external link in new tab/window', 'wp-greet' ),
		'input' => 'html',
		'html'  => "<input type='checkbox' name='attachments[" . $post->ID . "][wpgreet-external-link-target]' id='attachments-" . $post->ID . "-wpgreet-external-link-target' value='1' " . ( $wpg_elt == '1' ? "checked='checked'" : '' ) . '/>',
		'helps' => __( 'If checked external links will open in new tab/window', 'wp-greet' ),
	);

	return $form_fields;
}
add_filter( 'attachment_fields_to_edit', 'wpg_attachment_fields', 10, 2 );

// Felder speichern
function wpg_attachment_fields_save( $post, $attachment ) {
	if ( isset( $attachment['wpgreet-external-link'] ) ) {
		update_post_meta( $post['ID'], 'wpgreet_external_link', $attachment['wpgreet-external-link'] );
	}

	update_post_meta( $post['ID'], 'wpgreet_external_link_target', ( $attachment['wpgreet-external-link-target'] == 1 ? 1 : 0 ) );

	return $post;
}
add_filter( 'attachment_fields_to_save', 'wpg_attachment_fields_save', 10, 2 );

function get_fetchuntil_date() {
	$wpg_options = wpgreet_get_options();

	if ( $wpg_options['wp-greet-ocduration'] == '0' ) {
		$fetchuntil = '2035-12-31 23:59:59';
	} else {
		$fetchuntil = gmdate( 'Y-m-d H:i:s', time() + ( get_option( 'gmt_offset' ) * 60 * 60 ) + ( $wpg_options['wp-greet-ocduration'] * 60 * 60 * 24 ) );
	}
	return $fetchuntil;
}
