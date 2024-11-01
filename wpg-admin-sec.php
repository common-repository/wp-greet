<?php
/* This file is part of the wp-greet plugin for WordPress */

/*
  Copyright 2008-2014 Hans Matzen  (email : webmaster at tuxlog dot de)

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


// generic functions
require_once 'wpg-func.php';
require_once 'supp/supp.php';

//
// form handler for the admin dialog
//
function wpg_admin_sec() {
	global $wpg_options;

	// wp-greet optionen aus datenbank lesen
	$wpg_options = wpgreet_get_options();

	// get translation
	load_plugin_textdomain( 'wp-greet', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	// if this is a POST call, save new values
	if ( isset( $_POST['info_update'] ) ) {
		$upflag = false;

		// build mandatory fields string
		$_POST['wp-greet-fields'] =
		( ! isset( $_POST['wp-greet-field-sendername'] ) ? '0' : '1' ) .
		'1' .
		( ! isset( $_POST['wp-greet-field-receivername'] ) ? '0' : '1' ) .
		'1' .
		( ! isset( $_POST['wp-greet-field-subject'] ) ? '0' : '1' ) .
		( ! isset( $_POST['wp-greet-field-message'] ) ? '0' : '1' );
		( ! isset( $_POST['wp-greet-enable-confirm'] ) ? '0' : '1' );

		reset( $wpg_options );
		$thispageoptions = array(
			'wp-greet-minseclevel',
			'wp-greet-captcha',
			'wp-greet-mailconfirm',
			'wp-greet-mcduration',
			'wp-greet-mctext',
			'wp-greet-touswitch',
			'wp-greet-touswitch-text',
			'wp-greet-termsofusage',
			'wp-greet-fields',
			'wp-greet-enable-confirm',
			'wp-greet-ectext',
		);

		foreach ( $wpg_options as $key => $val ) {
			if ( ! isset( $_POST[ $key ] ) ) {
					$_POST[ $key ] = '';
			}
			if ( in_array( $key, $thispageoptions ) and $wpg_options[ $key ] != $_POST[ $key ] ) {
				$wpg_options[ $key ] = stripslashes( trim( $_POST[ $key ] ) );
				$upflag              = true;

				// add capabiliities if necessary
				if ( $key == 'wp-greet-minseclevel' ) {
					set_permissions( $wpg_options[ $key ] );
				}
			}
		}

		// save options and put message after update
		echo "<div class='updated'><p><strong>";

		// check for captcha plugin if captcha was set
		$plugin_exists = true;
		if ( $wpg_options['wp-greet-captcha'] > 0 ) {
			$plugin_exists = false;
			$parr          = get_plugins();
			foreach ( $parr as $key => $plugin ) {
				// error_log( $plugin['Name'] . " " . ABSPATH.PLUGINDIR."/".$key );

				if ( ( $plugin['Name'] == 'CaptCha!' or $plugin['Name'] == 'Captcha' ) and
						file_exists( ABSPATH . PLUGINDIR . '/' . $key ) ) {
					$plugin_exists = true;
				}

				if ( $plugin['Name'] == 'Math Comment Spam Protection' and
						file_exists( ABSPATH . PLUGINDIR . '/' . $key ) ) {
					$plugin_exists = true;
				}

				if ( $plugin['Name'] == 'Google Captcha (reCAPTCHA) by BestWebSoft' and
					file_exists( ABSPATH . PLUGINDIR . '/' . $key ) ) {
					$plugin_exists = true;
				}
			}
		}
		if ( ! $plugin_exists ) {
			echo __( 'Captcha plugin not found.', 'wp-greet' ) . '<br />';
			$upflag = false;
		}

		if ( $upflag ) {
			wpgreet_set_options();
			echo __( 'Settings successfully updated', 'wp-greet' );
		} else {
			echo __( 'You have to change a field to update settings.', 'wp-greet' );
		}

		echo '</strong></p></div>';
	}

	?>
<script type="text/javascript">
 function wechsle_felder () {
	swa=document.getElementById('wp-greet-touswitch');
	swb=document.getElementById('wp-greet-termsofusage');
	swc=document.getElementById('wp-greet-touswitch-text');
	if (swa.checked == false) { 
		swb.readOnly = true;
		swb.style.backgroundColor = "#EEEEEE"; 
		swc.readOnly = true;
		swc.style.backgroundColor = "#EEEEEE"; 
	 } else {
		swb.readOnly = false;
		swb.style.backgroundColor = "#FFFFFF";
		swc.readOnly = false;
		swc.style.backgroundColor = "#FFFFFF";
	}
	swa=document.getElementById('wp-greet-mailconfirm');
	swb=document.getElementById('wp-greet-mcduration');
	swc=document.getElementById('wp-greet-mctext');

	if (swa.checked == false) { 
		swb.readOnly = true;
		 swc.readOnly = true;
		 swc.style.backgroundColor = "#EEEEEE"; 
	} else {
		swb.readOnly = false;
		swc.readOnly = false;
		swc.style.backgroundColor = "#FFFFFF";
	}

	swa=document.getElementById('wp-greet-enable-confirm');
	swb=document.getElementById('wp-greet-ectext');
	if (swa.checked == false) {
	   swb.readOnly = true;
	   swb.style.backgroundColor = "#EEEEEE"; 
	} else {
	   swb.readOnly = false;  
	   swb.style.backgroundColor = "#FFFFFF";
	}
} 
</script>


<div class="wrap">
	<?php tl_add_supp( true ); ?>
	<h2>
		<?php echo __( 'wp-greet Security - Setup', 'wp-greet' ); ?>
	</h2>
	<form name="wpgreetsec" method="post" action='#'>
	<table class="optiontable">

	<tr class="tr-admin">
	<th scope="row"><?php echo __( 'Spam protection', 'wp-greet' ); ?>:</th>
	<td><select name="wp-greet-captcha" size="1">
	<option value="0"
		<?php
		if ( $wpg_options['wp-greet-captcha'] == '0' ) {
			echo 'selected="selected"';}
		?>
		>
		<?php echo __( 'none', 'wp-greet' ); ?>
		</option>
		<option value="1"
		<?php
		if ( $wpg_options['wp-greet-captcha'] == '1' ) {
			echo 'selected="selected"';}
		?>
		>
			CaptCha!</option>
		<option value="2"
		<?php
		if ( $wpg_options['wp-greet-captcha'] == '2' ) {
			echo 'selected="selected"';}
		?>
		>
			Math Comment Spam Protection</option>
		<option value="3"
		<?php
		if ( $wpg_options['wp-greet-captcha'] == '3' ) {
			echo 'selected="selected"';}
		?>
		>
			Google Captcha (reCAPTCHA)</option>
	</select>
	</td>
	</tr>

			<tr class="tr-admin">
				<th scope="row"><?php echo __( 'Minimum role to send card', 'wp-greet' ); ?>:
				</th>
				<td><select name="wp-greet-minseclevel" size="1">
						<?php
						$r = '';
						global $wp_roles;
						$roles = $wp_roles->role_names;
						foreach ( $roles as $role => $name ) {
							if ( $wpg_options['wp-greet-minseclevel'] == $role ) {
								$r .= "\n\t<option selected='selected' value='$role'>$name</option>";
							} else {
								$r .= "\n\t<option value='$role'>$name</option>";
							}
						}
						echo $r . "\n";

						?>
						<option value="everyone"
						<?php
						if ( $wpg_options['wp-greet-minseclevel'] == 'everyone' ) {
							echo "selected='selected'";}
						?>
						>
							<?php echo __( 'Everyone', 'wp-greet' ); ?>
						</option>
				</select></td>
			</tr>


	<tr class="tr-admin">
	<th scope="row">&nbsp;</th>
	<td><input type="checkbox" id="wp-greet-touswitch"
				name="wp-greet-touswitch" value="1"
				<?php
				if ( $wpg_options['wp-greet-touswitch'] == '1' ) {
					echo 'checked="checked"';}
				?>
				onclick="wechsle_felder();" /> <b><?php echo __( 'Enable Terms of Usage display and check', 'wp-greet' ); ?>
	</b></td>
	</tr>

	<tr class="tr-admin">
	<th scope="row"><?php echo __( 'Label for Terms of usage', 'wp-greet' ); ?>:</th>
	<td><input id='wp-greet-touswitch-text' name='wp-greet-touswitch-text' value="<?php echo htmlspecialchars( $wpg_options['wp-greet-touswitch-text'], ENT_QUOTES ); ?>" size="45"/><img src="<?php echo site_url( PLUGINDIR . '/wp-greet/tooltip_icon.png' ); ?>"
				alt="tooltip" title='<?php _e( 'HTML is allowed. Leave empty for default.', 'wp-greet' ); ?>' />
	</td>
	</tr>

	<tr class="tr-admin">
	<th scope="row"><?php echo __( 'Terms of usage', 'wp-greet' ); ?>:</th>
	<td><textarea id='wp-greet-termsofusage' name='wp-greet-termsofusage' cols='50' rows='6'><?php echo $wpg_options['wp-greet-termsofusage']; ?></textarea><img src="<?php echo site_url( PLUGINDIR . '/wp-greet/tooltip_icon.png' ); ?>"
				alt="tooltip" title='<?php _e( 'HTML is allowed', 'wp-greet' ); ?>' />
	</td>
			</tr>

			<tr class="tr-admin">
				<th scope="row">&nbsp;</th>
				<td><input type="checkbox" id="wp-greet-mailconfirm"
					name="wp-greet-mailconfirm" value="1"
					<?php
					if ( $wpg_options['wp-greet-mailconfirm'] == '1' ) {
						echo 'checked="checked"';}
					?>
					onclick="wechsle_felder();" /> <b><?php echo __( 'Use mail to verify sender address', 'wp-greet' ); ?>
				</b></td>
			</tr>

	<tr class="tr-admin">
	<th scope="row"><?php _e( 'Verification mail text', 'wp-greet' ); ?>:</th>
	<td><textarea id='wp-greet-mctext' name='wp-greet-mctext' cols='50' rows='6'><?php echo $wpg_options['wp-greet-mctext']; ?></textarea> 
	<img src="<?php echo site_url( PLUGINDIR . '/wp-greet/tooltip_icon.png' ); ?>" alt="tooltip"
					title='<?php _e( 'HTML allowed, use %1$sender% for sendername, %2$sendermail% for sender email-address, %receiver% for receiver name, %link% for generated link, %3$duration% for time the link is valid', 'wp-greet' ); ?>' />
				</td>
			</tr>

			<tr class="tr-admin">
				<th scope="row"><?php echo __( 'Link valid time (hours)', 'wp-greet' ); ?>:</th>
				<td><input id="wp-greet-mcduration" name="wp-greet-mcduration"
					type="text" size="5" maxlength="4"
					value="<?php echo $wpg_options['wp-greet-mcduration']; ?>" /> <img
					src="<?php echo site_url( PLUGINDIR . '/wp-greet/tooltip_icon.png' ); ?>"
					alt="tooltip"
					title='<?php _e( '0 means confirmation-link will never expire', 'wp-greet' ); ?>' />
				</td>
			</tr>

			<tr class="tr-admin">
				<th scope="row"><?php echo __( 'Confirmation mail to sender', 'wp-greet' ) . ':'; ?>
				</th>
				<td><input type="checkbox" id="wp-greet-enable-confirm"
					name="wp-greet-enable-confirm" value="1"
					<?php
					if ( $wpg_options['wp-greet-enable-confirm'] == '1' ) {
						echo 'checked="checked"';}
					?>
					onclick="wechsle_felder();" />
				</td>
			</tr>

	<tr class="tr-admin">
	<th scope="row"><?php _e( 'Confirmation mail text', 'wp-greet' ); ?>:</th>
	<td><textarea id='wp-greet-ectext' name='wp-greet-ectext' cols='50' rows='6'><?php echo $wpg_options['wp-greet-ectext']; ?></textarea> <img
					src="<?php echo site_url( PLUGINDIR . '/wp-greet/tooltip_icon.png' ); ?>"
					alt="tooltip"
					title='<?php _e( 'HTML allowed, use %1$sender% for sendername, %2$sendermail% for sender email-address, %receiver% for receiver name, %link% for generated link, %3$duration% for time the link is valid', 'wp-greet' ); ?>' />
				</td>
			</tr>



			<tr class="tr-admin">
				<th scope="row"><?php echo __( 'Mandatory fields', 'wp-greet' ) . ':'; ?>
				</th>
				<td><b><?php echo __( 'Sendername', 'wp-greet' ); ?>:</b> <input
					type="checkbox" id="wp-greet-field-sendername"
					name="wp-greet-field-sendername" value="1"
					<?php
					if ( substr( $wpg_options['wp-greet-fields'], 0, 1 ) == '1' ) {
						echo 'checked="checked"';}
					?>
					 />
					<b><?php echo __( 'Sender', 'wp-greet' ); ?>:</b> <input type="checkbox"
					id="wp-greet-field-sendermail" name="wp-greet-field-sendermail"
					value="1"
					<?php
					if ( substr( $wpg_options['wp-greet-fields'], 1, 1 ) == '1' ) {
						echo 'checked="checked"';}
					?>
					disabled="disabled" /> <b><?php echo __( 'Recipientname', 'wp-greet' ); ?>:</b>
					<input type="checkbox" id="wp-greet-field-receivername"
					name="wp-greet-field-receivername" value="1"
					<?php
					if ( substr( $wpg_options['wp-greet-fields'], 2, 1 ) == '1' ) {
						echo 'checked="checked"';}
					?>
					 />
					<b><?php echo __( 'Recipient', 'wp-greet' ); ?>:</b> <input
					type="checkbox" id="wp-greet-field-receivermail"
					name="wp-greet-fieldreceivermail" value="1"
					<?php
					if ( substr( $wpg_options['wp-greet-fields'], 3, 1 ) == '1' ) {
						echo 'checked="checked"';}
					?>
					disabled="disabled" /> <b><?php echo __( 'Subject', 'wp-greet' ); ?>:</b>
					<input type="checkbox" id="wp-greet-field-subject"
					name="wp-greet-field-subject" value="1"
					<?php
					if ( substr( $wpg_options['wp-greet-fields'], 4, 1 ) == '1' ) {
						echo 'checked="checked"';}
					?>
					 />
					<b><?php echo __( 'Message', 'wp-greet' ); ?>:</b> <input
					type="checkbox" id="wp-greet-field-message"
					name="wp-greet-field-message" value="1"
					<?php
					if ( substr( $wpg_options['wp-greet-fields'], 5, 1 ) == '1' ) {
						echo 'checked="checked"';}
					?>
					 />
				</td>
			</tr>

		</table>

		<div class='submit'>
			<input type='submit' name='info_update'
				value='<?php _e( 'Update options', 'wp-greet' ); ?> »' />
		</div>
	</form>
	<script type="text/javascript">wechsle_felder();</script>
</div>
	<?php
}
?>
