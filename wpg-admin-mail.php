<?php
/* This file is part of the wp-greet plugin for WordPress */

/*
  Copyright 2008-2016 Hans Matzen  (email : webmaster at tuxlog dot de)

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
function wpg_admin_mail() {
	 global $wpg_options;

	// wp-greet optionen aus datenbank lesen
	$wpg_options = wpgreet_get_options();

	// get translation
	load_plugin_textdomain( 'wp-greet', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	// if this is a POST call, save new values
	if ( isset( $_POST['info_update'] ) ) {
		$upflag = false;

		reset( $wpg_options );
		$thispageoptions = array(
			'wp-greet-usesmtp',
			'wp-greet-smtp-host',
			'wp-greet-smtp-port',
			'wp-greet-smtp-ssl',
			'wp-greet-smtp-user',
			'wp-greet-smtp-pass',
			'wp-greet-staticsender',
			'wp-greet-mail-from',
			'wp-greet-mail-fromname',
			'wp-greet-mail-replyto',
			'wp-greet-bcc',
			'wp-greet-mailreturnpath',
			'wp-greet-default-title',
			'wp-greet-default-header',
			'wp-greet-default-footer',
			'wp-greet-imgattach',
		);

		$allowed_tags = wp_kses_allowed_html( 'post' );

		foreach ( $wpg_options as $key => $value ) {
			// for empty checkboxes
			if ( ! isset( $_POST[ $key ] ) ) {
				$_POST[ $key ] = 0;
			}

			// save options if applicable
			if ( in_array( $key, $thispageoptions ) and $wpg_options[ $key ] != $_POST[ $key ] ) {
				// for validating textareas
				if ( $key == 'wp-greet-default-header' or $key == 'wp-greet-default-footer' ) {
					$wpg_options[ $key ] = wp_kses( wpautop( stripslashes( $_POST[ $key ] ) ), $allowed_tags );
					$upflag              = true;
				} else {
					$wpg_options[ $key ] = esc_attr( $_POST[ $key ] );
					$upflag              = true;
				}
			}
		}

		// save options and put message after update
		echo "<div class='updated'><p><strong>";

		// check email adresses
		if ( ! wpg_check_email( $wpg_options['wp-greet-mailreturnpath'] ) and $wpg_options['wp-greet-mailreturnpath'] != '' ) {
			echo __( 'Mailreturnpath is not valid (wrong format or no MX entry for domain).', 'wp-greet' ) . '<br />';
			$upflag = false;
		}

		if ( ! wpg_check_email( $wpg_options['wp-greet-staticsender'] ) and $wpg_options['wp-greet-staticsender'] != '' ) {
			echo __( 'Static Sender email address is not valid (wrong format or no MX entry for domain).', 'wp-greet' ) . '<br />';
			$upflag = false;
		}

		if ( ! wpg_check_email( $wpg_options['wp-greet-bcc'] ) and $wpg_options['wp-greet-bcc'] != '' ) {
			echo __( 'BCC (blind copy) email address is not valid (wrong format or no MX entry for domain).', 'wp-greet' ) . '<br />';
			$upflag = false;
		}

		if ( ! wpg_check_email( $wpg_options['wp-greet-mail-from'] ) and $wpg_options['wp-greet-mail-from'] != '' ) {
			echo __( 'Static From-Address email address is not valid (wrong format or no MX entry for domain).', 'wp-greet' ) . '<br />';
			$upflag = false;
		}

		if ( ! wpg_check_email( $wpg_options['wp-greet-mail-replyto'] ) and $wpg_options['wp-greet-mail-replyto'] != '' ) {
			echo __( 'Reply-To email address is not valid (wrong format or no MX entry for domain).', 'wp-greet' ) . '<br />';
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
function wechsle_inline () {
	imga=document.getElementById('wp-greet-imgattach');
	usmtp=document.getElementById('wp-greet-usesmtp1');
	if (usmtp.checked == false) {
	  imga.checked = false;
	  imga.disabled = true;
	} else
	  imga.disabled=false;
} 

function wechsle_smtp () {
	usmtp=document.getElementById('wp-greet-usesmtp1'); 
	obja=document.getElementById('wp-greet-smtp-host');
	objb=document.getElementById('wp-greet-smtp-port');
	objc=document.getElementById('wp-greet-smtp-ssl');
	objd=document.getElementById('wp-greet-smtp-user');
	obje=document.getElementById('wp-greet-smtp-pass');
		 
	obja.readOnly = (usmtp.checked == false);
	objb.readOnly = (usmtp.checked == false);
	objc.disabled = (usmtp.checked == false);
	objd.readOnly = (usmtp.checked == false);
	obje.readOnly = (usmtp.checked == false);
	wechsle_inline();
} 
</script>
<div class="wrap">
	<?php tl_add_supp( true ); ?>
	<h2><?php echo __( 'wp-greet Mail-Settings', 'wp-greet' ); ?></h2>
   

   <form name="wpgreetadmin" method="post" action='#'>
   <table class="optiontable">
   
	  <tr><th scope="row"><?php echo __( 'Mailtransfermethod', 'wp-greet' ); ?>:</th>
		<td>
		  <input type="radio" name="wp-greet-usesmtp" id="wp-greet-usesmtp1" value="1" 
		  <?php
			if ( $wpg_options['wp-greet-usesmtp'] == '1' ) {
				echo 'checked="checked" ';}
			?>
			 onclick="wechsle_smtp();"  />SMTP (class-phpmailer.php)
		  <input type="radio" name="wp-greet-usesmtp" id="wp-greet-usesmtp2" value="0" 
		  <?php
			if ( $wpg_options['wp-greet-usesmtp'] == '0' ) {
				echo 'checked="checked" ';}
			?>
			 onclick="wechsle_smtp();" /> PHP mail() function  
		</td>
	  </tr>
	
	  <tr class="tr-admin"><th scope="row"><?php echo __( 'SMTP Server (hostname)', 'wp-greet' ); ?>:</th>
		  <td><input id="wp-greet-smtp-host" name="wp-greet-smtp-host" type="text" size="30" maxlength="80" value="<?php echo $wpg_options['wp-greet-smtp-host']; ?>" /></td>
	  </tr> 
	  <tr class="tr-admin">
		<th scope="row"><?php echo __( 'SMTP Port (default:25)', 'wp-greet' ); ?>:</th>
		<td><input id="wp-greet-smtp-port" name="wp-greet-smtp-port" type="text" size="10" maxlength="5" value="<?php echo $wpg_options['wp-greet-smtp-port']; ?>" /></td>
	  </tr> 
	   
	  <tr class="tr-admin">
		<th scope="row">&nbsp;</th>
		<td><input type="checkbox" id="wp-greet-smtp-ssl" name="wp-greet-smtp-ssl" value="1" 
		<?php
		if ( $wpg_options['wp-greet-smtp-ssl'] == '1' ) {
			echo 'checked="checked"';}
		?>
		 /> <b><?php echo __( 'SMTP use SSL?', 'wp-greet' ); ?></b></td>
	  </tr>

	  <tr class="tr-admin">
		<th scope="row"><?php echo __( 'SMTP Username', 'wp-greet' ); ?>:</th>
		<td><input id="wp-greet-smtp-user" name="wp-greet-smtp-user" type="text" size="30" maxlength="80" value="<?php echo $wpg_options['wp-greet-smtp-user']; ?>" /></td>
	  </tr> 

	  <tr class="tr-admin">
		<th scope="row"><?php echo __( 'SMTP Password', 'wp-greet' ); ?>:</th>
		<td><input id="wp-greet-smtp-pass" name="wp-greet-smtp-pass" type="password" size="30" maxlength="80" value="<?php echo $wpg_options['wp-greet-smtp-pass']; ?>" /></td>
	  </tr>

	  <tr class="tr-admin"><td>&nbsp;</td></tr>
		
	  <tr class="tr-admin">
	  <th scope="row"><?php echo __( 'Send mage inline', 'wp-greet' ); ?>:</th>
	  <td><input type="checkbox" name="wp-greet-imgattach" id="wp-greet-imgattach" value="1" 
	  <?php
		if ( $wpg_options['wp-greet-imgattach'] == '1' ) {
			echo 'checked="checked" ';}
		?>
		 />&nbsp;</td>
	  </tr>
	  <tr class="tr-admin"><td>&nbsp;</td></tr>
		  
	  <tr class="tr-admin">
		<th scope="row"><?php echo __( 'Static Sender-Address', 'wp-greet' ); ?>:</th>
		<td><input name="wp-greet-staticsender" type="text" size="30" maxlength="80" value="<?php echo $wpg_options['wp-greet-staticsender']; ?>" />
		<img src="<?php echo site_url( PLUGINDIR . '/wp-greet/tooltip_icon.png' ); ?>" alt="tooltip" title='<?php _e( 'Will be sent as MAIL FROM, instead of the Sender-EMail from the form.', 'wp-greet' ); ?>'/>
		</td>
	  </tr>
	  
	  <tr class="tr-admin">
		<th scope="row"><?php echo __( 'Static From-Address', 'wp-greet' ); ?>:</th>
		<td><input name="wp-greet-mail-from" type="text" size="30" maxlength="80" value="<?php echo $wpg_options['wp-greet-mail-from']; ?>" />
		<img src="<?php echo site_url( PLUGINDIR . '/wp-greet/tooltip_icon.png' ); ?>" alt="tooltip" title='<?php _e( 'Will be used as From-Address, instead of the Sender-EMail from the form.', 'wp-greet' ); ?>'/>
		</td>
	  </tr>
	  
	  <tr class="tr-admin">
		<th scope="row"><?php echo __( 'Static From-Name', 'wp-greet' ); ?>:</th>
		<td><input name="wp-greet-mail-fromname" type="text" size="30" maxlength="80" value="<?php echo $wpg_options['wp-greet-mail-fromname']; ?>" />
		<img src="<?php echo site_url( PLUGINDIR . '/wp-greet/tooltip_icon.png' ); ?>" alt="tooltip" title='<?php _e( 'Will be used as From-Name, instead of the Sender-Name from the form.', 'wp-greet' ); ?>'/>
		</td>
	  </tr>

	  <tr class="tr-admin">
		<th scope="row"><?php echo __( 'Send Bcc to', 'wp-greet' ); ?>:</th>
		<td><input name="wp-greet-bcc" type="text" size="30" maxlength="80" value="<?php echo $wpg_options['wp-greet-bcc']; ?>" />
		<img src="<?php echo site_url( PLUGINDIR . '/wp-greet/tooltip_icon.png' ); ?>" alt="tooltip" title='<?php _e( 'Mails will be sent to this address as blind copy.', 'wp-greet' ); ?>'/>
		</td>   
	  </tr>
	  
	  <tr class="tr-admin">
		<th scope="row"><?php echo __( 'Static Reply-To-Address', 'wp-greet' ); ?>:</th>
		<td><input name="wp-greet-mail-replyto" type="text" size="30" maxlength="80" value="<?php echo $wpg_options['wp-greet-mail-replyto']; ?>" />
		<img src="<?php echo site_url( PLUGINDIR . '/wp-greet/tooltip_icon.png' ); ?>" alt="tooltip" title='<?php _e( 'Will be used as Reply-To-Address, instead of the Sender-EMail from the form.', 'wp-greet' ); ?>'/>
		</td>
	  </tr>
		  
	  <tr class="tr-admin">
		<th scope="row"><?php echo __( 'Mailreturnpath', 'wp-greet' ); ?>:</th>
		<td><input name="wp-greet-mailreturnpath" type="text" size="30" maxlength="80" value="<?php echo $wpg_options['wp-greet-mailreturnpath']; ?>" />
		<img src="<?php echo site_url( PLUGINDIR . '/wp-greet/tooltip_icon.png' ); ?>" alt="tooltip" title='<?php _e( 'If set will add an extra mail header setting the return-path (deprecated in PHPMailer class).', 'wp-greet' ); ?>'/>
		</td>
	  </tr>

	  <tr class="tr-admin"><td>&nbsp;</td></tr>

	  <tr class="tr-admin">
		<th scope="row"><?php echo __( 'Default mail header', 'wp-greet' ); ?>:</th>
		<td><textarea name='wp-greet-default-header' cols='50'rows='4'><?php echo $wpg_options['wp-greet-default-header']; ?></textarea>
		<img src="<?php echo site_url( PLUGINDIR . '/wp-greet/tooltip_icon.png' ); ?>" alt="tooltip" title='<?php _e( 'HTML allowed, use %1$sender% for sendername, %2$sendermail% for sender email-address, %receiver% for receiver name, %link% for generated link, %3$duration% for time the link is valid', 'wp-greet' ); ?>'/>
		</td>
	  </tr>

	  <tr class="tr-admin">
		<th scope="row"><?php echo __( 'Default mail footer', 'wp-greet' ); ?>:</th>
		<td><textarea name='wp-greet-default-footer' cols='50'rows='4'><?php echo $wpg_options['wp-greet-default-footer']; ?></textarea>
		 <img src="<?php echo site_url( PLUGINDIR . '/wp-greet/tooltip_icon.png' ); ?>" alt="tooltip" title='<?php _e( 'HTML allowed, use %1$sender% for sendername, %2$sendermail% for sender email-address, %receiver% for receiver name, %link% for generated link, %3$duration% for time the link is valid', 'wp-greet' ); ?>'/>
		 </td>
	  </tr>
   

  </table>
   <div class='submit'>
	  <input type='submit' name='info_update' value='<?php _e( 'Update options', 'wp-greet' ); ?> »' />
   </div>
   </form>
   <script type="text/javascript">wechsle_inline();wechsle_smtp();</script></div>
	<?php
}
?>
