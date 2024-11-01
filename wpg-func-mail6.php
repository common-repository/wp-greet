<?php
/* This file is part of the wp-greet plugin for WordPress */

/*
  Copyright 2009-2020  Hans Matzen  (email : webmaster at tuxlog dot de)

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
	die( 'You are not allowed to call this page directly.' );
}

// include common functions
require_once 'wpg-func.php';

/**
 * WordPress 5.5 deprecated version 5.2 of PHPMailer
 * and is now using version 6.0 of PHPMailer which uses namespaces.
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

if ( ! class_exists( 'PHPMailer\\PHPMailer\\PHPMailer' ) ) {
	require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
	require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
	require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
}


// derive our own PHPMailer class {
class WPGMailer extends PHPMailer {

	private $wpg_options = array();

	public function __construct( $wpg_options, $exceptions = false ) {
		$this->exceptions  = (bool) $exceptions;
		$this->wpg_options = $wpg_options;

		// set basic parameters from wpg_options
		if ( $wpg_options['wp-greet-usesmtp'] ) {
			$this->IsSMTP();                // set mailer to use SMTP
			$this->Host = $wpg_options['wp-greet-smtp-host'];
			$this->Port = $wpg_options['wp-greet-smtp-port'];
			if ( $wpg_options['wp-greet-smtp-port'] == '' ) {
				if ( $wpg_options['wp-greet-smtp-ssl'] == '1' ) {
					$this->Port = 465;
				} else {
					$this->Port = 25;
				}
			}

			if ( isset( $wpg_options['wp-greet-smtp-ssl'] ) and
				$wpg_options['wp-greet-smtp-ssl'] == '1' ) {
				$this->SMTPSecure = 'ssl';
			}

			if ( isset( $wpg_options['wp-greet-smtp-user'] ) and
				$wpg_options['wp-greet-smtp-user'] != '' and
				$wpg_options['wp-greet-smtp-pass'] != '' ) {
				$this->SMTPAuth = true;                                 // turn on SMTP authentication
				$this->Username = $wpg_options['wp-greet-smtp-user'];   // SMTP username
				$this->Password = $wpg_options['wp-greet-smtp-pass'];   // SMTP password
			}
		} else {
			$this->IsMail();                                            // set mailer to mail
		}

		$this->CharSet  = 'utf-8';                                       // set mail encoding
		$this->WordWrap = 50;                                           // set word wrap to 50 characters
		$this->IsHTML( true );                                            // set email format to HTML

		if ( $wpg_options['wp-greet-bcc'] != '' ) {
			$this->AddBCC( $wpg_options['wp-greet-bcc'] );                // add bcc if option is set
		}

		// pull DKIM conf in if available
		if ( file_exists( 'dkim_conf.php' ) ) {
			$dkim                  = include 'dkim_conf.php';
			$this->DKIM_domain     = $dkim['domain'];
			$this->DKIM_selector   = $dkim['selector'];
			$this->DKIM_private    = $dkim['private'];
			$this->DKIM_passphrase = $dkim['passphrase'];
		}
	}

	//
	// add return path if set in admin dialog else take it from
	// parameter $address
	//
	function AddReturnPath( $address, $name = '' ) {
		if ( trim( $this->wpg_options['wp-greet-mailreturnpath'] ) != '' ) {
			$this->ReturnPath = $wpg_options['wp-greet-mailreturnpath'];
		} else {
			$this->ReturnPath = addslashes( $address );
		}
	}

	//
	// add reply to if set in admin dialog else take it from
	// parameter $address
	//
	function AddReplyTo( $address, $name = '' ) {
		if ( trim( $this->wpg_options['wp-greet-mail-replyto'] ) != '' ) {
			parent::AddReplyTo( $this->wpg_options['wp-greet-mail-replyto'], $this->wpg_options['wp-greet-mail-replyto'] );
		} else {
			parent::AddReplyTo( addslashes( $address ), addslashes( $name ) );
		}
	}

	//
	// add from address if set in admin dialog else take it from
	// parameter $from
	//
	function AddFrom( $from ) {
		if ( trim( $this->wpg_options['wp-greet-mail-from'] ) != '' ) {
			$this->From = addslashes( trim( $this->wpg_options['wp-greet-mail-from'] ) );
		} else {
			$this->From = addslashes( trim( $from ) );
		}
	}

	//
	// add from address if set in admin dialog else take it from
	// parameter $sender
	//
	function AddSender( $sender ) {
		if ( trim( $this->wpg_options['wp-greet-staticsender'] ) != '' ) {
			$this->Sender = addslashes( trim( $this->wpg_options['wp-greet-staticsender'] ) );
		} else {
			$this->Sender = addslashes( trim( $sender ) );
		}
	}

	//
	// add from name if set in admin dialog else take it from
	// parameter $fromname
	//
	function AddFromName( $fromname ) {
		if ( trim( $this->wpg_options['wp-greet-mail-fromname'] ) != '' ) {
			$this->FromName = html_entity_decode( trim( $this->wpg_options['wp-greet-mail-fromname'] ), ENT_COMPAT | ENT_QUOTES | ENT_HTML401 );
		} else {
			$this->FromName = html_entity_decode( trim( $fromname ), ENT_COMPAT | ENT_QUOTES | ENT_HTML401 );
		}
	}
}

//
// this function sends the greeting card mail
//
// $sender     - sender email
// $sendername - sender name
// $recv       - receiver email
// $recvname   - receiver name
// $title      - mailsubject
// $msgtext    - the mail mesage
// $ccsender   - if 1 then the sender will receive a copy of the mail
// $debug      - if true SMTP Mailerclass debugger will be turned on
// $picurl     - url to the greet card picture
//
// returns mail->ErrInfo when error occurs or true when everything went wright
//
function sendGreetcardMail( $sender, $sendername, $recv, $recvname, $title,
		$msgtext, $picurl, $ccsender, $sendbcc, $debug = false ) {
	// hole optionen
	$wpg_options = wpgreet_get_options();

	// get translation
	load_plugin_textdomain( 'wp-greet', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	// get mailer object
	$mail              = new WPGMailer( $wpg_options );
	$mail->SMTPDebug   = $debug;          // for debugging
	$mail->Debugoutput = 'error_log';

	//
	// inline images gehen nur mit smtp versand
	// pruefen ob inline images gewuenscht sind
	//
	$inline = false;
	if ( $mail->Mailer == 'smtp' && $wpg_options['wp-greet-imgattach'] ) {
		$inline = true;
	}

	// erzeuge eindeutige cid
	$wpgcid = uniqid( 'wpgimg_', false );

	// html message bauen
	$message  = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
	$message .= '<title>' . $title . "</title>\n</head><body>";
	$message .= $wpg_options['wp-greet-default-header'] . "\r\n";
	// add ngg image description to message
	$ngg_desc = '';
	if ( isset( $wpg_options['wp-greet-show-ngg-desc'] ) and $wpg_options['wp-greet-show-ngg-desc'] ) {
		global $nggdb;

		if ( isset( $nggdb ) ) {
			$nggimg = $nggdb->search_for_images( substr( $picurl, strrpos( $picurl, '/' ) + 1 ) );
			if ( isset( $nggimg[0]->description ) ) {
				$ngg_desc = trim( $nggimg[0]->description );
			}
		}
	}

	if ( $inline ) {
		$message .= "<p><img src=\"cid:$wpgcid\" alt=\"wp-greet card image\" width=\"" . $wpg_options['wp-greet-imagewidth'] . '"/></p>';
	} else {
		$message .= "<p><img src='" . $picurl . "' width='" . $wpg_options['wp-greet-imagewidth'] . "' /></p>";
	}
	$message .= "<div class='wpg_image_description'>" . $ngg_desc . '</div>';
	$message .= '<br />';

	// nachrichtentext escapen
	$msgtext = nl2br( $msgtext );

	$message .= "\r\n" . $msgtext . "\r\n";
	$message .= '<p>' . $wpg_options['wp-greet-default-footer'] . "</p>\r\n";
	$message .= '</body></html>';

	// replace textvars
	$message = str_replace( '%sender%', $sendername, $message );
	$message = str_replace( '%sendermail%', $sender, $message );
	$message = str_replace( '%receiver%', $recvname, $message );
	$message = str_replace( '%link%', ( isset( $fetchlink ) ? $fetchlink : '' ), $message );
	$message = str_replace( '%duration%', ( isset( $duration ) ? $duration : '' ), $message );

	// jetzt nehmen wir den eigentlichen mail versand vor
	$mail->AddSender( $sender );
	$mail->AddFrom( $sender );
	$mail->AddFromName( $sendername );
	$mail->AddReplyTo( $sender );

	// add cc if option is set
	if ( $ccsender & 1 ) {
		$mail->AddCC( $sender );
	}

	// inline image anfügen
	if ( $inline ) {
		// mit briefmarke
		if ( trim( $wpg_options['wp-greet-stampimage'] ) != '' ) {
			// briefmarke einbauen
			// aus der url des bildes den dateinamen bauen
			$surl     = get_option( 'siteurl' );
			$picpath  = ABSPATH . substr( $picurl, strpos( $picurl, $surl ) + strlen( $surl ) + 1 );
			$stampurl = site_url( 'wp-content/plugins/wp-greet/' ) .
			"wpg-stamped.php?cci=$picpath&sti=" . ABSPATH . $wpg_options['wp-greet-stampimage'] .
			'&stw=' . $wpg_options['wp-greet-stamppercent'] . '&ob=1';

			$resp       = wp_remote_request( $stampurl, array( 'timeout' => 10 ) );
			$stampedimg = $resp['body'];
			$picfile    = substr( $picurl, strrpos( $picurl, '/' ) + 1 );
			// und ans mail haengen

			// neue Variante einen Anhang aus einem binary String zu erzeugen ab wp 3.7
			$mail->AddStringEmbeddedImage( $stampedimg, $wpgcid, $name = $picfile, $encoding = 'base64', $type = 'image/png' );

			// ohne briefmarke
		} else {
			// aus der url des bildes den dateinamen bauen
			$surl    = get_option( 'siteurl' );
			$picpath = ABSPATH . substr( $picurl, strpos( $picurl, $surl ) + strlen( $surl ) + 1 );
			$picfile = substr( $picurl, strrpos( $picurl, '/' ) + 1 );
			$mtype   = get_mimetype( $picfile );

			// und ans mail haengen
			$mail->AddEmbeddedImage( $picpath, $wpgcid, $picfile, 'base64', $mtype );
		}
	}

	// smilies ersetzen
	if ( $wpg_options['wp-greet-smilies'] ) {

		// für den textmodus
		$smprefix = get_option( 'siteurl' ) . '/wp-content/plugins/wp-greet/smilies/';
		$picpath  = ABSPATH . 'wp-content/plugins/wp-greet/smilies/';
		preg_match_all( '(:[^\040]+:)', $msgtext, $treffer );

		foreach ( $treffer[0] as $sm ) {
			if ( $inline == true ) {
				$smrep = '<img src="cid:' . substr( $sm, 1, strlen( $sm ) - 2 ) . '" alt="wp-greet smiley" />';
				// aus dem namen des bildes den dateinamen bauen
				$picfile = substr( $sm, 1, strlen( $sm ) - 2 );
				$mtype   = get_mimetype( $picfile );
				  $mail->AddEmbeddedImage( $picpath . '/' . $picfile, $picfile, $picfile, 'base64', $mtype );
			} else {
				$smrep = '<img src="' . $smprefix . substr( $sm, 1, strlen( $sm ) - 2 ) . '" alt="' . substr( $sm, 1, strlen( $sm ) - 2 ) . '" />';
			}
			$message = str_replace( $sm, $smrep, $message );
		}

		  // für den tinymce editor modus
		  preg_match_all( '/(<img[^>]*src="(.*?)"[^>]*>)/i', $msgtext, $treffer );
		foreach ( $treffer[2] as $sm ) {
			// nur bei inline ersetzen im textmodus gibt es keine img tags an dieser stelle
			if ( $inline == true ) {
				$wpgsmcid = uniqid( 'wpgimg_', false );
				$smrep    = 'cid:' . $wpgsmcid;
				// aus der url des bildes den dateinamen bauen
				$picfile = substr( $sm, strrpos( $sm, '/' ) + 1, strlen( $sm ) - 2 );
				$mtype   = get_mimetype( $picfile );
				$mail->AddEmbeddedImage( $picpath . $picfile, $wpgsmcid, $picfile, 'base64', $mtype );
				  $message = str_replace( $sm, $smrep, $message );
			}
		}
	}

	$mail->Subject = $title;  // subject hinzufuegen
	$mail->Body    = $message;   // nachricht hinzufuegen

	// send mail to each of the recipients
	$result = true;
	$ems    = explode( ',', $recv );
	$emn    = explode( ',', $recvname );
	$j      = 0;

	// if Bcc then send mail to sender
	if ( $sendbcc ) {
		$mail->AddAddress( $sender );
	}

	foreach ( $ems as $i ) {
		// only give the sender a CC the first time
		if ( $j > 0 ) {
			$mail->ClearCCs();
		}
		$mail->ClearAddresses();
		if ( ! $sendbcc ) {
			$mail->AddAddress( trim( $i ), html_entity_decode( trim( $emn[ $j ] ), ENT_COMPAT | ENT_QUOTES | ENT_HTML401 ) );
		} else {
			$mail->AddBCC( trim( $i ), html_entity_decode( trim( $emn[ $j ] ), ENT_COMPAT | ENT_QUOTES | ENT_HTML401 ) );
		}
		$j++;
		if ( ! $mail->Send() ) {
			error_log( 'PHPMailer-Fehler: ' . $mail->ErrorInfo );
			$result .= $mail->ErrorInfo;
		}
	}
	return $result;
}


//
// this function sends the confirmation mail
//
// $sender     - sender email
// $sendername - sender name
// $recv       - receiver email
// $recvname   - receiver name
// $debug      - if true SMTP Mailerclass debugger will be turned on
// $confirmcode - uniquie code for validation
// $confirmuntil - time until the confirmation has to be done
//
// returns mail->ErrInfo when error occurs or true when everything went wright
//
function sendConfirmationMail( $sender, $sendername, $recvname, $confirmcode, $confirmuntil, $debug = false ) {
	global $wpdb;

	// hole optionen
	$wpg_options = wpgreet_get_options();

	// get translation
	load_plugin_textdomain( 'wp-greet', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	// mail betreff aufbauen
	$subj = get_option( 'blogname' ) . ' - ' . __( 'Greeting Card Confirmation Mail', 'wp-greet' );

	$url_prefix = get_permalink( $wpg_options['wp-greet-formpage'], false );

	if ( strpos( $url_prefix, '?' ) === false ) {
		$url_prefix .= '?';
	} else {
		$url_prefix .= '&';
	}
	$confirmlink = stripslashes( $url_prefix . 'verify=' . $confirmcode );
	$confirmlink = '<a href="' . $confirmlink . '">' . $confirmlink . '</a>';

	// html message bauen
	$message  = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
	$message .= '<title>' . $subj . "</title>\n</head><body>";
	$message .= '<br />';

	// hole nachrichten text
	$msgtext = $wpg_options['wp-greet-mctext'];

	// nachrichtentext escapen
	$msgtext = nl2br( $msgtext );
	$msgtext = str_replace( '%sender%', $sendername, $msgtext );
	$msgtext = str_replace( '%sendermail%', $sender, $msgtext );
	$msgtext = str_replace( '%receiver%', $recvname, $msgtext );
	$msgtext = str_replace( '%link%', $confirmlink, $msgtext );
	$msgtext = str_replace( '%duration%', $wpg_options['wp-greet-mcduration'], $msgtext );

	$message .= "\r\n" . $msgtext . "\r\n";
	$message .= '</body></html>';

	// jetzt nehmen wir den eigentlichen mail versand vor
	$mail              = new WPGMailer( $wpg_options );
	$mail->SMTPDebug   = $debug;          // for debugging
	$mail->Debugoutput = 'error_log';

	$mail->AddSender( $sender );
	$mail->AddFrom( get_option( 'admin_email' ) );
	$mail->AddFromName( get_option( 'blogname' ) );
	$mail->AddReplyTo( $sender );

	$mail->AddAddress( $sender, $sendername );

	$mail->Subject = $subj;                 // subject hinzufuegen
	$mail->Body    = $message;                 // nachricht hinzufuegen

	if ( $mail->Send() ) {
		return true;
	} else {
		error_log( 'PHPMailer-Fehler: ' . $mail->ErrorInfo );
		return $mail->ErrorInfo;
	}
}



//
// this function sends the link mail
//
// $sender     - sender email
// $sendername - sender name
// $recv       - receiver email
// $recvname   - receiver name
// $debug      - if true SMTP Mailerclass debugger will be turned on
// $duration   - number of days the card can be fetched
// $fetchcode  - code to fetch the greet card
//
// returns mail->ErrInfo when error occurs or true when everything went wright
//
function sendGreetcardLink( $sender, $sendername, $recv, $recvname, $duration, $fetchcode, $ccsender, $sendbcc, $debug = false ) {
	global $wpdb;

	// hole optionen
	$wpg_options = wpgreet_get_options();

	// get translation
	load_plugin_textdomain( 'wp-greet', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	// mail betreff aufbauen
	$subj = get_option( 'blogname' ) . ' - ' . __( 'A Greeting Card for you', 'wp-greet' );

	// abruflink aufbauen
	$url_prefix = get_permalink( $wpg_options['wp-greet-formpage'], false );

	if ( strpos( $url_prefix, '?' ) === false ) {
		$url_prefix .= '?';
	} else {
		$url_prefix .= '&';
	}

	$confirmlink = stripslashes( $url_prefix . 'verify=' . ( isset( $confirmcode ) ? $confirmcode : '' ) );
	$fetchlink   = stripslashes( $url_prefix . 'display=' . $fetchcode );
	$fetchlink   = '<a href="' . $fetchlink . '">' . $fetchlink . '</a>';

	// html message bauen
	$message  = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
	$message .= '<title>' . $subj . "</title>\n</head><body>";
	$message .= '<br />';

	// hole nachrichten text
	$msgtext = $wpg_options['wp-greet-octext'];
	// nachrichtentext escapen
	$msgtext = nl2br( $msgtext );
	$msgtext = str_replace( '%sender%', $sendername, $msgtext );
	$msgtext = str_replace( '%sendermail%', $sender, $msgtext );
	$msgtext = str_replace( '%receiver%', $recvname, $msgtext );
	$msgtext = str_replace( '%link%', $fetchlink, $msgtext );
	$msgtext = str_replace( '%duration%', $duration, $msgtext );

	$message .= "\r\n" . $msgtext . "\r\n";
	$message .= '<p>' . $wpg_options['wp-greet-default-footer'] . "</p>\r\n";
	$message .= '</body></html>';

	// jetzt nehmen wir den eigentlichen mail versand vor
	$mail              = new WPGMailer( $wpg_options );
	$mail->SMTPDebug   = $debug;          // for debugging
	$mail->Debugoutput = 'error_log';

	$mail->AddSender( $sender );
	$mail->AddFrom( $sender );
	$mail->AddFromName( $sendername );
	$mail->AddReplyTo( $sender );

	// add cc if option is set
	if ( $ccsender & 1 ) {
		$mail->AddCC( $sender );
	}

	$mail->Subject = $subj;                  // subject hinzufuegen
	$mail->Body    = $message;                  // nachricht hinzufuegen

	// send mail to each of the recipients
	$result = true;
	$ems    = explode( ',', $recv );
	$emn    = explode( ',', $recvname );
	$j      = 0;

	// if Bcc then send mail to sender
	if ( $sendbcc ) {
		$mail->AddAddress( $sender );
	}

	foreach ( $ems as $i ) {
		// only give the sender a CC the first time
		if ( $j > 0 ) {
			$mail->ClearCCs();
		}
		$mail->ClearAddresses();

		if ( ! $sendbcc ) {
			$mail->AddAddress( trim( $i ), html_entity_decode( ( isset( $emn[ $j ] ) ? trim( $emn[ $j ] ) : trim( $emn[0] ) ), ENT_COMPAT | ENT_QUOTES | ENT_HTML401 ) );
		} else {
			$mail->AddBCC( trim( $i ), html_entity_decode( ( isset( $emn[ $j ] ) ? trim( $emn[ $j ] ) : trim( $emn[0] ) ), ENT_COMPAT | ENT_QUOTES | ENT_HTML401 ) );
		}

		$j++;
		if ( ! $mail->Send() ) {
			$result .= $mail->ErrorInfo;
			error_log( 'PHPMailer-Fehler: ' . $mail->ErrorInfo );
		}
	}
	return $result;
}

//
// this function sends the fetch confirmation mail
//
// $sender     - sender email
// $sendername - sender name
// $recv       - receiver email
// $recvname   - receiver name
// $debug      - if true SMTP Mailerclass debugger will be turned on
// $duration   - number of days the card can be fetched
// $fetchcode  - code to fetch the greet card
//
// returns mail->ErrInfo when error occurs or true when everything went wright
//
function sendGreetcardConfirmation( $sender, $sendername, $recv, $recvname, $duration, $fetchcode, $debug = false ) {
	global $wpdb;

	// hole optionen
	$wpg_options = wpgreet_get_options();

	// get translation
	load_plugin_textdomain( 'wp-greet', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	// mail betreff aufbauen
	$subj = get_option( 'blogname' ) . ' - ' . __( 'Your greeting card was received', 'wp-greet' );

	// abruflink aufbauen
	$url_prefix = get_permalink( $wpg_options['wp-greet-formpage'], false );

	if ( strpos( $url_prefix, '?' ) === false ) {
		$url_prefix .= '?';
	} else {
		$url_prefix .= '&';
	}
	$fetchlink = stripslashes( $url_prefix . 'display=' . $fetchcode );
	$fetchlink = '<a href="' . $fetchlink . '">' . $fetchlink . '</a>';

	// html message bauen
	$message  = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />';
	$message .= '<title>' . $subj . "</title>\n</head><body>";
	$message .= '<br />';

	// hole nachrichten text
	$msgtext = $wpg_options['wp-greet-ectext'];
	// nachrichtentext escapen
	$msgtext = nl2br( $msgtext );
	$msgtext = str_replace( '%sender%', $sendername, $msgtext );
	$msgtext = str_replace( '%sendermail%', $sender, $msgtext );
	$msgtext = str_replace( '%receiver%', $recvname, $msgtext );
	$msgtext = str_replace( '%link%', $fetchlink, $msgtext );
	$msgtext = str_replace( '%duration%', $duration, $msgtext );

	$message .= "\r\n" . $msgtext . "\r\n";
	$message .= '</body></html>';

	// jetzt nehmen wir den eigentlichen mail versand vor
	$mail              = new WPGMailer( $wpg_options );
	$mail->SMTPDebug   = $debug;          // for debugging
	$mail->Debugoutput = 'error_log';

	$mail->AddSender( $sender );
	$mail->AddFrom( get_option( 'admin_email' ) );
	$mail->AddFromName( get_option( 'blogname' ) );
	$mail->AddReplyTo( $sender );

	// add recipients
	// $ems = explode(",",$recv);
	// foreach($ems as $i)
	// $mail->AddAddress( trim($i), html_entity_decode($recvname, ENT_COMPAT | ENT_QUOTES | ENT_HTML401 ));

	$mail->AddAddress( trim( $sender ), html_entity_decode( $sendername, ENT_COMPAT | ENT_QUOTES | ENT_HTML401 ) );

	$mail->Subject = $subj;                 // subject hinzufuegen
	$mail->Body    = $message;                  // nachricht hinzufuegen

	if ( $mail->Send() ) {
		return true;
	} else {
		error_log( 'PHPMailer-Fehler: ' . $mail->ErrorInfo );
		return $mail->ErrorInfo;
	}
}

//
// check if the card was already sent
//
function checkMailSent( $sender, $recv ) {
	global $wpdb;

	$now  = time() + ( get_option( 'gmt_offset' ) * 60 * 60 + date_offset_get( new DateTime() ) );
	$tmin = gmdate( 'Y-m-d H:i:s', $now - 30 );
	$tmax = gmdate( 'Y-m-d H:i:s', $now + 30 );

	$sql = 'select count(*) as anz from ' . $wpdb->prefix . "wpgreet_stats where senttime>'$tmin' and senttime<'$tmax' and frommail='$sender' and tomail='$recv'";

	$count = $wpdb->get_row( $sql );

	if ( intval( $count->anz ) > 0 ) {
		return true;
	} else {
		return false;
	}
}

