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


function wpg_offerstamp_output_form_selector( $template ) {

	$wpg_opt = wpgreet_get_options();

	// check for Buddpress parameters
	$bp_stamps = '';
	if ( defined( 'BP_VERSION' ) && bp_is_active( 'groups' ) && is_user_logged_in() && $wpg_opt['wp-greet-offer-stamps-buddypress'] ) {
			global $bp;
			$cuserid = $bp->loggedin_user->id;
			$cgroups = BP_Groups_Member::get_group_ids( get_current_user_id() );

			$bp_stamps = '';
		foreach ( $cgroups['groups'] as $i ) {
			$avatar_options = array(
				'item_id'    => intval( $i ),
				'object'     => 'group',
				'type'       => 'full',
				'avatar_dir' => 'group-avatars',
				'alt'        => 'Group avatar',
				'css_id'     => 1234,
				'class'      => 'avatar',
				'width'      => 50,
				'height'     => 50,
				'html'       => false,
			);
			$cavatarurl     = bp_core_fetch_avatar( $avatar_options );
			$cgroup         = groups_get_group( array( 'group_id' => $i ) );
			$cbp_group_name = $cgroup->name;

			$cisadmin = groups_is_user_admin( $cuserid, intval( $i ) );
			if ( ! $wpg_opt['wp-greet-offer-stamps-buddypress-adminonly'] or $cisadmin ) {
				$cbp_stamp_selected = '';
				if ( isset( $_POST['wp-greet-offer-stamp'] ) and $_POST['wp-greet-offer-stamp'] == $cavatarurl ) {
					$cbp_stamp_selected = 'selected="selected"';
				}
				$bp_stamps .= '<option value ="' . $cavatarurl . "\" $cbp_stamp_selected >" . $cbp_group_name . "</option>\n";
			}
		}
	}

	$url         = plugin_dir_url( __FILE__ );
	$stampdir    = plugin_dir_path( __FILE__ ) . 'stamps';
	$stampfiles  = scandir( $stampdir );
	$stamp_input = '<select id="wp-greet-offer-stamp" name="wp-greet-offer-stamp" onchange="wpg_switch_stamp();">' . "\n";

	foreach ( $stampfiles as $i ) {
		$ext = substr( $i, strlen( $i ) - 4 );
		if ( '.jpg' == $ext or '.png' == $ext ) {
			$stamp_selected = '';
			if ( isset( $_POST['wp-greet-offer-stamp'] ) and $_POST['wp-greet-offer-stamp'] == $url . 'stamps/' . $i or $wpg_opt['wp-greet-stampimage'] == $url . 'stamps/' . $i ) {
				$stamp_selected = 'selected="selected"';
			}
			$stamp_input .= '<option value ="' . $url . 'stamps/' . $i . "\" $stamp_selected>" . substr( $i, 0, strlen( $i ) - 4 ) . "</option>\n";
		}
	}
	$stamp_input .= $bp_stamps;
	$stamp_input .= "</select>\n";

	// add opacity selector
	$opacity_input = '<select id="wp-greet-offer-stamp-opacity" name="wp-greet-offer-stamp-opacity" onchange="wpg_switch_stamp();">' . "\n";

	for ( $i = 0; $i <= 10; $i++ ) {
		$opacity_selected = '';
		if ( isset( $_POST['wp-greet-offer-stamp-opacity'] ) and $_POST['wp-greet-offer-stamp-opacity'] == $i * 10 ) {
				$opacity_selected = 'selected="selected"';
		}
		$opacity_input .= '<option value ="' . $i * 10 . "\" $opacity_selected>" . $i * 10 . "%</option>\n";
	}
	$opacity_input .= "</select>\n";

	$stamp_input .= $opacity_input;

	$noneurl = build_stamp_url( 'xxx', $wpg_opt['wp-greet-stampimage'] );

	$stamp_js = <<<EOT
		<script type="text/javascript">
		function wpg_switch_stamp() {
			var NoneUrl =  "$noneurl";
			var a = document.getElementById('wp-greet-offer-stamp');
			var a1 = document.getElementById('wp-greet-offer-stamp-opacity');
			var b = document.getElementById('wpg-image');
			var c = document.getElementById('wpg-image-url');
			var new_value = a.options[a.selectedIndex].value;
			var new_opacity = 100 - a1.options[a1.selectedIndex].value;
			
			// haben wir bereits eine url mit einer briefmarke oder nicht
			var pos3 = b.src.indexOf("cci=");
			if (pos3 <= 0) {
				var u = NoneUrl.replace("xxx", c.value);
			} else {
				var u = b.src;
			}
			u = u.replace(/&amp;/g,"&");
			
			var pos4 = u.indexOf("&sto=");
			if (pos4 >= 0) {
				u = u.substring(0, pos4);
			}
			var pos1 = u.indexOf("sti=");
			var pos2 = u.indexOf("&stw=");
			
			
			var newu = u.substring(0, pos1 +4) + new_value + u.substring(pos2);
			newu = newu + "&sto=" + new_opacity;
						
			b.src= newu;
			c.value= newu;
		}
		</script>
EOT;

	$template = str_replace( '{%offerstamp_label%}', __( 'Select a predefined stamp and its opacity', 'wp-greet' ), $template );
	$template = str_replace( '{%offerstamp_input%}', $stamp_js . $stamp_input, $template );

	return $template;
}
add_filter( 'wp-greet-offerstamp', 'wpg_offerstamp_output_form_selector', 10, 1 );

function wpg_buddypress_group_output_form( $template ) {

	// get user info
	global $bp;
	$cuserid = $bp->loggedin_user->id;
	$cgroups = BP_Groups_Member::get_group_ids( get_current_user_id() );

	$selhtml = "<select style='width:200px' multiple='multiple' id='wpg-bbgroupselector' onchange='wpg_bbgroup_switch_receiver();'>";
	foreach ( $cgroups['groups'] as $i ) {
		if ( groups_is_user_admin( $cuserid, intval( $i ) ) ) {
			$groupinfo = groups_get_group( array( 'group_id' => $i ) );
			$name      = $groupinfo->name;

			// build receiver lists from selected groups
			global $wpdb;
			$recv_val = '';
			$user_ids = $wpdb->get_col( 'SELECT user_id FROM ' . $wpdb->prefix . "bp_groups_members WHERE group_id = {$groupinfo->id}" );
			foreach ( $user_ids as $u ) {
				$uo = get_userdata( $u );
				// $recv_val .= $uo->data->display_name . " <" . $uo->data->user_email . ">, ";
				$recv_val .= $uo->data->user_email . ', ';
			}
			$recv_val = substr( $recv_val, 0, -2 );
			$selhtml .= "<option value='$recv_val'>" . $groupinfo->name . '</option>';
		}
	}
	$selhtml .= '</select>';

	$bb_input = $selhtml;

	$bb_js = <<<EOT
		<script type="text/javascript">
		function wpg_bbgroup_switch_receiver() {
			
			var a = document.getElementById('wpg-bbgroupselector');
			var b = document.getElementById('recvname');
			var c = document.getElementById('recv');
			
			var rname = "";
			var rmail = "";
	
			for ( var i = 0; i < a.selectedOptions.length; i++) {
				rname = rname + a.selectedOptions[i].label + ", ";
				rmail = rmail + a.selectedOptions[i].value + ", ";
			}
			rname = rname.substr( 0, rname.length - 2 );
			rmail = rmail.substr( 0, rmail.length - 2 );

			b.value = rname;
			c.value = rmail;
		}
		</script>
EOT;

	$template = str_replace( '{%bbgroup_receiver_label%}', __( 'Select BuddyPress groups to send mail to', 'wp-greet' ), $template );
	$template = str_replace( '{%bbgroup_receiver_input%}', $bb_js . $bb_input, $template );

	return $template;
}
add_filter( 'wp-greet-buddypress-group-selector', 'wpg_buddypress_group_output_form', 10, 1 );
