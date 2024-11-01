<?php
/* This file is part of the wp-greet plugin for WordPress */

/*
  Copyright 2008-2016  Hans Matzen  (email : webmaster at tuxlog.de)

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
// setting up the default options in table wp-options during
// plugin activation from the plugins page
//
function wp_greet_activate() {
	global $wpdb, $wp_roles, $wp_version,$wpg_options;

	// upgrade function changed in WordPress 2.3
	if ( version_compare( $wp_version, '2.3', '>=' ) ) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	} else {
		require_once ABSPATH . 'wp-admin/upgrade-functions.php';
	}

	$wpg_options = wpgreet_get_options();

	$wpg_options['wp-greet-version'] = WP_GREET_VERSION;
	update_option(
		'wp-greet-version',
		$wpg_options['wp-greet-version'],
		'versionnumber of the installed wp-greet',
		'yes'
	);

	// retrieve collation
	// Use the character set and collation that's configured for WP tables
	$charset_collate = '';
	if ( ! empty( $wpdb->charset ) ) {
		// Some German installs use "utf-8" (invalid) instead of "utf8" (valid). None of
		// the charset ids supported by MySQL contain dashes, so we can safely strip them.
		// See http://dev.mysql.com/doc/refman/5.0/en/charset-charsets.html
		$charset = str_replace( '-', '', $wpdb->charset );

		$charset_collate = "DEFAULT CHARACTER SET {$charset}";
	}
	if ( ! empty( $wpdb->collate ) ) {
		$charset_collate .= " COLLATE {$wpdb->collate}";
	}

	// create table
	$sql = 'CREATE TABLE ' . $wpdb->prefix . 'wpgreet_stats' . " (
			mid BIGINT NOT NULL AUTO_INCREMENT ,
            senttime DATETIME NOT NULL,
			frommail VARCHAR(80) NOT NULL,
            tomail VARCHAR(80) NOT NULL,
            picture VARCHAR(255) NOT NULL,
            mailbody MEDIUMTEXT NOT NULL,
            remote_ip VARCHAR(15) NOT NULL,
	    PRIMARY KEY  (mid)
	    ) $charset_collate ;";

	dbDelta( $sql );

	// create table for greet card store (since v1.7)
	$sql = 'CREATE TABLE ' . $wpdb->prefix . 'wpgreet_cards' . " (
	    mid BIGINT NOT NULL AUTO_INCREMENT ,
            fromname VARCHAR(80) NOT NULL,
	        frommail VARCHAR(80) NOT NULL,
            toname VARCHAR(80) NOT NULL,
            tomail VARCHAR(512) NOT NULL,
            cc2from smallint NOT NULL,
            subject VARCHAR(255) NOT NULL,
            picture TEXT NOT NULL,
            mailbody TEXT NOT NULL,
            confirmuntil timestamp NOT NULL,
            confirmcode VARCHAR(32) NOT NULL,
            fetchuntil timestamp NOT NULL,
            fetchcode VARCHAR(32) NOT NULL,
            card_sent timestamp NOT NULL,
	        card_fetched timestamp NOT NULL,
	        future_send timestamp NOT NULL,
	        session_id VARCHAR(32) NOT NULL,
	        audiourl TEXT NOT NULL,
	        sendbcc smallint NOT NULL,
            PRIMARY KEY  (mid)
	    ) $charset_collate ;";

	dbDelta( $sql );

	if ( $wpg_options['wp-greet-minseclevel'] == '' ) {
		$wpg_options['wp-greet-minseclevel'] = 'Registered';
		add_option( 'wp-greet-minseclevel', $wpg_options['wp-greet-minseclevel'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-captcha'] == '' ) {
		$wpg_options['wp-greet-captcha'] = 0;
		add_option( 'wp-greet-captcha', $wpg_options['wp-greet-captcha'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-mailreturnpath'] == '' ) {
		$wpg_options['wp-greet-mailreturnpath'] = '';
		add_option( 'wp-greet-mailreturnpath', $wpg_options['wp-greet-mailreturnpath'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-staticsender'] == '' ) {
		$wpg_options['wp-greet-staticsender'] = '';
		add_option( 'wp-greet-staticsender', $wpg_options['wp-greet-staticsender'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-autofillform'] == '' ) {
		$wpg_options['wp-greet-autofillform'] = 1;
		add_option( 'wp-greet-autofillform', $wpg_options['wp-greet-autofillform'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-imagewidth'] == '' ) {
		$wpg_options['wp-greet-imagewidth'] = '100%';
		add_option( 'wp-greet-imagewidth', $wpg_options['wp-greet-imagewidth'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-stampimage'] == '' ) {
		$wpg_options['wp-greet-stampimage'] = 'wp-content/plugins/wp-greet/defaultstamp.jpg';
		add_option( 'wp-greet-stampimage', $wpg_options['wp-greet-stampimage'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-stamppercent'] == '' ) {
		$wpg_options['wp-greet-stamppercent'] = 15;
		add_option( 'wp-greet-stamppercent', $wpg_options['wp-greet-stamppercent'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-logging'] == '' ) {
		$wpg_options['wp-greet-logging'] = 1;
		add_option( 'wp-greet-logging', $wpg_options['wp-greet-logging'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-gallery'] == '' ) {
		$wpg_options['wp-greet-gallery'] = 'ngg';
		add_option( 'wp-greet-gallery', $wpg_options['wp-greet-gallery'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-linesperpage'] == '' ) {
		$wpg_options['wp-greet-linesperpage'] = '10';
		add_option( 'wp-greet-linesperpage', $wpg_options['wp-greet-linesperpage'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-usesmtp'] == '' ) {
		$wpg_options['wp-greet-usesmtp'] = '1';
		add_option( 'wp-greet-usesmtp', $wpg_options['wp-greet-usesmtp'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-touswitch'] == '' ) {
		$wpg_options['wp-greet-touswitch'] = '1';
		add_option( 'wp-greet-touswitch', $wpg_options['wp-greet-touswitch'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-touswitch-text'] == '' ) {
		$wpg_options['wp-greet-touswitch-text'] = 'Ich akzeptiere die Nutzungsbedingungen des Grußkartendienstes';
		add_option( 'wp-greet-touswitch-text', $wpg_options['wp-greet-touswitch-text'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-termsofusage'] == '' ) {
		$wpg_options['wp-greet-termsofusage'] = '<h2>Grußkarten (eCard) System: Nutzungsbedinungen- und Hinweise</h2>
<p>Bevor Sie eine Grußkarte absenden können, müssen Sie die Grußkarte bestätigen. Dazu erhalten Sie nach absenden der Grußkarte eine Email, die einen Bestätigungslink enthält. Erst nach klicken dieses Links wird Ihre Grußkarte tatsächlich abgeschickt.</p>

<p>Mit Absenden Ihrer eCard haben Sie nachfolgende Hinweise gelesen und bestätigt.
Der eCard Versender stellt sicher, dass er bei der Nutzung unseres eCard Service nicht gegen geltende Rechtsvorschriften verstößt. Der Versender stellt ebenso sicher und bestätigt, vor versenden seiner eCard, dass die Empfänger der verschickten Nachrichten mit dem Empfang der Nachricht einverstanden sind. Die Versendung kommerzieller Werbe Mails über den Grußkartendienst wird hiermit ausdrücklich untersagt. Ebenfalls ausdrücklich untersagt ist die Versendung von unerwünschten Mails (SPAM) oder Massen-Mails.</p>

<p>Das Urheberrecht aller eCards liegt beim Betreiber dieser Site. Die Genehmigung zum Versenden der eCards zwecks Grußkartenübermittlung ist hiermit erteilt. Jegliche weitere Nutzung bedarf einer schriftlichen Genehmigung.</p>

<p>Datenschutz: Ihre eMail-Adresse und die eMail-Adresse des Empfängers werden ausschließlich zu Übertragungszwecken verwendet . Zum Einen um Ihnen die Möglichkeit zu geben die Karte als Absender per Email zu bestätigen zum Anderen um den Empfänger über die eCard zu informieren, bzw. Sie, als Absender einer eCard, bei einem Fehler beim Versenden, zu benachrichtigen.</p>';
		add_option( 'wp-greet-termsofusage', $wpg_options['wp-greet-termsofusage'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-mailconfirm'] == '' ) {
		$wpg_options['wp-greet-mailconfirm'] = '0';
		add_option( 'wp-greet-mailconfirm', $wpg_options['wp-greet-mailconfirm'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-mcduration'] == '' ) {
		$wpg_options['wp-greet-mcduration'] = '0';
		add_option( 'wp-greet-mcduration', $wpg_options['wp-greet-mcduration'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-mctext'] == '' ) {
		$wpg_options['wp-greet-mctext'] = 'Hallo %sender%,
bitte bestätigen Sie, dass Sie %sender% (%sendermail%) eine Grußkarte senden möchten, indem Sie auf den unten stehenden Link klicken.

Bestätigungslink: %link%

Wenn Sie nicht wissen warum Sie diese E-Mail erhalten oder die Postkarte nicht absenden möchten, dann ignorieren Sie diese E-Mail oder kontaktieren den Administrator, indem Sie einfach auf diese E-Mail antworten.

Mit freundlichen Grüßen
wp-greet, das freundliche WordPress Grußkartenplugin';
		add_option( 'wp-greet-mctext', $wpg_options['wp-greet-mctext'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-ectext'] == '' ) {
		$wpg_options['wp-greet-ectext'] = 'Hallo %sender% (%sendermail%),
Ihre Grusskarte wurde unter dem %link% abgeholt. Vielen Dank, dass Sie den Grusskartendienst von wp-greet genutzt haben.

Wenn Sie nicht wissen warum Sie diese E-Mail erhalten oder die Postkarte nicht absenden möchten, dann ignorieren Sie diese E-Mail oder kontaktieren den Administrator, indem Sie einfach auf diese E-Mail antworten.

Mit freundlichen Grüßen
wp-greet, das freundliche WordPress Grußkartenplugin';
		add_option( 'wp-greet-ectext', $wpg_options['wp-greet-ectext'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-onlinecard'] == '' ) {
		$wpg_options['wp-greet-onlinecard'] = '0';
		add_option( 'wp-greet-onlinecard', $wpg_options['wp-greet-onlinecard'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-ocduration'] == '' ) {
		$wpg_options['wp-greet-ocduration'] = '0';
		add_option( 'wp-greet-ocduration', $wpg_options['wp-greet-ocduration'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-octext'] == '' ) {
		$wpg_options['wp-greet-octext'] = "Hallo %receiver%,\n\n Sie haben eine Grußkarte von %sender% erhalten. Die Grußkarte kann unter %link% abgeholt werden.\n

Die Postkarte steht für Sie für die nächsten %duration% Tage zur Abholung bereit.

Mit freundlichen Grüßen
wp-greet, das freundliche WordPress Grußkartenplugin";
		add_option( 'wp-greet-octext', $wpg_options['wp-greet-octext'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-logdays'] == '' ) {
		$wpg_options['wp-greet-logdays'] = '0';
		add_option( 'wp-greet-logdays', $wpg_options['wp-greet-logdays'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-carddays'] == '' ) {
		$wpg_options['wp-greet-carddays'] = '0';
		add_option( 'wp-greet-carddays', $wpg_options['wp-greet-carddays'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-fields'] == '' ) {
		$wpg_options['wp-greet-fields'] = '010100'; // sender and receiver email is allways mandatory
		add_option( 'wp-greet-fields', $wpg_options['wp-greet-fields'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-tinymce'] == '' ) {
		$wpg_options['wp-greet-tinymce'] = '1'; // use TinyMCE editor in form
		add_option( 'wp-greet-tinymce', $wpg_options['wp-greet-fields'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-smtp-host'] == '' ) {
		$wpg_options['wp-greet-smtp-host'] = 'localhost';
		add_option( 'wp-greet-smtp-host', $wpg_options['wp-greet-smtp-host'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-smtp-port'] == '' ) {
		$wpg_options['wp-greet-smtp-port'] = '25';
		add_option( 'wp-greet-smtp-port', $wpg_options['wp-greet-smtp-port'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-smtp-ssl'] == '' ) {
		$wpg_options['wp-greet-smtp-ssl'] = '0';
		add_option( 'wp-greet-smtp-ssl', $wpg_options['wp-greet-smtp-ssl'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-smtp-user'] == '' ) {
		$wpg_options['wp-greet-smtp-user'] = '';
		add_option( 'wp-greet-smtp-user', $wpg_options['wp-greet-smtp-user'], '', 'yes' );
	};

	if ( $wpg_options['wp-greet-smtp-pass'] == '' ) {
		$wpg_options['wp-greet-smtp-pass'] = '';
		add_option( 'wp-greet-smtp-pass', $wpg_options['wp-greet-smtp-pass'], '', 'yes' );
	};
}

function wp_greet_deactivate() {
	return; // avoid deletion of opts

	 $options = array(
		 'wp-greet-version'          => '',
		 'wp-greet-minseclevel'      => '',
		 'wp-greet-captcha'          => '',
		 'wp-greet-mailreturnpath'   => '',
		 'wp-greet-staticsender'     => '',
		 'wp-greet-autofillform'     => '',
		 'wp-greet-bcc'              => '',
		 'wp-greet-imgattach'        => '',
		 'wp-greet-default-title'    => '',
		 'wp-greet-default-header'   => '',
		 'wp-greet-default-footer'   => '',
		 'wp-greet-imagewidth'       => '',
		 'wp-greet-logging'          => '',
		 'wp-greet-gallery'          => '',
		 'wp-greet-formpage'         => '',
		 'wp-greet-galarr'           => array(),
		 'wp-greet-smilies'          => '',
		 'wp-greet-linesperpage'     => '',
		 'wp-greet-usesmtp'          => '',
		 'wp-greet-stampimage'       => '',
		 'wp-greet-stamppercent'     => '',
		 'wp-greet-mailconfirm'      => '',
		 'wp-greet-mcduration'       => '',
		 'wp-greet-mctext'           => '',
		 'wp-greet-touswitch'        => '',
		 'wp-greet-termsofusage'     => '',
		 'wp-greet-onlinecard'       => '',
		 'wp-greet-ocduration'       => '',
		 'wp-greet-octext'           => '',
		 'wp-greet-logdays'          => '',
		 'wp-greet-carddays'         => '',
		 'wp-greet-fields'           => '',
		 'wp-greet-show-ngg-desc'    => '',
		 'wp-greet-enable-confirm'   => '',
		 'wp-greet-future-send'      => '',
		 'wp-greet-multi-recipients' => '',
		 'wp-greet-ectext'           => '',
		 'wp-greet-offerresend'      => '',
		 'wp-greet-tinymce'          => '',
		 'wp-greet-external-link'    => '',
		 'wp-greet-disable-css'      => '',
		 'wp-greet-use-wpml-lang'    => '',
		 'wp-greet-smtp-host'        => '',
		 'wp-greet-smtp-port'        => '',
		 'wp-greet-smtp-ssl'         => '',
		 'wp-greet-smtp-user'        => '',
		 'wp-greet-smtp-pass'        => '',
		 'wp-greet-audio'            => '',
	 );

	 foreach ( $options as $key => $val ) {
		 delete_option( $val );
	 }
}
