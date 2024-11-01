<?php
/*
 This file is part of the wp-greet plugin for WordPress */
/*
  Copyright 2009-2016  Hans Matzen  (email : webmaster at tuxlog dot de)

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
// if called directly, get parameters from GET and output the greetcardform
global $wp_version;
if ( (float) $wp_version >= 5.5 ) {
	require_once 'wpg-func-mail6.php';
} else {
	require_once 'wpg-func-mail.php';
}

if ( preg_match( '#' . basename( __FILE__ ) . '#', $_SERVER['PHP_SELF'] ) ) {
	// direktaufruf des formulars

	$galleryID = 0;
	$picurl    = '';
	if ( isset( $_GET['gallery'] ) and isset( $_GET['image'] ) ) {
		// get post vars
		$galleryID = esc_attr( isset( $_GET['gallery'] ) ? (int) $_GET['gallery'] : '' );
		$picurl    = esc_attr( $_GET['image'] );
		$out       = showGreetcardForm( $galleryID, $picurl, $picdesc );
		echo $out;
	}
}

// apply the shortcode

function sc_searchwpgreet( $atts ) {
	// get translation
	load_plugin_textdomain( 'wp-greet', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	// get GET vars
	$galleryID = ( isset( $_GET['gallery'] ) ? esc_attr( $_GET['gallery'] ) : '' );
	$picurl    = ( isset( $_GET['image'] ) ? esc_attr( $_GET['image'] ) : '' );
	$verify    = ( isset( $_GET['verify'] ) ? esc_attr( $_GET['verify'] ) : '' );
	$display   = ( isset( $_GET['display'] ) ? esc_attr( $_GET['display'] ) : '' );
	// for bwcards
	$pid      = ( isset( $_GET['pid'] ) ? esc_attr( $_GET['pid'] ) : '' );
	$approved = ( isset( $_GET['approved'] ) ? esc_attr( $_GET['approved'] ) : '' );
	// for WPML
	$lang = ( isset( $_GET['lang'] ) ? esc_attr( $_GET['lang'] ) : '' );

	// check if woocommerce and WCCARDS are active adn redirect to checkout
	global $wpg_woocommerce_send;
	$wpg_woocommerce_send = false;
	if ( defined( 'WCCARDS' ) and function_exists( 'is_wp_greet_woocommerce_enabled' )
		and is_wp_greet_woocommerce_enabled() and $galleryID != ''
		and isset( $_POST['action'] ) and $_POST['action'] == __( 'Add to Cart', 'wp-greet' ) ) {

		$_POST['action']      = __( 'Send', 'wp-greet' );
		$wpg_woocommerce_send = true;
	}

	// check if BWCARDS is active
	if ( defined( 'BWCARDS' ) ) {
		$bwc_options = bwc_get_global_options();
		$sid         = session_id();
		// do  we use paypal?

		if ( isset( $bwc_options['bwc_general_paypal'] ) && $bwc_options['bwc_general_paypal'] == 1 ) {
			// zuerst pruefen, ob wir von einer fertigen karte (über die gallery) aufgerufen werden

			// bezahlt wird nachdem die karte geschrieben wurd, aber vor dem versand
			$autoinc = ( isset( $_POST['autoinc'] ) ? esc_attr( $_POST['autoinc'] ) : '' );

			if ( $autoinc > 0 ) {
				global $wpdb;
				$sql = 'select * from ' . $wpdb->prefix . "wpgreet_cards where mid=$autoinc";
				$res = $wpdb->get_row( $sql );
				/* folgende werte müssen gefüllt werden */
				$_POST['action']     = __( 'Send', 'wp-greet' );
				$_POST['sender']     = $res->frommail;
				$_POST['sendername'] = $res->fromname;
				$_POST['recv']       = $res->tomail;
				$_POST['recvname']   = $res->toname;
				$_POST['wpgtitle']   = $res->subject;
				$_POST['message']    = $res->mailbody;
				$_POST['ccsender']   = $res->cc2from;
				$_POST['accepttou']  = 1;
				$picurl              = $res->picture;
				$galleryID           = '';
				$_POST['fsend']      = $res->future_send;
			} else {
				// sonst wurde die karte über den purchase button gekauft und soll jetzt geschrieben werden

								$pprows = bwc_paypal_read_orders( $sid );
				// find valid order
				$ppitem     = '';
				$bwapproved = '';

				foreach ( $pprows as $pprow ) {

					if ( $pprow->sessionid == $sid and ( $pprow->pictureid == $pid or $pid == '' ) ) {
						$ppitem = $pprow;
						break;
					}
				}

				if ( is_object( $ppitem ) ) {
					$bwapproved  = ( $ppitem->status == '1' ? 'approved' : '' );
					$pid         = $ppitem->pictureid;
					$bwpid       = $pid;
					$picture     = nggdb::find_image( $bwpid );
					$bwgalleryID = $picture->galleryid;
					$bwpicurl    = $picture->imageURL;
					$fname       = $picture->imagePath;
					// append file name obfuscator here
					$obfusc = $bwc_options['bwc_general_image_suffix'];

					if ( file_exists( $fname . '_' . $obfusc ) ) {
						$bwpicurl = $bwpicurl . '_' . $obfusc;
					}
				} else {
					// get post vars for button sendfree mode
					$bwgalleryID = ( isset( $_POST['gallery'] ) ? esc_attr( $_POST['gallery'] ) : '' );
					$bwpicurl    = ( isset( $_POST['image'] ) ? esc_attr( $_POST['image'] ) : '' );
					$bwpid       = ( isset( $_POST['pid'] ) ? esc_attr( $_POST['pid'] ) : '' );
					$bwapproved  = ( isset( $_POST['approved'] ) ? esc_attr( $_POST['approved'] ) : '' );
				}
			}
			// wenn diese gallery active mit bw-cards ist, dann verwenden wir die ermittelten
			// werte für das formular
			if ( defined( 'BWCARDS' ) ) {
				require_once ABSPATH . 'wp-content/plugins/wp-greet-paypal/bw-nggncg.php';
			}

			if ( bwc_get_gallery_active( $bwgalleryID ) == 1 ) {
				$galleryID = $bwgalleryID;
				$picurl    = $bwpicurl;
				$pid       = $bwpid;
				$approved  = $bwapproved;
			}
		}
	}
	// switch language to lang

	if ( $lang != '' ) {
		global $sitepress;

		if ( method_exists( $sitepress, 'switch_lang' ) ) {
			$sitepress->switch_lang( $lang );
		}
	}
	// Karte wird abgeholt

	if ( $display != '' ) {
		$content = showGreetcard( $display );
	} else {
		// replace tag with html form
		$content = showGreetcardForm( $galleryID, $picurl, $verify, $pid, $approved );
	}
	return $content;
}

// this function controls the whole greetcard workflow and the forms


function showGreetcardForm( $galleryID, $picurl, $verify = '', $pid = '', $approved = '' ) {
	global $userdata;
	// hole optionen
	$wpg_options = wpgreet_get_options();
	// ausgabebuffer init
	$out = '';
	// get translation
	load_plugin_textdomain( 'wp-greet', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	// ---------------------------------------------------------------------

	// bestätigungsaufruf für den grußkartenversand

	// ---------------------------------------------------------------------

	if ( $verify != '' ) {
		global $wpdb;
		$sql  = 'select * from ' . $wpdb->prefix . "wpgreet_cards where confirmcode='" . $verify . "';";
		$res  = $wpdb->get_row( $sql );
		$now  = strtotime( gmdate( 'Y-m-d H:i:s', time() + ( get_option( 'gmt_offset' ) * 60 * 60 ) ) );
		$then = mysql2time( $res->confirmuntil );
		
		if ( is_null( $res ) ) {
			// ungültiger code
			$out .= __( 'Your verification code is invalid.', 'wp-greet' ) . '<br />' . __( 'Please send a new card at', 'wp-greet' ) . " <a href='" . site_url() . "' >" . site_url() . '</a>';
			return $out;
		} elseif ( $res->card_sent != 0 and $res->card_sent != '0000-00-00 00:00:00' ) {
			// karte wurde bereits versendet
			$out .= __( 'Your greeting card has already been sent.', 'wp-greet' ) . '<br />' . __( 'Please send a new card at', 'wp-greet' ) . " <a href='" . site_url() . "' >" . site_url() . '</a>';
			return $out;
		} elseif ( $now > $then and $wpg_options['wp-greet-mcduration'] != 0 ) {
			// die gültigkeiteisdauer ist abgelaufen
			$out .= __( 'Your confirmation link is timedout.', 'wp-greet' ) . '<br />' . __( 'Please send a new card at', 'wp-greet' ) . " <a href='" . site_url() . "' >" . site_url() . '</a>';
			return $out;
		} else {
			// alles okay, karte versenden
			$_POST['action']        = __( 'Send', 'wp-greet' );
			$_POST['sender']        = $res->frommail;
			$_POST['sendername']    = $res->fromname;
			$_POST['recv']          = $res->tomail;
			$_POST['recvname']      = $res->toname;
			$_POST['wpgtitle']      = $res->subject;
			$_POST['message']       = $res->mailbody;
			$_POST['ccsender']      = $res->cc2from;
			$_POST['accepttou']     = 1;
			$picurl                 = $res->picture;
			$_POST['wpg-image-url'] = $picurl;
			$galleryID              = '';
			$_POST['fsend']         = $res->future_send;
		}
	}
	// calculate sendtime
	if ( $wpg_options['wp-greet-future-send'] and isset( $_POST['fsend'] ) and $_POST['fsend'] != '' ) {
		$toffsetform = $_POST['clienttz'] * 60; // clienttimezone from minutes to seconds
		// convert given future send time to GMT timestamp
		$sendtime = strtotime( $_POST['fsend'] ) + $toffsetform;
		// it it is in the past, send it now
		if ( $sendtime < time() ) {
			$sendtime = 0;
		}
	} else {
		$sendtime = 0;
	}

	// -------------------------------------------------------------------------------------

	// pruefe berechtigung zum versenden von grusskarten

	if ( ! current_user_can( 'wp-greet-send' ) and $wpg_options['wp-greet-minseclevel'] != 'everyone' ) {
		return '<p><b>' . __( 'You are not permitted to send greeting cards.', 'wp-greet' ) . '<br />' . __( 'Please contact you WordPress Administrator.', 'wp-greet' ) . '</b></p>';
	}
	// uebernehme user daten bei erstaufruf

	if ( ! isset( $_POST['action'] ) and is_user_logged_in() ) {
		$current_user    = wp_get_current_user();
		$_POST['sender'] = $current_user->user_email;
	}
	// uebernehme default subject bei erstufruf

	if ( ! isset( $_POST['wpgtitle'] ) ) {
		$_POST['wpgtitle'] = $wpg_options['wp-greet-default-title'];
	}
	// Feldinhalte pruefen

	if ( isset( $_POST['action'] ) and ( $_POST['action'] == __( 'Preview', 'wp-greet' ) or $_POST['action'] == __( 'Send', 'wp-greet' ) ) ) {

		if ( isset( $_POST['sender'] ) && $_POST['sender'] != '' ) {
			$_POST['sender'] = esc_attr( $_POST['sender'] );
		}

		if ( isset( $_POST['sendername'] ) && $_POST['sendername'] != '' ) {
			$_POST['sendername'] = stripslashes( esc_attr( $_POST['sendername'] ) );
		}

		if ( isset( $_POST['ccsender'] ) && $_POST['ccsender'] != '' ) {
			$_POST['ccsender'] = esc_attr( $_POST['ccsender'] );
		} else {
			$_POST['ccsender'] = 0;
		}

		if ( isset( $_POST['wp-greet-enable-confirm'] ) && $_POST['wp-greet-enable-confirm'] != '' ) {
			$_POST['wp-greet-enable-confirm'] = esc_attr( $_POST['wp-greet-enable-confirm'] );
		} else {
			$_POST['wp-greet-enable-confirm'] = 0;
		}

		if ( isset( $_POST['accepttou'] ) && $_POST['accepttou'] != '' ) {
			$_POST['accepttou'] = esc_attr( $_POST['accepttou'] );
		} else {
			$_POST['accepttou'] = 0;
		}

		if ( isset( $_POST['recv'] ) && $_POST['recv'] != '' ) {
			$_POST['recv'] = esc_attr( $_POST['recv'] );
		}

		if ( isset( $_POST['recvname'] ) && $_POST['recvname'] != '' ) {
			$_POST['recvname'] = stripslashes( esc_attr( $_POST['recvname'] ) );
		}

		if ( isset( $_POST['wpgtitle'] ) && $_POST['wpgtitle'] != '' ) {
			$_POST['wpgtitle'] = esc_attr( stripslashes( $_POST['wpgtitle'] ) );
		}

		if ( isset( $_POST['wp-greet-audio'] ) && $_POST['wp-greet-audio'] != '' ) {
			$_POST['wp-greet-audio'] = esc_attr( stripslashes( $_POST['wp-greet-audio'] ) );
		} else {
			$_POST['wp-greet-audio'] = '';
		}

		if ( isset( $_POST['recvbcc'] ) && $_POST['recvbcc'] != '' ) {
			$_POST['recvbcc'] = esc_attr( $_POST['recvbcc'] );
		} else {
			$_POST['recvbcc'] = '';
		}

		if ( isset( $_POST['wp-greet-offer-stamp'] ) && $_POST['wp-greet-offer-stamp'] != '' ) {
			$_POST['wp-greet-offer-stamp'] = esc_attr( $_POST['wp-greet-offer-stamp'] );
		}

		if ( isset( $_POST['wp-greet-offer-stamp-opacity'] ) && $_POST['wp-greet-offer-stamp-opacity'] != '' ) {
			$_POST['wp-greet-offer-stamp-opacity'] = esc_attr( $_POST['wp-greet-offer-stamp-opacity'] );
		}

		// bringe das html in eine sichere form
		if ( isset( $_POST['message'] ) && $_POST['message'] != '' ) {
			$_POST['message'] = esc_attr( wp_kses_post( wpautop( stripslashes( $_POST['message'] ) ) ) );
		}
		// plausibilisieren der feldinhalte

		// pruefe pflichtfelder

		if ( substr( $wpg_options['wp-greet-fields'], 0, 1 ) == '1' and trim( $_POST['sendername'] ) == '' ) {
			$_POST['action'] = 'Formular';
			$out            .= "<div class='wp-greet-error'>" . __( 'Please fill in mandatory field', 'wp-greet' ) . ' ' . __( 'Sendername', 'wp-greet' ) . '<br /></div>';
		}

		if ( substr( $wpg_options['wp-greet-fields'], 1, 1 ) == '1' and trim( $_POST['sender'] ) == '' ) {
			$_POST['action'] = 'Formular';
			$out            .= "<div class='wp-greet-error'>" . __( 'Please fill in mandatory field', 'wp-greet' ) . ' ' . __( 'Sender', 'wp-greet' ) . '<br /></div>';
		} elseif ( ! wpg_check_email( $_POST['sender'] ) ) {
			$_POST['action'] = 'Formular';
			$out            .= "<div class='wp-greet-error'>" . __( 'Invalid sender mail address.', 'wp-greet' ) . '<br /></div>';
		}

		if ( substr( $wpg_options['wp-greet-fields'], 2, 1 ) == '1' and trim( $_POST['recvname'] ) == '' ) {
			$_POST['action'] = 'Formular';
			$out            .= "<div class='wp-greet-error'>" . __( 'Please fill in mandatory field', 'wp-greet' ) . ' ' . __( 'Recipientname', 'wp-greet' ) . '<br /></div>';
		}

		if ( substr( $wpg_options['wp-greet-fields'], 3, 1 ) == '1' and trim( $_POST['recv'] ) == '' ) {
			$_POST['action'] = 'Formular';
			$out            .= "<div class='wp-greet-error'>" . __( 'Please fill in mandatory field', 'wp-greet' ) . ' ' . __( 'Recipient', 'wp-greet' ) . '<br /></div>';
		} elseif ( $wpg_options['wp-greet-multi-recipients'] ) {
			$ems = explode( ',', $_POST['recv'] );

			foreach ( $ems as $i ) {

				if ( ! wpg_check_email( trim( $i ) ) ) {
					$_POST['action'] = 'Formular';
					$out            .= "<div class='wp-greet-error'>" . __( 'Invalid recipient mail address.', 'wp-greet' ) . '<br /></div>';
				}
			}
		} elseif ( ! wpg_check_email( $_POST['recv'] ) ) {
			$_POST['action'] = 'Formular';
			$out            .= "<div class='wp-greet-error'>" . __( 'Invalid recipient mail address.', 'wp-greet' ) . '<br /></div>';
		}

		if ( substr( $wpg_options['wp-greet-fields'], 4, 1 ) == '1' and trim( $_POST['wpgtitle'] ) == '' ) {
			$_POST['action'] = 'Formular';
			$out            .= "<div class='wp-greet-error'>" . __( 'Please fill in mandatory field', 'wp-greet' ) . ' ' . __( 'Subject', 'wp-greet' ) . '<br /></div>';
		}

		if ( substr( $wpg_options['wp-greet-fields'], 5, 1 ) == '1' and trim( $_POST['message'] ) == '' ) {
			$_POST['action'] = 'Formular';
			$out            .= "<div class='wp-greet-error'>" . __( 'Please fill in mandatory field', 'wp-greet' ) . ' ' . __( 'Message', 'wp-greet' ) . '<br /></div>';
		}

		// pruefe captcha
		if ( ( $wpg_options['wp-greet-captcha'] > 0 ) and ( isset( $_POST['public_key'] ) or isset( $_POST['mcspinfo'] ) or isset( $_POST['cptch_result'] ) or isset( $_POST['g-recaptcha-response'] ) ) ) {
			// check CaptCha!

			if ( $wpg_options['wp-greet-captcha'] == 1 ) {
				require_once ABSPATH . 'wp-content/plugins/captcha/captcha.php';

				if ( class_exists( 'Captcha' ) ) {
					$Cap             = new Captcha();
					$Cap->debug      = false;
					$Cap->public_key = $_POST['public_key'];

					if ( ! $Cap->check_captcha( $Cap->public_key_id(), $_POST['captcha'] ) ) {
						$_POST['action'] = 'Formular';
						$out            .= "<div class='wp-greet-error'>" . __( 'Spamprotection - Code is not valid.<br />', 'wp-greet' );
						$out            .= __( 'Please try again.<br />Tip: If you cannot identify the chars, you can generate a new image. Using Reload.', 'wp-greet' ) . '<br /></div>';
					}
				} else {
					// global $cptch_str_key;

					// if ( 0 != strcasecmp( trim( cptch_decode( $_POST['cptch_result'], $cptch_str_key, $_POST['cptch_time'] ) ), $_POST['cptch_number'] ) ) {

					if ( function_exists( 'cptch_check_custom_form' ) && cptch_check_custom_form() !== true ) {
						$_POST['action'] = 'Formular';
						$out            .= "<div class='wp-greet-error'>" . __( 'Spamprotection - Code is not valid.<br />', 'wp-greet' );
						$out            .= __( 'Please try again.<br />Tip: If you cannot identify the chars, you can generate a new image. Using Reload.', 'wp-greet' ) . '<br /></div>';
					}
				}
			}
			// check Math Protect

			if ( $wpg_options['wp-greet-captcha'] == 2 ) {
				require_once ABSPATH . 'wp-content/plugins/math-comment-spam-protection/math-comment-spam-protection.classes.php';
				$Cap = new MathCheck();
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
				$tap = get_plugins();

				if ( version_compare( $tap['math-comment-spam-protection/math-comment-spam-protection.php']['Version'], '3.0', '<' ) ) {
					$mc_nok = $Cap->InputValidation( $_POST['mcspinfo'], $_POST['mcspvalue'] );
				} else {
					$mc_nok = $Cap->MathCheck_InputValidation( $_POST['mcspinfo'], $_POST['mcspvalue'] );
				}

				if ( $mc_nok != '' ) {
					$_POST['action'] = 'Formular';
					$out            .= "<div class='wp-greet-error'>" . __( 'Spamprotection - Code is not valid.<br />', 'wp-greet' );
					$out            .= __( 'Please try again.', 'wp-greet' ) . '<br /></div>';
				}
			}

			// check google-captcha
			if ( $wpg_options['wp-greet-captcha'] == 3 ) {
				$rec_opt      = get_option( 'gglcptch_options' );
				$captcha      = $_POST['g-recaptcha-response'];
				$secretKey    = $rec_opt['private_key'];
				$ip           = $_SERVER['REMOTE_ADDR'];
				$response     = file_get_contents( 'https://www.google.com/recaptcha/api/siteverify?secret=' . $secretKey . '&response=' . $captcha . '&remoteip=' . $ip );
				$responseKeys = json_decode( $response, true );
				if ( intval( $responseKeys['success'] ) !== 1 ) {
					$_POST['action'] = 'Formular';
					$out            .= "<div class='wp-greet-error'>" . __( 'Spamprotection - Code is not valid.<br />', 'wp-greet' );
					$out            .= __( 'Please try again.', 'wp-greet' ) . '<br /></div>';
				}
				// if (!$rec_ok->is_valid) {
				// $_POST['action'] = "Formular";
				// $out.= "<div class='wp-greet-error'>" . __("Spamprotection - Code is not valid.<br />", "wp-greet");
				// $out.= __("Please try again.", "wp-greet") . "<br /></div>";
				// }
			}
		} // end of pruefe captcha

		// nutzungsbedingungen prüfen

		if ( $wpg_options['wp-greet-touswitch'] == 1 && isset( $_POST['accepttou'] ) && $_POST['accepttou'] != 1 ) {
			$_POST['action'] = 'Formular';
			$out            .= "<div class='wp-greet-error'>" . __( 'Please accept the terms of usage before sending a greeting card.<br />', 'wp-greet' ) . '</div>';
		}
	} // end of Feldinhalte pruefen

	// Vorschau

	if ( isset( $_POST['action'] ) and $_POST['action'] == __( 'Preview', 'wp-greet' ) ) {

		// smilies ersetzen

		if ( $wpg_options['wp-greet-smilies'] ) {
			$smprefix = get_option( 'siteurl' ) . '/wp-content/plugins/wp-greet/smilies/';
			preg_match_all( '(:[^\040]+:)', ( isset( $show_message ) ? $show_message : '' ), $treffer );

			foreach ( array_unique( $treffer[0] ) as $sm ) {
				$smrep        = '<img src="' . $smprefix . substr( $sm, 1, strlen( $sm ) - 2 ) . '" alt=' . $sm . ' />';
				$show_message = str_replace( $sm, $smrep, $show_message );
			}
		}
		// Vorschau anzeigen
		$ccsender = '';

		if ( isset( $_POST['ccsender'] ) && $_POST['ccsender'] == '1' ) {
			$ccsender = ' (' . __( 'CC', 'wp-greet' ) . ')';
		}
		// steuerungs informationen

		if ( ! isset( $_POST['wp-greet-enable-confirm'] ) ) {
			$_POST['wp-greet-enable-confirm'] = '';
		}

		if ( ! isset( $_POST['accepttou'] ) ) {
			$_POST['accepttou'] = '';
		}

		if ( ! isset( $_POST['fsend'] ) ) {
			$_POST['fsend'] = '';
		}

		if ( ! isset( $_POST['ccsender'] ) ) {
			$_POST['ccsender'] = '';
		}
		// load template
		ob_start();
		include plugin_dir_path( __FILE__ ) . '/templates/wp-greet-preview-template.php';
		$template = ob_get_clean();
		// replace placeholders
		$template = str_replace( '{%sendername%}', stripslashes( $_POST['sendername'] ), $template );
		$template = str_replace( '{%sendermail%}', $_POST['sender'], $template );
		$template = str_replace( '{%ccsender%}', $ccsender, $template );
		$template = str_replace( '{%recvname%}', stripslashes( $_POST['recvname'] ), $template );
		$template = str_replace( '{%recvmail%}', $_POST['recv'], $template );
		$template = str_replace( '{%subject%}', esc_attr( stripslashes( $_POST['wpgtitle'] ) ), $template );

		$mhead    = $wpg_options['wp-greet-default-header'];
		$mhead    = str_replace( '%sender%', stripslashes( $_POST['sendername'] ), $mhead );
		$mhead    = str_replace( '%sendermail%', $_POST['sender'], $mhead );
		$template = str_replace( '{%wp-greet-default-header%}', $mhead, $template );

		$mfoot    = $wpg_options['wp-greet-default-footer'];
		$mfoot    = str_replace( '%sender%', stripslashes( $_POST['sendername'] ), $mfoot );
		$mfoot    = str_replace( '%sendermail%', $_POST['sender'], $mfoot );
		$template = str_replace( '{%wp-greet-default-footer%}', $mfoot, $template );

		$template = str_replace( '{%image_url%}', get_imgtag( $galleryID, $picurl ), $template );
		$template = str_replace( '{%message%}', htmlspecialchars_decode( $_POST['message'] ), $template );
		$template = str_replace( '{%send_time%}', $_POST['fsend'], $template );
		$template = str_replace( '{%audiourl%}', esc_attr( $_POST['wp-greet-audio'] ), $template );

		$template .= "<form method='post' action='#'>";
		$template .= "<input name='sender' type='hidden' value='" . $_POST['sender'] . "' />\n";
		$template .= "<input name='sendername' type='hidden' value='" . stripslashes( $_POST['sendername'] ) . "' />\n";
		$template .= "<input name='ccsender' type='hidden' value='" . $_POST['ccsender'] . "' />\n";
		$template .= "<input name='wp-greet-enable-confirm' type='hidden' value='" . $_POST['wp-greet-enable-confirm'] . "' />\n";
		$template .= "<input name='recv' type='hidden' value='" . $_POST['recv'] . "' />\n";
		$template .= "<input name='recvname' type='hidden' value='" . stripslashes( $_POST['recvname'] ) . "' />\n";
		$template .= "<input name='recvbcc' type='hidden' value='" . $_POST['recvbcc'] . "' />\n";
		$template .= "<input name='wpgtitle' type='hidden' value='" . $_POST['wpgtitle'] . "' />\n";
		$template .= "<input name='message' type='hidden' value='" . $_POST['message'] . "' />\n";
		$template .= "<input name='accepttou' type='hidden' value='" . esc_attr( $_POST['accepttou'] ) . "' />\n";
		$template .= "<input name='fsend' type='hidden' value='" . $_POST['fsend'] . "' />\n";
		$template .= "<input id='wp-greet-audio' name='wp-greet-audio' type='hidden' value='" . esc_attr( $_POST['wp-greet-audio'] ) . "' />\n";
		$template .= "<input id='wp-greet-offer-stamp' name='wp-greet-offer-stamp' type='hidden' value='" . ( isset( $_POST['wp-greet-offer-stamp'] ) ? $_POST['wp-greet-offer-stamp'] : '' ) . "' />\n";
		$template .= "<input id='wpg-image-url' name='wpg-image-url' type='hidden' value='" . $_POST['wpg-image-url'] . "' />\n";
		$template .= "<input id='wp-greet-offer-stamp-opacity' name='wp-greet-offer-stamp-opacity' type='hidden' value='" . ( isset( $_POST['wp-greet-offer-stamp-opacity'] ) ? $_POST['wp-greet-offer-stamp-opacity'] : '' ) . "' />\n";

		// make sure parameters are sent when normal get mechanism is off for BWCards

		if ( defined( 'BWCARDS' ) or defined( 'AMACARDS' ) ) {
			$template .= "<input name='vcode' type='hidden' value='" . $_POST['vcode'] . "' />\n";
			$template .= "<input name='image' type='hidden' value='$picurl' />\n";
			$template .= "<input name='gallery' type='hidden' value='$galleryID' />\n";
			$template .= "<input name='pid' type='hidden' value='$pid' />\n";
			$template .= "<input name='approved' type='hidden' value='$approved' />\n";
		}

		// buttons for BWCARDS
		if ( defined( 'BWCARDS' ) ) {
			$template .= "<input name='action' type='submit' value='" . __( 'Edit', 'wp-greet' ) . "' />";
			$template .= '&nbsp;&nbsp;&nbsp;';

			if ( $autoinc > 0 ) {
				$template .= "<input name='action' type='submit'  value='" . __( 'Purchase', 'wp-greet' ) . "' />";
			} else {
				$template .= "<input name='action' type='submit'  value='" . __( 'Send', 'wp-greet' ) . "' />";
			}
			// buttons for AMACARDS
		} elseif ( defined( 'AMACARDS' ) ) {
			$erg       = bwc_check_voucher_code( $_POST['vcode'] );
			$template .= '<div style="float:left;padding:3px;"><input name="action" type="submit" value="' . __( 'Back', 'wp-greet' ) . '" /></div>';
			// voucher not valid client has to pay
			if ( $erg < 0 ) {
				// add send button for wpgamazon FIXME
				$template .= bwc_generate_pay_image_form( $galleryID, $picurl, $pid );
			} else {
				$template .= "<div style='float:left;padding:3px;'><input name='action' type='submit'  value='" . __( 'Send', 'wp-greet' ) . "' /></div>";
			}
			// button for woocommerce
		} elseif ( defined( 'WCCARDS' ) and function_exists( 'is_wp_greet_woocommerce_enabled' ) and is_wp_greet_woocommerce_enabled() ) {
			$template .= "<input name='action' type='submit' value='" . __( 'Back', 'wp-greet' ) . "' />";
			$template .= '&nbsp;&nbsp;&nbsp;';
			$template .= "<input id='sendbutton' name='action' type='submit'  value='" . __( 'Add to Cart', 'wp-greet' ) . "' />";

			// buttons for default process
		} else {
			$template .= "<input name='action' type='submit' value='" . __( 'Back', 'wp-greet' ) . "' />";
			$template .= '&nbsp;&nbsp;&nbsp;';
			$template .= "<input name='action' type='submit'  value='" . __( 'Send', 'wp-greet' ) . "' />";
		}

		// put hidden fields for calcultaing the time differencte between server and client time
		if ( $wpg_options['wp-greet-future-send'] ) {
			$timediff  = "<input name='clienttz' id='clienttz' type='hidden' value='-1' />\n";
			$timediff .= "<script type='text/javascript'>jQuery(document).ready(function() {var d=new Date(); n=d.getTimezoneOffset();document.getElementById(\"clienttz\").value = n;});</script>";
			$template .= $timediff;
		}
		$template .= '</form>';
		$template .= '<p>&nbsp;';
		// output preview
		$out .= $template;
	} elseif ( isset( $_POST['action'] ) and $_POST['action'] == __( 'Send', 'wp-greet' ) and ( $wpg_options['wp-greet-mailconfirm'] != '1' or $verify != '' ) ) {
		// ---------------------------------------------------------------------
		//
		// Grußkarten Mail senden oder Grußkarten Link Mail senden
		//
		// ----------------------------------------------------------------------

		$fetchcode = '';

		if ( checkMailSent( $_POST['sender'], $_POST['recv'] ) == false ) {

			// WOOCOMMERCE transaction
			global $wpg_woocommerce_send;
			if ( $wpg_woocommerce_send == true ) {
				// determine image url without watermark
				$wc_opt          = bwc_get_global_options();
				$wc_picurl       = str_replace( '/uploads/', $wc_opt['bwc_general_image_path'], $_POST['wpg-image-url'] );
				$check_wc_picurl = substr( $wc_picurl, strpos( $wc_picurl, $wc_opt['bwc_general_image_path'] ) + 1 );

				if ( ! file_exists( ABSPATH . '/wp-content/' . $check_wc_picurl ) ) {
					$wc_picurl = $_POST['wpg-image-url'];
				}

				// save card to database
				$autoinc = save_greetcard(
					$_POST['sender'],
					$_POST['sendername'],
					$_POST['recv'],
					$_POST['recvname'],
					$_POST['wpgtitle'],
					$_POST['message'],
					$wc_picurl,
					$_POST['ccsender'] * 1 + $_POST['wp-greet-enable-confirm'] * 2,
					'', // confirm until stays blank
					$verify, // confirmcode if available
					get_fetchuntil_date(),
					uniqid( 'wpgreet_', false ),
					3661, // this is 1970-01-01 01:01:01
					session_id(),
					$_POST['wp-greet-audio'],
					$_POST['recvbcc']
				);

				// add product to cart
				$wc_prod = get_product_from_gallery( $galleryID );
				add_product_to_woo_cart( $wc_prod, $autoinc );

				// redirect to checkout url
				global $woocommerce;
				$url = $woocommerce->cart->get_checkout_url();
				echo "<script type='text/javascript'>window.location.replace('$url');</script>";
				exit;
			}

			if ( $wpg_options['wp-greet-onlinecard'] == 1 ) {
				// grußkarten link mail senden
				// karte ablegen inkl. bestätigungscode
				$fetchcode = uniqid( 'wpgreet_', false );

				$fetchuntil = get_fetchuntil_date();

				// $autoinc = save_greetcard($_POST['sender'], $_POST['sendername'], $_POST['recv'], $_POST['recvname'], $_POST['wpgtitle'], $_POST['message'], $picurl, $_POST['ccsender'] * 1 + $_POST['wp-greet-enable-confirm'] * 2, "", // confirm until stays blank
				$autoinc = save_greetcard(
					$_POST['sender'],
					$_POST['sendername'],
					$_POST['recv'],
					$_POST['recvname'],
					$_POST['wpgtitle'],
					$_POST['message'],
					$_POST['wpg-image-url'],
					$_POST['ccsender'] * 1 + $_POST['wp-greet-enable-confirm'] * 2,
					'', // confirm until stays blank
					$verify, // confirmcode if available
					$fetchuntil,
					$fetchcode,
					$sendtime,
					session_id(),
					$_POST['wp-greet-audio'],
					$_POST['recvbcc']
				);

				// Here comes the mpg stuff for BWcards

				// go to mpg here and if sucessfull call a script which sends the card

				if ( defined( 'BWCARDS' ) ) {
					$bwc_options = bwc_get_global_options();
					$conn        = bwc_get_gallery_active( $galleryID );
					$price       = bwc_get_price( $pid );

					if ( ! isset( $price ) ) {
						$price = 1;
					}
				}
				// check for package voucher code and set approved appropriate

				if ( defined( 'BWCARDS' ) and trim( $_POST['vcode'] ) != '' ) {
					$approved = '';
					$cleft    = bwc_use_voucher_code( trim( $_POST['vcode'] ) );

					if ( $cleft >= 0 ) {
						$approved = 'approved';
						$out     .= "Package code accepted.<br/>You have $cleft cards left.<br/>";
					} elseif ( $cleft == -1 ) {
						$out .= 'Invalid package code. You will be redirected to the payment gateway now.<br/>';
					} else {
						$out .= 'Package code expired. You will be redirected to the payment gateway now.<br/>';
					}
				}

				if ( defined( 'BWCARDS' ) and $conn and $bwc_options['bwc_general_formhook'] and $price > 0 and $approved != 'approved' ) {
					$picture    = nggdb::find_image( $pid );
					$out       .= __( 'You will be redirected to Paypal to complete your order. If you are not redirected to PayPal please click the Purchase button.', 'wp-greet' );
					$bjs        = bwc_generate_pay_script( $picture->gid, $picture->imageURL, $picture, $autoinc );
					$out       .= $bjs;
					$sendstatus = 'no';
				} else {
					// link mail senden or schedulen

					if ( $sendtime != 0 ) {
						// save scheduled card to database
						// $mid = save_greetcard($_POST['sender'], $_POST['sendername'], $_POST['recv'], $_POST['recvname'], $_POST['wpgtitle'], $_POST['message'], $picurl, $_POST['ccsender'], "", "", "", "", $sendtime, $_POST['wp-greet-audio']);
						$mid = save_greetcard( $_POST['sender'], $_POST['sendername'], $_POST['recv'], $_POST['recvname'], $_POST['wpgtitle'], $_POST['message'], $_POST['wpg-image-url'], $_POST['ccsender'], '', '', '', '', $sendtime, $_POST['wp-greet-audio'], $_POST['recvbcc'] );
						wp_schedule_single_event(
							$sendtime,
							'wpgreet_sendcard_link',
							array(
								$mid,
								$_POST['sender'],
								$_POST['sendername'],
								$_POST['recv'],
								$_POST['recvname'],
								$wpg_options['wp-greet-ocduration'],
								$fetchcode,
								$_POST['ccsender'],
								false,
							)
						);
						$sendstatus = true;
					} else {
						$sendstatus = sendGreetcardLink( $_POST['sender'], $_POST['sendername'], $_POST['recv'], $_POST['recvname'], $wpg_options['wp-greet-ocduration'], $fetchcode, $_POST['ccsender'], $_POST['recvbcc'], false );
					}
				}
			} else { // grußkarten mail senden
				if ( $sendtime ) {
					// save scheduled card to database
					// $mid = save_greetcard($_POST['sender'], $_POST['sendername'], $_POST['recv'], $_POST['recvname'], $_POST['wpgtitle'], $_POST['message'], $picurl, $_POST['ccsender'], "", "", "", "", $sendtime, $_POST['wp-greet-audio']);
					$mid = save_greetcard( $_POST['sender'], $_POST['sendername'], $_POST['recv'], $_POST['recvname'], $_POST['wpgtitle'], $_POST['message'], $_POST['wpg-image-url'], $_POST['ccsender'], '', '', '', '', $sendtime, $_POST['wp-greet-audio'] );
					wp_schedule_single_event(
						$sendtime,
						'wpgreet_sendcard_mail',
						array(
							$mid,
							$_POST['sender'],
							$_POST['sendername'],
							$_POST['recv'],
							$_POST['recvname'],
							htmlspecialchars_decode( $_POST['wpgtitle'], ENT_QUOTES ),
							htmlspecialchars_decode( $_POST['message'], ENT_QUOTES ),
							$picurl,
							$_POST['ccsender'],
							false,
						)
					);
					$sendstatus = true;
				} else {
					$sendstatus = sendGreetcardMail( $_POST['sender'], $_POST['sendername'], $_POST['recv'], $_POST['recvname'], htmlspecialchars_decode( $_POST['wpgtitle'], ENT_QUOTES ), htmlspecialchars_decode( $_POST['message'], ENT_QUOTES ), $picurl, $_POST['ccsender'], $_POST['recvbcc'], false );
				}
			} // end grußkarten mail senden
		} else {
			$sendstatus = true;
		}

		if ( strval( $sendstatus ) != 'no' ) { // this is to prevent output if used with bwcards

			if ( $sendstatus == true ) {
				// show sent message
				if ( trim( $wpg_options['wp-greet-sent-message'] ) != '' ) {
					$message = $wpg_options['wp-greet-sent-message'];
					$message = str_replace( '%sendermail%', $_POST['sender'], $message );
					$message = str_replace( '%sender%', $_POST['sendername'], $message );
					$message = str_replace( '%receiver%', $_POST['recvname'], $message );
					$out    .= $message . '<br />';
				} else {

					if ( $wpg_options['wp-greet-future-send'] != 1 ) {
						$out .= __( 'Your greeting card has been sent.', 'wp-greet' ) . '<br />';
					} else {
						$out .= __( 'Your greeting card has been sent or scheduled.', 'wp-greet' ) . '<br />';
					}
				}

				// show resend link

				if ( $wpg_options['wp-greet-offerresend'] ) {
					$out .= "<form method='post' action='#'>";
					$out .= "<input name='sender' type='hidden' value='" . $_POST['sender'] . "' />\n";
					$out .= "<input name='sendername' type='hidden' value='" . $_POST['sendername'] . "' />\n";
					$out .= "<input name='ccsender' type='hidden' value='" . ( $_POST['ccsender'] == '' ? 0 : 1 ) . "' />\n";
					$out .= "<input name='wp-greet-enable-confirm' type='hidden' value='" . ( $_POST['wp-greet-enable-confirm'] == '' ? 0 : 1 ) . "' />\n";
					// $out .= "<input name='recv' type='hidden' value='" . $_POST['recv']  . "' />\n";
					// $out .= "<input name='recvname' type='hidden' value='" . $_POST['recvname']  . "' />\n";
					$out .= "<input name='wpgtitle' type='hidden' value='" . $_POST['wpgtitle'] . "' />\n";
					$out .= "<input name='message' type='hidden' value='" . $_POST['message'] . "' />\n";
					$out .= "<input name='accepttou' type='hidden' value='" . esc_attr( $_POST['accepttou'] ) . "' />\n";
					$out .= "<input name='fsend' type='hidden' value='" . ( isset( $_POST['fsend'] ) ? $_POST['fsend'] : '' ) . "' />\n";
					$out .= "<input name='wp-greet-audio' type='hidden' value='" . esc_attr( $_POST['wp-greet-audio'] ) . "' />\n";
					$out .= "<input name='wp-greet-offer-stamp' type='hidden' value='" . esc_attr( $_POST['wp-greet-offer-stamp'] ) . "' />\n";
					$out .= "<input name='wpg-image-url' type='hidden' value='" . esc_attr( $_POST['wpg-image-url'] ) . "' />\n";
					$out .= "<input name='wp-greet-offer-stamp-opacity' type='hidden' value='" . esc_attr( $_POST['wp-greet-offer-stamp-opacity'] ) . "' />\n";
					// make sure parameters are sent when normal get mechanism is off for BWCards

					if ( defined( 'BWCARDS' ) ) {
						$out .= "<input name='image' type='hidden' value='$picurl' />\n";
						$out .= "<input name='gallery' type='hidden' value='$galleryID' />\n";
						$out .= "<input name='pid' type='hidden' value='$pid' />\n";
						// $out .= "<input name='approved' type='hidden' value='$approved' />\n";

					}
					$out .= "<input name='action' type='submit'  value='" . __( 'Send this card to another recipient', 'wp-greet' ) . "' />";
					$out .= '</form>';
				}
				// create log entry
				log_greetcard( $_POST['recv'], addslashes( $_POST['sender'] ), $picurl, $_POST['message'] );
				// clean log and cards table

				// we are doing this whenever a card has been successfully sent

				// beacause wp-cron does not work properly at the moment
				remove_cards();
				remove_logs();
				// haben wir eine karte mit bestätigungsverfahren gesendet,

				// dann markieren wir sie als versendet

				// if ( $verify != "" )

				// mark_sentcard($verify);

			} else {
				$out  = __( 'An error occured while sending your greeting card.', 'wp-greet' ) . '<br />';
				$out .= __( 'Problem report', 'wp-greet' ) . ' ' . $sendstatus;
			}
		}
	} elseif ( isset( $_POST['action'] ) and $_POST['action'] == __( 'Send', 'wp-greet' ) and ( $wpg_options['wp-greet-mailconfirm'] == '1' or $verify == '' ) ) {
		// ---------------------------------------------------------------------

		// Bestätigungsmail senden und Grußkarte inklusive bestätigungscode ablegen

		// ----------------------------------------------------------------------

		if ( checkMailSent( $_POST['sender'], $_POST['recv'] ) == false ) {
			// karte ablegen inkl. bestätigungscode
			$confirmcode  = uniqid( 'wpgreet_', false );
			$confirmuntil = gmdate( 'Y-m-d H:i:s', time() + ( get_option( 'gmt_offset' ) * 60 * 60 ) + ( $wpg_options['wp-greet-mcduration'] * 60 * 60 ) );
			// save_greetcard($_POST['sender'], $_POST['sendername'], $_POST['recv'], $_POST['recvname'], $_POST['wpgtitle'], $_POST['message'], $picurl, $_POST['ccsender'] * 1 + $_POST['wp-greet-enable-confirm'] * 2, $confirmuntil, $confirmcode, "", // fetchuntil stays blank until confirmation
			save_greetcard(
				$_POST['sender'],
				$_POST['sendername'],
				$_POST['recv'],
				$_POST['recvname'],
				$_POST['wpgtitle'],
				$_POST['message'],
				$_POST['wpg-image-url'],
				$_POST['ccsender'] * 1 + $_POST['wp-greet-enable-confirm'] * 2,
				$confirmuntil,
				$confirmcode,
				'', // fetchuntil stays blank until confirmation
				'',
				$sendtime,
				'',
				$_POST['wp-greet-audio'],
				$_POST['recvbcc']
			); // fetchcode stays blank until confirmation

			// bestätigungsmail senden
			$sendstatus = sendConfirmationMail( $_POST['sender'], $_POST['sendername'], $_POST['recvname'], $confirmcode, $confirmuntil, false, $sendtime );
		} else {
			$sendstatus = true;
		}

		if ( $sendstatus == true ) {
			if ( trim( $wpg_options['wp-greet-confirm-sent-message'] ) != '' ) {
				$out = trim( $wpg_options['wp-greet-confirm-sent-message'] );
			} else {
				$out  = __( 'A confirmation mail has been sent to your address.', 'wp-greet' ) . '<br />';
				$out .= __( 'Please enter the link contained within the email into your browser and the greeting card will be sent.', 'wp-greet' ) . '<br />';
			}
			// create log entry
			log_greetcard( $_POST['sender'], get_option( 'blogname' ), '', 'Confirmation sent: ' . $confirmcode );
		} else {
			$out  = __( 'An error occured while sending the confirmation mail.', 'wp-greet' ) . '<br />';
			$out .= __( 'Problem report', 'wp-greet' ) . ' ' . $sendstatus;
		}
	} else { // ------------------------------ formular anzeigen

		// Vorbelegung setzen, bei Erstaufruf

		// if ( isset($_POST['action']) && $_POST['action'] != __("Back","wp-greet")

		// && $_POST['action'] != __("Send this card to another recipient","wp-greet") ) {

		// $_POST['wp-greet-enable-confirm']=1;

		// $_POST['ccsender']=1;

		// }

		// Formular anzeigen
		$captcha = 0;
		// CaptCha! plugin

		if ( $wpg_options['wp-greet-captcha'] == 1 ) {
			$cfile = plugin_dir_path( __FILE__ ) . '../captcha/captcha.php';

			if ( file_exists( $cfile ) ) {
				require_once $cfile;
				$captcha = 1;

				if ( class_exists( 'Captcha' ) ) {
					$Cap             = new Captcha();
					$Cap->debug      = false;
					$Cap->public_key = intval( $_GET['x'] );
				}
			}
		}
		// Math Comment Spam Protection Plugin

		if ( $wpg_options['wp-greet-captcha'] == 2 ) {
			$cfile = plugin_dir_path( __FILE__ ) . '../math-comment-spam-protection/math-comment-spam-protection.classes.php';

			if ( file_exists( $cfile ) ) {
				require_once $cfile;
				$cap = new MathCheck();
				// Set class options
				$cap_opt                   = get_option( 'plugin_mathcommentspamprotection' );
				$cap->opt['input_numbers'] = $cap_opt['mcsp_opt_numbers'];
				// Generate numbers to be displayed and result
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
				$tap = get_plugins();

				if ( version_compare( $tap['math-comment-spam-protection/math-comment-spam-protection.php']['Version'], '3.0', '<' ) ) {
					$cap->GenerateValues();
					$cap_info             = array();
					$cap_info['operand1'] = $cap->info['operand1'];
					$cap_info['operand2'] = $cap->info['operand2'];
					$cap_info['result']   = $cap->info['result'];
				} else {
					$cap_info = $cap->MathCheck_GenerateValues();
				}
				$captcha = 2;
			}
		}
		// google-captcha plugin

		if ( $wpg_options['wp-greet-captcha'] == 3 ) {
			// nothing else to do here anymore
			$captcha = 3;
		}

		// check if a bwcards cookie is set and take name and email address to pre fill the form

		if ( defined( 'BWCARDS' ) ) {

			if ( isset( $_COOKIE['BWGreetCards'] ) ) {
				$cookie_text         = base64_decode( $_COOKIE['BWGreetCards'] );
				$cookie_vals         = explode( ';', $cookie_text );
				$_POST['sendername'] = $cookie_vals[1];
				$_POST['sender']     = $cookie_vals[0];
			}
		}
		// lets calculate and set the variables to use in the template file
		$image_url        = get_imgtag( $galleryID, $picurl );
		$sendername_label = __( 'Sender Name', 'wp-greet' ) . ( substr( $wpg_options['wp-greet-fields'], 0, 1 ) == '1' ? '<sup>*</sup>' : '' );
		$sendername_input = '<input name="sendername" type="text" maxlength="60" value="' . ( isset( $_POST['sendername'] ) ? $_POST['sendername'] : '' ) . '"/>' . "\n";
		$sendermail_label = __( 'Sender Email', 'wp-greet' ) . ( substr( $wpg_options['wp-greet-fields'], 1, 1 ) == '1' ? '<sup>*</sup>' : '' );
		$sendermail_input = '<input name="sender" type="text" maxlength="60" value="' . ( isset( $_POST['sender'] ) ? $_POST['sender'] : '' ) . '"/>' . "\n";
		$confirm_label    = '';
		$confirm_input    = '';

		if ( $wpg_options['wp-greet-enable-confirm'] ) {
			$confirm_label = __( 'Send confirmation to Sender', 'wp-greet' );
			$confirm_input = "<input name='wp-greet-enable-confirm' type='checkbox' value='1' " . ( ( isset( $_POST['wp-greet-enable-confirm'] ) and $_POST['wp-greet-enable-confirm'] == 1 ) ? 'checked="checked"' : '' ) . " />\n";
		}
		$ccsender_label = __( 'CC to Sender', 'wp-greet' );
		$ccsender_input = "<input name='ccsender' type='checkbox' value='1' " . ( ( isset( $_POST['ccsender'] ) and $_POST['ccsender'] == '1' ) ? 'checked="checked"' : '' ) . " />\n";
		$recvname_label = __( 'Recipient Name', 'wp-greet' ) . ( substr( $wpg_options['wp-greet-fields'], 2, 1 ) == '1' ? '<sup>*</sup>' : '' );
		$recvname_input = "<input id='recvname' name='recvname' type='text' maxlength='60' value='" . ( isset( $_POST['recvname'] ) ? $_POST['recvname'] : '' ) . "'/>\n";
		$recvmail_label = __( 'Recipient Email', 'wp-greet' ) . ( substr( $wpg_options['wp-greet-fields'], 3, 1 ) == '1' ? '<sup>*</sup>' : '' );
		$recvmail_input = "<input id='recv' name='recv' type='text' maxlength='2550' value='" . ( isset( $_POST['recv'] ) ? $_POST['recv'] : '' ) . "'/>";
		$recvbcc_label  = __( 'Send card as blindcopy', 'wp-greet' );
		$recvbcc_input  = "<input name='recvbcc' type='checkbox' value='1' " . ( ( isset( $_POST['recvbcc'] ) and $_POST['recvbcc'] == '1' ) ? 'checked="checked"' : '' ) . " />\n";

		$audio_label = '';
		$audio_input = '';
		if ( $wpg_options['wp-greet-audio'] ) {
			$url          = plugin_dir_url( __FILE__ );
			$audio_label  = __( 'Background Music', 'wp-greet' );
			$audiodir     = plugin_dir_path( __FILE__ ) . 'music';
			$audiofiles   = scandir( $audiodir );
			$audio_input  = '<select id="wp-greet-audio" name="wp-greet-audio" onchange="wpg_switch_background_music();">';
			$audio_input .= '<option value="' . $url . 'music/None.mp3">' . __( 'None', 'wp-greet' ) . '</option>';

			foreach ( $audiofiles as $i ) {
				if ( '.mp3' == substr( $i, strlen( $i ) - 4 ) and $i != 'None.mp3' ) {
					$audio_selected = '';
					if ( isset( $_POST['wp-greet-audio'] ) and $_POST['wp-greet-audio'] == $url . 'music/' . $i ) {
						$audio_selected = 'selected="selected"';
					}
					$audio_input .= '<option value ="' . $url . 'music/' . $i . "\" $audio_selected>" . substr( $i, 0, strlen( $i ) - 4 ) . '</option>';
				}
			}
			$audio_input .= '</select>';
		}

		$futuresend_label = '';
		$futuresend_input = '';

		if ( $wpg_options['wp-greet-future-send'] ) {
			$futuresend_label = __( 'Time to send card', 'wp-greet' );
			$futuresend_input = '<input type="text" name="fsend" id="fsend" value="' . ( isset( $_POST['fsend'] ) ? $_POST['fsend'] : '' ) . '" />';
		}
		$subject_label = __( 'Subject', 'wp-greet' ) . ( substr( $wpg_options['wp-greet-fields'], 4, 1 ) == '1' ? '<sup>*</sup>' : '' );
		$subject_input = "<input name='wpgtitle'  type='text' maxlength='512' value='" . esc_attr( stripslashes( $_POST['wpgtitle'] ) ) . "'/>\n";
		$message_label = __( 'Message', 'wp-greet' ) . ( substr( $wpg_options['wp-greet-fields'], 5, 1 ) == '1' ? '<sup>*</sup>' : '' );
		$message_input = "<textarea class=\"wp-greet-form\" name='message' id='message' rows='40' cols='15'>" . ( isset( $_POST['message'] ) ? htmlspecialchars_decode( $_POST['message'] ) : '' ) . "</textarea>\n";

		if ( $wpg_options['wp-greet-tinymce'] ) {

			if ( ! function_exists( 'add_wpg_safe_smiley' ) ) {

				function add_wpg_safe_smiley( $plugin_array ) {
					$plugin_array['wpg_safesmiley'] = plugins_url( 'tinymce_safesmiley.js', __FILE__ );
					return $plugin_array;
				}
				add_filter( 'mce_external_plugins', 'add_wpg_safe_smiley' );
			}
			// default settings
			global $wp_version;

			if ( ( version_compare( $wp_version, '3.9', '>=' ) ) ) {
				// settings for WP 3.9 and up
				$settings = array(
					'wpautop'          => true, // use wpautop?
					'media_buttons'    => false, // show insert/upload button(s)
					'textarea_name'    => 'message', // set the textarea name to something different, square brackets [] can be used here
					'textarea_rows'    => get_option( 'default_post_edit_rows', 10 ), // rows="..."
					'tabindex'         => '',
					'editor_css'       => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
					'editor_class'     => '', // add extra class(es) to the editor textarea
					'teeny'            => false, // output the minimal editor config used in Press This
					'dfw'              => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
					'quicktags'        => false, // load Quicktags, can be used to pass settings directly to Quicktags using an array()
					'drag_drop_upload' => false, // disable drag and rop upload
					'tinymce'          => array(
						'toolbar1' => 'bold,italic,underline,blockquote,|,undo,redo,|,fontselect,fontsizeselect,forecolor,backcolor',
						'toolbar2' => '',
						'toolbar3' => '',
						'toolbar4' => '',
					),
				);
			} else {
				// settings before WP 3.9
				$settings = array(
					'wpautop'          => true, // use wpautop?
					'media_buttons'    => false, // show insert/upload button(s)
					'textarea_name'    => 'message', // set the textarea name to something different, square brackets [] can be used here
					'textarea_rows'    => get_option( 'default_post_edit_rows', 10 ), // rows="..."
					'tabindex'         => '',
					'editor_css'       => '', // intended for extra styles for both visual and HTML editors buttons, needs to include the <style> tags, can use "scoped".
					'editor_class'     => '', // add extra class(es) to the editor textarea
					'teeny'            => false, // output the minimal editor config used in Press This
					'dfw'              => false, // replace the default fullscreen with DFW (supported on the front-end in WordPress 3.4)
					'quicktags'        => false, // load Quicktags, can be used to pass settings directly to Quicktags using an array()
					'drag_drop_upload' => false, // disable drag and rop upload
					'tinymce'          => array(
						'theme_advanced_buttons1' => 'bold,italic,underline,blockquote,|,undo,redo,|,fullscreen,|,fontselect,fontsizeselect,forecolor,backcolor',
						'theme_advanced_buttons2' => '',
						'theme_advanced_buttons3' => '',
						'theme_advanced_buttons4' => '',
						'theme_advanced_statusbar_location' => 'none',
					),
				);
			}
			ob_start();
			wp_editor( ( isset( $_POST['message'] ) ? htmlspecialchars_decode( stripslashes( $_POST['message'] ) ) : '' ), 'message', $settings );
			$message_input = ob_get_contents();
			ob_end_clean();
		}
		// smilies unter formular anzeigen
		$smilies_label = '';
		$smilies_input = '';

		if ( $wpg_options['wp-greet-smilies'] ) {
			$smileypath    = ABSPATH . 'wp-content/plugins/wp-greet/smilies';
			$smprefix      = get_option( 'siteurl' ) . '/wp-content/plugins/wp-greet/smilies/';
			$smilies_label = __( 'Smileys', 'wp-greet' );
			$smarr         = get_dir_alphasort( $smileypath );
			$smilies_input = '';

			foreach ( $smarr as $file ) {

				if ( $wpg_options['wp-greet-tinymce'] ) {
					$smilies_input .= '<img src="' . $smprefix . $file . '" alt="' . $file . '" onclick=\'smile("' . $smprefix . $file . '")\' />';
				} else {
					$smilies_input .= '<img src="' . $smprefix . $file . '" alt="' . $file . '" onclick=\'smile("' . $file . '")\' />';
				}
			}
		}
		// captcha anzeigen
		$captcha_label = '';
		$captcha_input = '';

		if ( $captcha != 0 ) {
			$captcha_label = __( 'Spamprotection:', 'wp-greet' );
		}
		// CaptCha!

		if ( $captcha == 1 ) {

			if ( isset( $Cap ) ) {
				$captcha_input = $Cap->display_captcha() . '&nbsp;<input name="captcha" type="text" size="10" maxlength="10" />';
			} else {
				ob_start();

				if ( function_exists( 'cptch_display_captcha_custom' ) ) {
					echo "<input type='hidden' name='cntctfrm_contact_action' value='true' />";
					echo cptch_display_captcha_custom();
				}
				// cptch_comment_form_wp3();
				$captcha_input = ob_get_contents();
				ob_end_clean();
			}
		}
		// Math Protect

		if ( $captcha == 2 ) {
			$captcha_input = '<label for="mcspvalue"><small>' . __( 'Sum of', 'wp-greet' ) . '&nbsp;' . $cap_info['operand1'] . ' + ' . $cap_info['operand2'] . ' ? ' . '</small></label><input type="text" name="mcspvalue" id="mcspvalue" value="" size="23" maxlength="10" /><input type="hidden" name="mcspinfo" value="' . $cap_info['result'] . '" />';
		}

		// Google-captcha
		if ( $captcha == 3 ) {
			$rec_opt       = get_option( 'gglcptch_options' );
			$relang        = get_bloginfo( 'language' );
			$captcha_input = '<div class="g-recaptcha" data-sitekey="' . $rec_opt['public_key'] . '"></div>';
			// add javascript for Google-Captcha if wanted
			$captcha_input .= "<script src='https://www.google.com/recaptcha/api.js?hl=" . substr( $relang, 0, 2 ) . "'></script>";
		}

		// terms of usage
		$tou_label = '';
		$tou_input = '';

		if ( $wpg_options['wp-greet-touswitch'] == 1 ) {
			$tou_input = "<input name='accepttou' type='checkbox' value='1' " . ( isset( $_POST['accepttou'] ) and $_POST['accepttou'] == 1 ? 'checked="checked"' : '' ) . ' />';
			if ( trim( $wpg_options['wp-greet-touswitch-text'] ) != '' ) {
				$tou_label_text = $wpg_options['wp-greet-touswitch-text'];
			} else {
				$tou_label_text = __( 'I accept the terms of usage of the greeting card service', 'wp-greet' );
			}
			$tou_label  = $tou_label_text . ' <a href="#TB_inline&width=600&height=600&inlineId=wpg-tou" class="thickbox">' . __( '(show)', 'wp-greet' ) . '</a>';
			$tou_label .= '<div id="wpg-tou" style="display:none;"><p>' . $wpg_options['wp-greet-termsofusage'] . '</p></div>';

		}
		// submit buttons
		$preview_button = "<input name='action' type='submit' value='" . __( 'Preview', 'wp-greet' ) . "' />";
		$reset_button   = "<input type='reset' value='" . __( 'Reset form', 'wp-greet' ) . "'/>";
		$send_button    = "<input id='sendbutton' name='action' type='submit'  value='" . __( 'Send', 'wp-greet' ) . "' />";

		// retrieve vcode form session if available
		if ( isset( $_SESSION['vcode'] ) and ! isset( $_POST['vcode'] ) ) {
				$_POST['vcode'] = $_SESSION['vcode'];
		}
		$vcode_input = "<input id='vcode' name='vcode' type='text' maxlength='15' value='" . ( isset( $_POST['vcode'] ) ? $_POST['vcode'] : '' ) . "'/>";
		// load template
		ob_start();
		include plugin_dir_path( __FILE__ ) . '/templates/wp-greet-form-template.php';
		$template = ob_get_clean();
		// replace placeholders
		$template = str_replace( '{%sendername_label%}', $sendername_label, $template );
		$template = str_replace( '{%sendername_input%}', $sendername_input, $template );
		$template = str_replace( '{%sendermail_label%}', $sendermail_label, $template );
		$template = str_replace( '{%sendermail_input%}', $sendermail_input, $template );
		$template = str_replace( '{%ccsender_label%}', $ccsender_label, $template );
		$template = str_replace( '{%ccsender_input%}', $ccsender_input, $template );
		$template = str_replace( '{%confirm_label%}', $confirm_label, $template );
		$template = str_replace( '{%confirm_input%}', $confirm_input, $template );
		$template = str_replace( '{%recvname_label%}', $recvname_label, $template );
		$template = str_replace( '{%recvname_input%}', $recvname_input, $template );
		$template = str_replace( '{%recvbcc_label%}', $recvbcc_label, $template );
		$template = str_replace( '{%recvbcc_input%}', $recvbcc_input, $template );
		$template = str_replace( '{%recvmail_label%}', $recvmail_label, $template );
		$template = str_replace( '{%recvmail_input%}', $recvmail_input, $template );
		$template = str_replace( '{%subject_label%}', $subject_label, $template );
		$template = str_replace( '{%subject_input%}', $subject_input, $template );
		$template = str_replace( '{%image_url%}', $image_url, $template );
		$template = str_replace( '{%message_label%}', $message_label, $template );
		$template = str_replace( '{%message_input%}', $message_input, $template );
		$template = str_replace( '{%audio_label%}', $audio_label, $template );
		$template = str_replace( '{%audio_input%}', $audio_input, $template );
		$template = str_replace( '{%futuresend_label%}', $futuresend_label, $template );
		$template = str_replace( '{%futuresend_input%}', $futuresend_input, $template );
		$template = str_replace( '{%smilies_label%}', $smilies_label, $template );
		$template = str_replace( '{%smilies_input%}', $smilies_input, $template );
		$template = str_replace( '{%captcha_label%}', $captcha_label, $template );
		$template = str_replace( '{%captcha_input%}', $captcha_input, $template );
		$template = str_replace( '{%tou_label%}', $tou_label, $template );
		$template = str_replace( '{%tou_input%}', $tou_input, $template );
		$template = str_replace( '{%preview_button%}', $preview_button, $template );
		// if we use amazon do not display send button
		if ( defined( 'AMACARDS' ) ) {
			$template = str_replace( '{%send_button%}', '', $template );
		} elseif ( defined( 'WCCARDS' ) and function_exists( 'is_wp_greet_woocommerce_enabled' ) and is_wp_greet_woocommerce_enabled() ) {
			$sbutt    = "<input id='sendbutton' name='action' type='submit'  value='" . __( 'Add to Cart', 'wp-greet' ) . "' />";
			$template = str_replace( '{%send_button%}', $sbutt, $template );
		} else {
			$template = str_replace( '{%send_button%}', $send_button, $template );
		}

		$template = str_replace( '{%reset_button%}', $reset_button, $template );
		$template = str_replace( '{%vcode_input%}', $vcode_input, $template );
		// add autotext if applicable
		if ( is_plugin_active( 'wp-greet-autotext/wp-greet-autotext.php' ) ) {
			$template = apply_filters( 'wp-greet-autotext', $template, $picurl );
		}

		// add offer stamp if applicable
		if ( $wpg_options['wp-greet-offer-stamps'] ) {
			$template = apply_filters( 'wp-greet-offerstamp', $template );
		}

		 // add BuddyPress group receiver selector
		if ( $wpg_options['wp-greet-offer-stamps-buddypress-adminonly'] and function_exists( 'bp_is_active' ) ) {
			$template = apply_filters( 'wp-greet-buddypress-group-selector', $template );
		}

		// make sure parameters are sent when normal get mechanism is off for BWCards
		$bwout = '';

		if ( defined( 'BWCARDS' ) ) {
			$bwout .= "<input name='image' type='hidden' value='$picurl' />\n";
			$bwout .= "<input name='gallery' type='hidden' value='$galleryID' />\n";
			$bwout .= "<input name='pid' type='hidden' value='$pid' />\n";
			$bwout .= "<input name='approved' type='hidden' value='$approved' />\n";
		}

		// put hidden fields for calculating the time difference between server and client time
		$timediff = '';
		if ( $wpg_options['wp-greet-future-send'] ) {
			$timediff .= "<input name='clienttz' id='clienttz' type='hidden' value='-1' />\n";
			$timediff .= "<script type='text/javascript'>jQuery(document).ready(function() {var d=new Date(); n=d.getTimezoneOffset();document.getElementById(\"clienttz\").value = n;});</script>";
		}

		// put image url hidden field
		$template .= "<input id='wpg-image-url' name='wpg-image-url' type='hidden' value='" . $picurl . "' />\n";

		// put formtag around output
		$out = "<div class='wp-greet-form'><form method='post' action='#'>\n" . $out . $bwout . $template . $timediff . '</form></div><p>&nbsp;';
	}
	// Rueckgabe des HTML Codes
	return $out;
}

// anzeige einer grußkarte über den karten code


function showGreetcard( $display ) {
	// hole optionen
	$wpg_options = wpgreet_get_options();
	// ausgabebuffer init
	$out = '';
	// get translation
	load_plugin_textdomain( 'wp-greet', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	$pid = -1;

	global $wpdb;
	$sql  = 'select * from ' . $wpdb->prefix . "wpgreet_cards where fetchcode='" . $display . "';";
	$res  = $wpdb->get_row( $sql );
	$now  = strtotime( gmdate( 'Y-m-d H:i:s', time() + ( get_option( 'gmt_offset' ) * 60 * 60 ) ) );
	$then = mysql2time( $res->fetchuntil );
	// NGG picture ID ermitteln
	$fname = substr( $res->picture, strrpos( $res->picture, '/' ) + 1 );
	$sql   = 'select pid from ' . $wpdb->prefix . "ngg_pictures where filename='$fname';";
	$res1  = $wpdb->get_row( $sql );
	if ( null != $res1 ) {
		$pid = $res1->pid;
	}
	// WordPress native gallery ID ermitteln

	if ( $wpg_options['wp-greet-external-link'] == '1' ) {
		$fname = $res->picture;
		$sql   = 'select ID from ' . $wpdb->prefix . "posts where post_type='attachment' and guid='$fname';";
		$res1  = $wpdb->get_row( $sql );
		if ( null != $res1 ) {
			$pid = $res1->ID;
		}
	}

	if ( is_null( $res ) ) {
		// ungültiger code
		$out .= __( 'Your verification code is invalid.', 'wp-greet' ) . '<br />' . __( 'Send a new card at', 'wp-greet' ) . " <a href='" . site_url() . "' >" . site_url() . '</a>';
		return $out;
	} elseif ( $now > $then ) {
		// die gültigkeiteisdauer ist abgelaufen
		$out .= __( 'Your greetcard link is timed out.', 'wp-greet' ) . '<br />' . __( 'Send a new card at', 'wp-greet' ) . " <a href='" . site_url() . "' >" . site_url() . '</a>';
		return $out;
	} else {
		// alles okay, karte anzeigen

		// message escapen
		$show_message = $res->mailbody;
		// smilies ersetzen

		if ( $wpg_options['wp-greet-smilies'] ) {
			$smprefix = get_option( 'siteurl' ) . '/wp-content/plugins/wp-greet/smilies/';
			preg_match_all( '(:[^\040]+:)', $show_message, $treffer );

			foreach ( array_unique( $treffer[0] ) as $sm ) {
				$smrep        = '<img src="' . $smprefix . substr( $sm, 1, strlen( $sm ) - 2 ) . '" alt="' . $sm . '" />';
				$show_message = str_replace( $sm, $smrep, $show_message );
			}
		}
		// Karte als abgeholt markieren
		mark_fetchcard( $display );
		// und log eintrag vornehmen
		log_greetcard( '', get_option( 'blogname' ), '', 'Card fetched: ' . $display );
		// und falls gewünscht und noch nicht erfolgt bestätigung an sender schicken

		if ( $res->cc2from & 2 and $res->card_fetched == '0000-00-00 00:00:00' ) {
			sendGreetcardConfirmation( $res->frommail, $res->fromname, $res->tomail, $res->toname, $wpg_options['wp-greet-ocduration'], $display );
			log_greetcard( '', get_option( 'blogname' ), '', 'Confirmation mail sent to sender for card:' . $display );
		}
		// load template
		ob_start();
		include plugin_dir_path( __FILE__ ) . '/templates/wp-greet-display-template.php';
		$template = ob_get_clean();
		// replace placeholders
		$template = str_replace( '{%sendername%}', $res->fromname, $template );
		$template = str_replace( '{%sendermail%}', $res->frommail, $template );
		$template = str_replace( '{%ccsender%}', $res->cc2from, $template );
		$template = str_replace( '{%recvname%}', $res->toname, $template );
		$template = str_replace( '{%recvmail%}', $res->tomail, $template );
		$template = str_replace( '{%subject%}', $res->subject, $template );
		$template = str_replace( '{%audiourl%}', $res->audiourl, $template );
		$template = str_replace( '{%wp-greet-default-header%}', $wpg_options['wp-greet-default-header'], $template );
		$template = str_replace( '{%wp-greet-default-footer%}', $wpg_options['wp-greet-default-footer'], $template );
		$template = str_replace( '{%image_url%}', get_imgtag( $pid, $res->picture ), $template );
		$template = str_replace( '{%message%}', htmlspecialchars_decode( $show_message ), $template );
	}
	return $template;
}

