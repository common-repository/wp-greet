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

function wpcs_wpml_remove_translated_posts( $parr ) {
	global $wpdb;
	$lc = ICL_LANGUAGE_CODE;
	// build where clause
	$pid = '';
	foreach ( $parr as $p ) {
		$pid .= $p->id . ',';
	}
	$pid = substr( $pid, 0, strlen( $pid ) - 1 );

	// build wpml sql
	$sql        = "SELECT b.element_id FROM {$wpdb->prefix}icl_translations as a inner join {$wpdb->prefix}icl_translations as b on a.trid=b.trid and a.element_id in ($pid) and b.language_code <> '$lc'";
	$duplicates = $wpdb->get_results( $sql );
	$duparr     = array();
	foreach ( $duplicates as $d ) {
		$duparr[] = $d->element_id;
	}

	$perg = array();
	foreach ( $parr as $p ) {
		if ( ! in_array( $p->id, $duparr ) ) {
			$perg[] = $p;
		}
	}
	return $perg;
}


//
// form handler for the admin dialog
//
function wpg_admin_form() {
	 global $wpg_options;

	// wp-greet optionen aus datenbank lesen
	$wpg_options = wpgreet_get_options();

	// get translation
	load_plugin_textdomain( 'wp-greet', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

	// load possible form pages
	$wpdb    =& $GLOBALS['wpdb'];
	$sql     = 'SELECT id,post_title FROM ' . $wpdb->prefix . "posts WHERE post_type in ('page','post') and post_content like '%[wp-greet]%' order by id;";
	$pagearr = $wpdb->get_results( $sql );

	// filter translated posts or pages not to be displayed double in selection
	if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
		$pagearr = wpcs_wpml_remove_translated_posts( $pagearr );
	}

	$upflag = false;

	// if this is a POST call, save new values
	if ( isset( $_POST['info_update'] ) ) {
		// save options and put message after update
		echo "<div class='updated'><p><strong>";

		$thispageoptions = array(
			'wp-greet-autofillform',
			'wp-greet-default-title',
			'wp-greet-imagewidth',
			'wp-greet-logging',
			'wp-greet-gallery',
			'wp-greet-formpage',
			'wp-greet-smilies',
			'wp-greet-stampimage',
			'wp-greet-stamppercent',
			'wp-greet-onlinecard',
			'wp-greet-ocduration',
			'wp-greet-octext',
			'wp-greet-logdays',
			'wp-greet-carddays',
			'wp-greet-show-ngg-desc',
			'wp-greet-future-send',
			'wp-greet-multi-recipients',
			'wp-greet-tinymce',
			'wp-greet-offerresend',
			'wp-greet-external-link',
			'wp-greet-disable-css',
			'wp-greet-use-wpml-lang',
			'wp-greet-sent-message',
			'wp-greet-confirm-sent-message',
			'wp-greet-audio',
			'wp-greet-offer-stamps',
			'wp-greet-offer-stamps-buddypress',
			'wp-greet-offer-stamps-buddypress-adminonly',
			'wp-greet-multi-recipients-bcc',
		);

		foreach ( $wpg_options as $key => $value ) {
			// for empty checkboxes
			if ( ! isset( $_POST[ $key ] ) ) {
				$_POST[ $key ] = 0;}
			// save options if applicable
			if ( in_array( $key, $thispageoptions ) and
			$wpg_options[ $key ] != $_POST[ $key ] ) {
				$wpg_options[ $key ] = stripslashes( $_POST[ $key ] );
				$upflag              = true;
			}
		}

		// check email adresses
		if ( $wpg_options['wp-greet-imagewidth'] < 0 or $wpg_options['wp-greet-imagewidth'] > 3200 ) {
			echo __( 'imagewidth not in range (0..3200).', 'wp-greet' ) . '<br />';
			$upflag = false;
		}

		if ( $wpg_options['wp-greet-onlinecard'] == 1 and $wpg_options['wp-greet-ocduration'] > $wpg_options['wp-greet-carddays'] ) {
			echo __( 'Cards will be removed before fetch interval expires (Number of days an online card can be fetched > Number of days card entries are stored)', 'wp-greet' ) . '<br />';
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
function wechsle_stamp () {
	imgb=document.getElementById('wp-greet-onlinecard');
	stamp=document.getElementById('wp-greet-stampimage');
	stampwidth=document.getElementById('wp-greet-stamppercent');
	a=document.getElementById('wp-greet-offer-stamps');
	b=document.getElementById('wp-greet-offer-stamps-buddypress');
	c=document.getElementById('wp-greet-offer-stamps-buddypress-adminonly');
	
	audio=document.getElementById('wp-greet-audio');
	stamp.readOnly = ((imgb.checked == false));
	stampwidth.readOnly = ((imgb.checked == false));
	audio.disabled = ((imgb.checked == false));
	a.readOnly = ((imgb.checked == false));
	b.readOnly = ((imgb.checked == false));
	c.readOnly = ((imgb.checked == false));
}

function wechsle_onlinecard () {
	obja=document.getElementById('wp-greet-onlinecard');
	objb=document.getElementById('wp-greet-ocduration');
	objc=document.getElementById('wp-greet-octext');
	objb.readOnly = (obja.checked == false);
	objc.readOnly = (obja.checked == false);
	wechsle_stamp();
}

function wechsle_galerie () {
	obja=document.getElementById('wp-greet-gallery');
	objb=document.getElementById('wp-greet-show-ngg-desc');
	objc=document.getElementById('wp-greet-external-link');
	objb.readOnly = (obja.value == 'wp' || obja.value == '-');
	objc.readOnly = (obja.value != 'wp');
}

function wechsle_logging () {
	obja=document.getElementById('wp-greet-logging');
	objb=document.getElementById('wp-greet-logdays');
	objc=document.getElementById('wp-greet-carddays');
	objb.readOnly = (obja.checked == false);
	objc.readOnly = (obja.checked == false);
} 

function wechsle_multiuser () {
	obja=document.getElementById('wp-greet-multi-recipients');
	objb=document.getElementById('wp-greet-multi-recipients-bcc');
	if (obja.checked == false) {
		objb.checked  = false;
		objb.disabled = true;
	} else {
		objb.disabled = false;
	}    
}

</script>
<div class="wrap">
	<?php tl_add_supp( true ); ?>
	<h2><?php echo __( 'wp-greet Setup', 'wp-greet' ); ?></h2>
   
	<div style="text-align:right;padding-bottom:10px;">
	  <a class="thickbox button-secondary" href="../wp-content/plugins/wp-greet/wpg-admin-reschedule.php?height=350&amp;width=550&amp;fn=match" ><?php _e( 'Reschedule future cards', 'wp-greet' ); ?></a>&nbsp;&nbsp;&nbsp;
	</div>

   <form name="wpgreetadmin" method="post" action='#'>
   <table class="optiontable">
		  <tr class="tr-admin">
		  <th scope="row"><?php echo __( 'Gallery-Plugin', 'wp-greet' ); ?>:</th>
		  <td><select id="wp-greet-gallery" name="wp-greet-gallery" size="1" onchange="wechsle_galerie();">
		  <option value="-" 
		  <?php
			if ( $wpg_options['wp-greet-gallery'] == '-' ) {
				echo "selected='selected'";}
			?>
			><?php _e( 'none', 'wp-greet' ); ?></option>
		  <option value="ngg" 
		  <?php
			if ( $wpg_options['wp-greet-gallery'] == 'ngg' ) {
				echo "selected='selected'";}
			?>
			>Nextgen/NextCellent Gallery</option>
		  <option value="wp" 
		  <?php
			if ( $wpg_options['wp-greet-gallery'] == 'wp' ) {
				echo "selected='selected'";}
			?>
			>WordPress</option>
		  </select> 
		  </td>
		  </tr>

		   <tr class="tr-admin">
		   <th scope="row">&nbsp;</th>
		   <td><input type="checkbox" id="wp-greet-external-link" name="wp-greet-external-link" value="1" 
		   <?php
			if ( $wpg_options['wp-greet-external-link'] == '1' ) {
				echo 'checked="checked"';}
			?>
			 /> <b><?php echo __( 'Use external link from WordPress media', 'wp-greet' ); ?></b></td>
		   </tr>
		   
		   <tr class="tr-admin">
		   <th scope="row">&nbsp;</th>
		   <td><input type="checkbox" id="wp-greet-show-ngg-desc" name="wp-greet-show-ngg-desc" value="1" 
		   <?php
			if ( $wpg_options['wp-greet-show-ngg-desc'] == '1' ) {
				echo 'checked="checked"';}
			?>
			 /> <b><?php echo __( 'Use NGG/NCG data for image description', 'wp-greet' ); ?></b></td>
		   </tr>   
		   <tr class="tr-admin"><td>&nbsp;</td></tr>
		
		   
		  <tr class="tr-admin">
		  <th scope="row"><?php echo __( 'Form-Post/Page', 'wp-greet' ); ?>:</th>
		  <td><select name="wp-greet-formpage" size="1">
	<?php
	$r = '';
	$o = '';
	foreach ( $pagearr as $p ) {
		if ( $wpg_options['wp-greet-formpage'] == $p->id ) {
			$o = "\n\t<option selected='selected' value='" . $p->id . "'>" . $p->post_title . '</option>';
		} else {
			$r .= "\n\t<option value='" . $p->id . "'>" . $p->post_title . '</option>';
		}
	}
	echo $o . $r . "\n";
	?>
		  </select></td></tr>
		
		  <tr class="tr-admin">
		 <th scope="row"><?php echo __( 'Fixed image width', 'wp-greet' ); ?>:</th>
		 <td><input name="wp-greet-imagewidth" type="text" size="10" maxlength="5" value="<?php echo $wpg_options['wp-greet-imagewidth']; ?>" /></td>
		 </tr>
		  
		  <tr class="tr-admin">
		 <th scope="row">&nbsp;</th>
		 <td><input type="checkbox" name="wp-greet-autofillform" value="1" 
		 <?php
			if ( $wpg_options['wp-greet-autofillform'] == '1' ) {
				echo 'checked="checked"';}
			?>
			 /> <b><?php echo __( 'Use informations from profile', 'wp-greet' ); ?></b></td>
		 </tr>
		  
		  
		 <tr class="tr-admin">
		 <th scope="row">&nbsp;</th>
		 <td><input type="checkbox" name="wp-greet-multi-recipients" id="wp-greet-multi-recipients" value="1" 
		 <?php
			if ( $wpg_options['wp-greet-multi-recipients'] == '1' ) {
				echo 'checked="checked"';}
			?>
			 onclick="wechsle_multiuser();" /> <b><?php echo __( 'Allow more than one recipient', 'wp-greet' ); ?></b></td>
		 </tr>
		   
		 <tr class="tr-admin">
		 <th scope="row">&nbsp;</th>
		 <td><input type="checkbox" name="wp-greet-multi-recipients-bcc" id="wp-greet-multi-recipients-bcc" value="1" 
		 <?php
			if ( $wpg_options['wp-greet-multi-recipients-bcc'] == '1' ) {
				echo 'checked="checked"';}
			?>
			 /> <b><?php echo __( 'Allow Bcc for multiple recipients', 'wp-greet' ); ?></b></td>
		 </tr>
		 
		 <tr class="tr-admin">
		 <th scope="row">&nbsp;</th>
		 <td><input type="checkbox" name="wp-greet-future-send" value="1" 
		 <?php
			if ( $wpg_options['wp-greet-future-send'] == '1' ) {
				echo 'checked="checked"';}
			?>
			 /> <b><?php echo __( 'Allow sending cards in the future', 'wp-greet' ); ?></b></td>
		 </tr>
			 
		 <tr class="tr-admin">
		 <th scope="row">&nbsp;</th>
		 <td><input type="checkbox" name="wp-greet-tinymce" value="1" 
		 <?php
			if ( $wpg_options['wp-greet-tinymce'] == '1' ) {
				echo 'checked="checked"';}
			?>
			 /> <b><?php echo __( 'Use TinyMCE editor', 'wp-greet' ); ?></b></td>
		 </tr>
			   
		 <tr class="tr-admin">
		 <th scope="row">&nbsp;</th>
		 <td><input type="checkbox" name="wp-greet-smilies" value="1" 
		 <?php
			if ( $wpg_options['wp-greet-smilies'] == '1' ) {
				echo 'checked="checked"';}
			?>
			 /> <b><?php echo __( 'Enable Smileys on greetcard form', 'wp-greet' ); ?></b></td>
		 </tr>
	   
		  <tr class="tr-admin">
		 <th scope="row"><?php echo __( 'Default mail subject', 'wp-greet' ); ?>:</th>
		 <td><input name="wp-greet-default-title" type="text" size="30" maxlength="80" value="<?php echo $wpg_options['wp-greet-default-title']; ?>" /></td>   
		 </tr>
		 <tr class="tr-admin"><td>&nbsp;</td></tr>
		  
		  <tr class="tr-admin">
		 <th scope="row">&nbsp;</th>
		 <td><input type="checkbox" name="wp-greet-offerresend" value="1" 
		 <?php
			if ( $wpg_options['wp-greet-offerresend'] == '1' ) {
				echo 'checked="checked"';}
			?>
			 /> <b><?php echo __( 'Offer \'send again\'-link ', 'wp-greet' ); ?></b></td>
		 </tr>
		
	<?php
																 /*
		 <tr class="tr-admin">
		 <th scope="row">&nbsp;</th>
		 <td><input type="checkbox" name="wp-greet-use-wpml-lang" value="1" <?php if ($wpg_options['wp-greet-use-wpml-lang']=="1") echo "checked=\"checked\""?> /> <b><?php echo __('Show form in gallery language (WPML only)',"wp-greet")?></b></td>
		 </tr>
																 */
	?>
		  
   
		<tr class="tr-admin">
		<th scope="row"><?php echo __( 'Message to display when a card has been sent or scheduled', 'wp-greet' ); ?>:</th>
		<td><textarea name='wp-greet-sent-message' cols='50'rows='4'><?php echo $wpg_options['wp-greet-sent-message']; ?></textarea>
		 <img src="<?php echo site_url( PLUGINDIR . '/wp-greet/tooltip_icon.png' ); ?>" alt="tooltip" title='<?php _e( 'HTML allowed, use %1$sender% for sendername, %2$sendermail% for sender email-address, %receiver% for receiver name', 'wp-greet' ); ?>'/>
		</td>
		</tr>
		  
		<tr class="tr-admin">
		<th scope="row"><?php echo __( 'Message to display when a confirmation mail has been sent', 'wp-greet' ); ?>:</th>
		<td><textarea name='wp-greet-confirm-sent-message' cols='50'rows='4'><?php echo $wpg_options['wp-greet-confirm-sent-message']; ?></textarea>
		 <img src="<?php echo site_url( PLUGINDIR . '/wp-greet/tooltip_icon.png' ); ?>" alt="tooltip" title='<?php _e( 'HTML allowed, use %1$sender% for sendername, %2$sendermail% for sender email-address, %receiver% for receiver name', 'wp-greet' ); ?>'/>
		</td>
		</tr>
		  
		  <tr class="tr-admin"><td>&nbsp;</td></tr>
		 
		  
		  <tr class="tr-admin">
		  <th scope="row">&nbsp;</th>
		  <td>
		  <input type="checkbox" name="wp-greet-onlinecard" id="wp-greet-onlinecard" value="1" 
		  <?php
			if ( $wpg_options['wp-greet-onlinecard'] == '1' ) {
				echo 'checked="checked" ';}
			?>
			  onclick="wechsle_onlinecard();"  /> <b><?php echo __( 'Fetch cards online', 'wp-greet' ); ?></b></td>
		  </tr>

		  <tr class="tr-admin">
		  <th scope="row"><?php echo __( 'Number of days an online card can be fetched', 'wp-greet' ); ?>:</th>
		  <td><input id="wp-greet-ocduration" name="wp-greet-ocduration" type="text" size="10" maxlength="5" value="<?php echo $wpg_options['wp-greet-ocduration']; ?>" /></td>
		  </tr>

		  <tr class="tr-admin">
		  <th scope="row"><?php echo __( 'Online card HTML mail text', 'wp-greet' ); ?>:
		  <br />
		  </th>
		  <td><textarea id='wp-greet-octext' name='wp-greet-octext' cols='50'rows='4'><?php echo $wpg_options['wp-greet-octext']; ?></textarea>
		  <img src="<?php echo site_url( PLUGINDIR . '/wp-greet/tooltip_icon.png' ); ?>" alt="tooltip" title='<?php _e( 'HTML allowed, use %1$sender% for sendername, %2$sendermail% for sender email-address, %receiver% for receiver name, %link% for generated link, %3$duration% for time the link is valid', 'wp-greet' ); ?>'/>
		  </td>
		  </tr>
		 
		  <tr class="tr-admin">
		  <th scope="row">&nbsp;</th>
		  <td><input type="checkbox" name="wp-greet-offer-stamps"  id="wp-greet-offer-stamps" value="1" 
		  <?php
			if ( $wpg_options['wp-greet-offer-stamps'] == '1' ) {
				echo 'checked="checked"';}
			?>
			 /> <b><?php echo __( 'offer different stamps from the stamps directory', 'wp-greet' ); ?></b></td>
		  </tr>
		  
		  <tr class="tr-admin">
		  <th scope="row">&nbsp;</th>
		  <td><input type="checkbox" name="wp-greet-offer-stamps-buddypress"  id="wp-greet-offer-stamps-buddypress" value="1" 
		  <?php
			if ( $wpg_options['wp-greet-offer-stamps-buddypress'] == '1' ) {
				echo 'checked="checked"';}
			?>
			 /> <b><?php echo __( 'offer BuddyPress group logo as stamp', 'wp-greet' ); ?></b></td>
		  <td><input type="checkbox" name="wp-greet-offer-stamps-buddypress-adminonly"  id="wp-greet-offer-stamps-buddypress-adminonly" value="1" 
		  <?php
			if ( $wpg_options['wp-greet-offer-stamps-buddypress-adminonly'] == '1' ) {
				echo 'checked="checked"';}
			?>
			 /> <b><?php echo __( 'for BuddyPress group admin only', 'wp-greet' ); ?></b></td>
		  </tr>
			
		  <tr class="tr-admin">
		  <th scope="row"><?php echo __( 'Add stamp image', 'wp-greet' ); ?>:</th>
		  <td><select name="wp-greet-stampimage" size="1" id="wp-greet-stampimage">
	<?php
		$stampdir   = plugin_dir_path( __FILE__ ) . 'stamps';
		$stampfiles = scandir( $stampdir );

	foreach ( $stampfiles as $i ) {
		$ext = substr( $i, strlen( $i ) - 4 );
		if ( '.jpg' == $ext or '.png' == $ext ) {
			$url = plugin_dir_url( __FILE__ ) . 'stamps/' . $i;

			$stamp_selected = '';
			if ( $wpg_options['wp-greet-stampimage'] == $url ) {
				$stamp_selected = 'selected="selected"';
			}

			echo '<option value ="' . $url . "\" $stamp_selected>" . substr( $i, 0, strlen( $i ) - 4 ) . "</option>\n";
		}
	}
	?>
		  </select>          
		  </td></tr>

		  <tr class="tr-admin">
		  <th scope="row"><?php echo __( 'Stampwidth in % of imagewidth', 'wp-greet' ); ?>:</th>
		  <td><input name="wp-greet-stamppercent" id="wp-greet-stamppercent" type="text" size="5" maxlength="3" value="<?php echo $wpg_options['wp-greet-stamppercent']; ?>" /></td>
		  </tr>           

		  <tr class="tr-admin">
		  <th scope="row">&nbsp;</th>
		  <td><input type="checkbox" name="wp-greet-audio"  id="wp-greet-audio" value="1" 
		  <?php
			if ( $wpg_options['wp-greet-audio'] == '1' ) {
				echo 'checked="checked"';}
			?>
			 /> <b><?php echo __( 'enable background music for cards', 'wp-greet' ); ?></b></td>
		  </tr>
		  
		  <tr class="tr-admin"><td>&nbsp;</td></tr>

		  
		  <tr class="tr-admin"> 
		  <th scope="row">&nbsp;</th>
		  <td><input type="checkbox" name="wp-greet-logging"  id="wp-greet-logging" value="1" 
		  <?php
			if ( $wpg_options['wp-greet-logging'] == '1' ) {
				echo 'checked="checked"';}
			?>
			 onclick="wechsle_logging();"/> <b><?php echo __( 'enable logging', 'wp-greet' ); ?></b></td>
		  </tr>
		 
		  <tr class="tr-admin">
		  <th scope="row"><?php echo __( 'Number of days log entries are stored', 'wp-greet' ); ?>:</th>
		  <td><input id="wp-greet-logdays" name="wp-greet-logdays" type="text" size="10" maxlength="5" value="<?php echo $wpg_options['wp-greet-logdays']; ?>" /></td>
		  </tr>

		  <tr class="tr-admin">
		  <th scope="row"><?php echo __( 'Number of days card entries are stored', 'wp-greet' ); ?>:</th>
		  <td><input id="wp-greet-carddays" name="wp-greet-carddays" type="text" size="10" maxlength="5" value="<?php echo $wpg_options['wp-greet-carddays']; ?>" /></td>
		  </tr>

		 <tr class="tr-admin"> 
		 <th scope="row">&nbsp;</th>
		 <td><input type="checkbox" name="wp-greet-disable-css" value="1" 
		 <?php
			if ( $wpg_options['wp-greet-disable-css'] == '1' ) {
				echo 'checked="checked"';}
			?>
			 /> <b><?php echo __( 'disable wp-greet css rules', 'wp-greet' ); ?></b></td>
		 </tr>

  </table>
   <div class='submit'>
	  <input type='submit' name='info_update' value='<?php _e( 'Update options', 'wp-greet' ); ?> »' />
   </div>
   </form>
   <script type="text/javascript">wechsle_galerie(); wechsle_onlinecard();wechsle_stamp();wechsle_logging();wechsle_multiuser();</script></div>
	<?php
}
?>
