<?php
/*
Plugin Name: wp-greet
Plugin URI: http://www.tuxlog.de
Description: wp-greet is a WordPress plugin to send greeting cards from your WordPress blog.
Version: 6.2
Author: Hans Matzen <webmaster at tuxlog.de>
Author URI: http://www.tuxlog.de
Text Domain: wp-greet
*/

/*
  Copyright 2008-2021  Hans Matzen  (email : webmaster at tuxlog dot de)

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

define( 'WP_GREET_VERSION', '6.1' );

// global options array
$wpg_options = array();

// include setup functions
$plugin_prefix_root     = plugin_dir_path( __FILE__ );
$plugin_prefix_filename = "{$plugin_prefix_root}/setup.php";
require_once $plugin_prefix_filename;

// include functions
require_once 'wpg-func.php';
global $wp_version;
if ( (float) $wp_version >= 5.5 ) {
	require_once 'wpg-func-mail6.php';
} else {
	require_once 'wpg-func-mail.php';
}
// include admin options page
require_once 'wpg-admin.php';
require_once 'wpg-admin-log.php';
require_once 'wpg-admin-gal.php';
require_once 'wpg-admin-mail.php';
require_once 'wpg-admin-sec.php';

// include form page
require_once 'wpg-offerstamp.php';
require_once 'wpg-form.php';


function wp_greet_init() {
	// optionen laden
	global $wpg_options;
	$wpg_options = wpgreet_get_options();

	// Action calls for all functions
	add_shortcode( 'wp-greet', 'sc_searchwpgreet' );

	// add filter to exclude wp-greet-formpage from home page
	add_filter( 'pre_get_posts', 'wpg_ExcludeFromHomepage' );

	// filter for ngg integration
	if ( $wpg_options['wp-greet-gallery'] == 'ngg' ) {
		add_filter( 'ngg_create_gallery_link', 'ngg_connect', 1, 2 );
		// next line from ngg-version 1.0 on
		add_filter( 'ngg_get_thumbcode', 'ngg_remove_thumbcode', 2, 2 );
	}

	// filter for wp integration
	if ( $wpg_options['wp-greet-gallery'] == 'wp' ) {
		add_filter( 'post_gallery', 'wpgreet_post_gallery', 9999, 2 );
		// fitler gutenberg gallery block
		add_filter( 'render_block', 'wpgreet_gutenberg_gallery', 9999, 2 );
	}

	// add actions for future send
	if ( $wpg_options['wp-greet-future-send'] == '1' ) {
		// add actions for future send
		add_action( 'wpgreet_sendcard_link', 'cron_sendGreetCardLink', 10, 9 );
		add_action( 'wpgreet_sendcard_mail', 'cron_sendGreetCardMail', 10, 10 );
	}
}

function wp_greet_scripts() {
	// optionen laden
	global $post, $wpg_options;
	$wpg_options = wpgreet_get_options();

	// add css in header
	if ( ! is_admin() && ! $wpg_options['wp-greet-disable-css'] ) {
		// load possible form pages into one array
		global $wpdb;
		$sql                 = 'SELECT id FROM ' . $wpdb->prefix . "posts WHERE post_type in ('page','post') and post_content like '%[wp-greet]%' order by id;";
		$possible_form_pages = $wpdb->get_col( $sql );

		if ( in_array( $post->ID, $possible_form_pages ) ) {
			wp_enqueue_style( 'wp-greet', plugins_url( 'wp-greet.css', __FILE__ ) );
		}
	}

	if ( $post->ID == $wpg_options['wp-greet-formpage'] ) {

		// add thickbox for frontend
		if ( $wpg_options['wp-greet-touswitch'] and ! is_admin() ) {
			add_action( 'wp_print_scripts', 'wpg_add_thickbox_script' );
			add_action( 'wp_print_styles', 'wpg_add_thickbox_style' );
		}

		// javascript fuer smilies ausgeben falls notwendig
		if ( ! is_admin() and $wpg_options['wp-greet-smilies'] == '1' ) {
			if ( $wpg_options['wp-greet-tinymce'] == '1' ) {
				// javascript mit tinymce
				wp_enqueue_script( 'wpg-smile', plugins_url( 'wp-greet/smilies_tinymce.js', dirname( __FILE__ ) ) );
			} else {
				// javascript ohne tinyMCE
				wp_enqueue_script( 'wpg-smile', plugins_url( 'wp-greet/smilies.js', dirname( __FILE__ ) ) );
			}
		}

		// add jquery extensions for datepicker if applicable
		if ( $wpg_options['wp-greet-future-send'] and ! is_admin() ) {
			$locale = trim( substr( get_locale(), 0, 2 ) );
			wp_enqueue_script( 'wp-greet-flatpcikr-js', plugins_url( 'wp-greet/flatpickr/flatpickr.min.js' ) );
			wp_enqueue_style( 'wp-greet-flatpickr-css', plugins_url( 'wp-greet/flatpickr/flatpickr.min.css' ) );
			wp_enqueue_script( 'wp-greet-datepicker-locale', plugins_url( "wp-greet/flatpickr/l10n/$locale.js" ) );

		}

		if ( $wpg_options['wp-greet-audio'] ) {
			wp_enqueue_script( 'wpg-howler', plugins_url( 'wp-greet/howler/howler.min.js', dirname( __FILE__ ) ), array() );
		}
	}
}

function wp_greet_admin_scripts() {
	// add admin javascript
	if ( is_admin() ) {
		wp_enqueue_script( 'thickbox' );
		wp_enqueue_style( 'thickbox' );
		wp_enqueue_script( 'wpg_admin', plugins_url( 'wpg_admin.js', __FILE__ ), array(), '9999' );
	}
}


function wpg_add_menus() {
	$PPATH = ABSPATH . PLUGINDIR . '/wp-greet/';

	// get translation
	load_plugin_textdomain( 'wp-greet', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	add_menu_page( 'wp-greet', 'wp-greet', 'manage_options', $PPATH . 'wpg-admin.php', 'wpg_admin_form', site_url( '/wp-content/plugins/wp-greet' ) . '/wp-greet.png' );

	add_submenu_page( $PPATH . 'wpg-admin.php', __( 'Galleries', 'wp-greet' ), __( 'Galleries', 'wp-greet' ), 'manage_options', $PPATH . 'wpg-admin-gal.php', 'wpg_admin_gal', 1 );

	add_submenu_page( $PPATH . 'wpg-admin.php', __( 'Mail', 'wp-greet' ), __( 'Mail', 'wp-greet' ), 'manage_options', $PPATH . 'wpg-admin-mail.php', 'wpg_admin_mail', 2 );

	add_submenu_page( $PPATH . 'wpg-admin.php', __( 'Security', 'wp-greet' ), __( 'Security', 'wp-greet' ), 'manage_options', $PPATH . 'wpg-admin-sec.php', 'wpg_admin_sec', 3 );

	add_submenu_page( $PPATH . 'wpg-admin.php', __( 'Logging', 'wp-greet' ), __( 'Logging', 'wp-greet' ), 'manage_options', $PPATH . 'wpg-admin-log.php', 'wpg_admin_log', 4 );

}

// add thickbox to page headers
function wpg_add_thickbox_script() {
	global $post,$wpg_options;
	if ( is_object( $post ) and $wpg_options['wp-greet-formpage'] == $post->ID ) {
		wp_enqueue_script( 'thickbox' );
	}
}

// add thickbox to page headers
function wpg_add_thickbox_style() {
	 global $post, $wpg_options;
	if ( is_object( $post ) and $wpg_options['wp-greet-formpage'] == $post->ID ) {
		wp_enqueue_style( 'thickbox' );
	}
}

// wrapper functions for wp_cron trigger
function cron_sendGreetCardMail( $mid, $sender, $sendername, $recv, $recvname, $title,
				$msgtext, $picurl, $ccsender, $debug = false ) {
	sendGreetcardMail( $sender, $sendername, $recv, $recvname, $title, $msgtext, $picurl, $ccsender, $debug );
	log_greetcard( $recv, addslashes( $sender ), $picurl, $msgtext );
	// update send time in wpgreet_cards
	global $wpdb;
	$now = gmdate( 'Y-m-d H:i:s', time() + ( get_option( 'gmt_offset' ) * 60 * 60 ) );
	$sql = 'update ' . $wpdb->prefix . "wpgreet_cards set card_sent='$now' where mid=$mid;";
	$wpdb->query( $sql );
}

function cron_sendGreetCardLink( $mid, $sender, $sendername, $recv, $recvname, $duration, $fetchcode, $ccsender, $debug = false ) {
	sendGreetcardLink( $sender, $sendername, $recv, $recvname, $duration, $fetchcode, $ccsender, $debug );
	mark_sentcard( $fetchcode );
}

//
// this removes the wp-greet-formapge from the pot query used to get the posts to show on the homepage
//
function wpg_ExcludeFromHomepage( $query ) {
	if ( $query->is_home ) {
		// load possible form pages into one array
		global $wpdb;
		$sql              = 'SELECT id FROM ' . $wpdb->prefix . "posts WHERE post_type in ('page','post') and post_content like '%[wp-greet]%' order by id;";
		$posts_to_exclude = $wpdb->get_col( $sql );

		// if any exists
		if ( $posts_to_exclude ) {
			// merge with existing excludes in the query
			(array) $posts_exclude_before = $query->get( 'post__not_in' );
			if ( ! empty( $posts_exclude_before ) && is_array( $posts_exclude_before ) ) {
				$posts_to_exclude = array_unique( array_merge( $posts_to_exclude, $posts_exclude_before ) );
			}
			// set new exclude list
			$query->set( 'post__not_in', $posts_to_exclude );
		}
	}
	return $query;
}
//
// MAIN
//
register_activation_hook( __FILE__, 'wp_greet_activate' );
register_deactivation_hook( __FILE__, 'wp_greet_deactivate' );


// add admin notice for broken NGG >= 2.0.0
add_action( 'admin_notices', 'wpg_fix_broken_ngg_hint' );
// add admin menu
add_action( 'admin_menu', 'wpg_add_menus' );

// init plugin
add_action( 'init', 'wp_greet_init' );
add_action( 'wp_enqueue_scripts', 'wp_greet_scripts' );
add_action( 'admin_enqueue_scripts', 'wp_greet_admin_scripts' );

