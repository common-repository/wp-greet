<?php
/*
 *  Template for the form of a wp-greet card
 *  
 *  The following placeholders can be used:
 *  
 *  All placeholders ending in _label contain the default wp-greet labels for this field
 *  All placeholders ending in _input contain the input tag for this field
 *  
 *  {%sendername_label%}	-	name of the sender	
 *  {%sendername_input%}
 *  {%sendermail_label%}	- 	email address of the sender
 *  {%sendermail_input%}
 *  {%ccsender_label%}		- 	flag if the sender should get a copy of the greetcard mail
 *  {%ccsender_input%}
 *  {%confirm_label%}		-	flag if the sender should get a confirmation mail when the card is fetched by the receiver
 *  {%confirm_input%}
 *  {%recvname_label%}		- 	name of the receiver of the card
 *  {%recvname_input%}
 *  {%recvmail_label%}		-  	email address of the receiver of the card
 *  {%recvmail_input%}
 *  {%subject_label%}		-	subject of the card
 *  {%subject_input%}
 *  {%image_url%}			-	returns an img tag to show the greetcard  picture
 *  {%message_label%}		-	message of the card
 *  {%message_input%}
 *  {%futuresend_label%}	-	the time the card should be send
 *  {%futuresend_input%}
 *  {%smilies_label%}		-	smilies to show insert into the card message
 *  {%smilies_input%}
 *  {%captcha_label%}		-	if a spamprotection is used you can show it with these placeholders
 *  {%captcha_input%}
 *  {%tou_label%}			-	terms of usage
 *  {%tou_input%}
 *  {%audio_label%}			-  	background music selector
 *  {%audio_input%}
 *  {%autotext_label%}	 	- 	if you use the autotext extension you can add autotext to form using this
 *  {%autotext_input%}
 *  {%offerstamp_label%}	- offer different stamps from the stmaps directory to selet from
 *  {%offerstamp_input%}
 *  {%bbgroup_receiver_label%} - let BuddyPress group admins select a whole group a receiver
 *  {%bbgroup_receiver_input%}
 *  
 * The template will be placed inside:
 *  <div class='wp-greet-form'>
 *    <form method='post' action='#'>
 *      <Here goes the template>
 *    </form>
 *  </div>";
 *  	
 */ 
// include this for is_plugin_active
include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
?>

<br/>
{%image_url%}
<br />
<?php if ($wpg_options['wp-greet-audio']): 
         $Noneurl=plugin_dir_url(__FILE__). "../music/None.mp3";
?> 
<script type="text/javascript">
	function wpg_switch_background_music() {
		song=document.getElementById('wp-greet-audio').value;
		player=document.getElementById('wpg_audio_player');
		fbplayer=document.getElementById('wpg_audio_player_fallback');
		player.src=song;
		fbplayer.src=song;
}
jQuery(document).ready(function() {
	song=document.getElementById('wp-greet-audio').value;
	player=document.getElementById('wpg_audio_player');
	fbplayer=document.getElementById('wpg_audio_player_fallback');
	player.src=song;
	fbplayer.src=song;
});
</script>	

<audio id="wpg_audio_player" autoplay="true" loop="true">
 <source src="<?php echo $Noneurl; ?>" />
 <!-- fallback -->
 <embed id="wpg_audio_player_fallback" type="audio/mpeg" src="<?php echo $Noneurl; ?>" autostart="true" loop="true">
</audio>
<?php endif; ?>		

<table class="wp-greet-form">
  <tr class="wp-greet-form">
    <td class="wp-greet-form-left" colspan="2"><?php _e("Mandatory inputfields are marked with a","wp-greet")?><strong>*</strong><br/>&nbsp;</td>
  </tr>
  
<?php if ($wpg_options['wp-greet-offer-stamps']): ?> 
    <tr class="wp-greet-form">
    <td class="wp-greet-form-left">{%offerstamp_label%}:</td>
    <td class="wp-greet-form">{%offerstamp_input%}</td>
    </tr>
<?php endif; ?>

  <tr class="wp-greet-form">
    <td class="wp-greet-form-left">{%sendername_label%}:</td>
    <td class="wp-greet-form">{%sendername_input%}</td>
  </tr>

  <tr class="wp-greet-form">
    <td class="wp-greet-form-left">{%sendermail_label%}:</td>
    <td class="wp-greet-form">{%sendermail_input%}</td>
  </tr>

<?php if ($wpg_options['wp-greet-enable-confirm']): ?>
  <tr class="wp-greet-form">
    <td class="wp-greet-form-left">{%confirm_label%}:</td>
    <td class="wp-greet-form">{%confirm_input%}</td>
  </tr>
<?php endif;?>

  <tr class="wp-greet-form">
    <td class="wp-greet-form-left">{%ccsender_label%}:</td>
    <td class="wp-greet-form">{%ccsender_input%}</td>
  </tr>
  
<?php if ($wpg_options['wp-greet-offer-stamps-buddypress-adminonly']): ?>
  <tr class="wp-greet-form">
    <td class="wp-greet-form-left">{%bbgroup_receiver_label%}:</td>
    <td class="wp-greet-form">{%bbgroup_receiver_input%}</td>
  </tr>
<?php endif;?>
  
  <tr class="wp-greet-form">
    <td class="wp-greet-form-left">{%recvname_label%}:</td>
    <td class="wp-greet-form">{%recvname_input%}</td>
  </tr>
  
  <tr class="wp-greet-form">
    <td class="wp-greet-form-left">{%recvmail_label%}:</td>
    <td class="wp-greet-form">{%recvmail_input%}
<?php if ($wpg_options['wp-greet-multi-recipients']): ?>
	<br />
	<div style='font-size: 8px;'><?php  _e("Multiple adresses can be separated by comma", "wp-greet") ?></div>
<?php endif; ?>
	</td>
  </tr>

<?php if ($wpg_options['wp-greet-multi-recipients-bcc']): ?>
  <tr class="wp-greet-form">
    <td class="wp-greet-form-left">{%recvbcc_label%}:</td>
    <td class="wp-greet-form">{%recvbcc_input%}</td>
  </tr>
<?php endif; ?>

<?php if ($wpg_options['wp-greet-audio']): ?>
    <tr class="wp-greet-form">
    <td class="wp-greet-form-left">{%audio_label%}:</td>
    <td class="wp-greet-form">{%audio_input%}</td>
    </tr>
<?php endif; ?>
  
<?php if ($wpg_options['wp-greet-future-send']): ?>
  <tr class="wp-greet-form"><td class="wp-greet-form-left">{%futuresend_label%}:</td>
  <script type="text/javascript">jQuery(document).ready(function () {
      var offset = new Date().getTimezoneOffset();
      offset=-1*(offset/60)*100;
      if (offset>0) {
	  offstr='+'+offset.toString();
	} else {
	  offstr=offset.toString();
	}
	if (offstr.length<5)
	  offstr=offstr.substring(0,1)+'0'+offstr.substring(1);
	/*jQuery('#fsend').datetimepicker({dateFormat: 'dd.mm.yy',timeFormat: "HH:mm z",showTimezone:false,timezone:offstr }); */ 
	flatpickr("#fsend", { enableTime: true, dateFormat: "d.m.Y H:i", minDate: "today", time_24hr: true } );
	}); 
</script>
					
	<td class="wp-greet-form"><div>{%futuresend_input%}</div></td>
  </tr>
<?php endif; ?>

  <tr class="wp-greet-form">
    <td class="wp-greet-form-left">{%subject_label%}:</td>
    <td class="wp-greet-form">{%subject_input%}</td>
  </tr>
 
<?php if (is_plugin_active('wp-greet-autotext/wp-greet-autotext.php')) : ?> 
   <tr class="wp-greet-form">
    <td class="wp-greet-form-left">{%autotext_label%}:</td>
    <td class="wp-greet-form">{%autotext_input%}</td>
  </tr>
<?php endif; ?>

  <!--  script for message length control -->
<!-- 
  <script type="text/javascript">
  jQuery(document).ready(function()  {
    var characters = 600;
    jQuery("#counter").append("You have <strong>"+  characters+"</strong> characters remaining");
    jQuery("#message").keyup(function(){
        if(jQuery(this).val().length > characters){
        	jQuery(this).val(jQuery(this).val().substr(0, characters));
        }
    var remaining = characters -  jQuery(this).val().length;
    jQuery("#counter").html("You have <strong>"+  remaining+"</strong> characters remaining");
    if(remaining <= 10)
    {
        jQuery("#counter").css("color","red");
    }
    else
    {
        jQuery("#counter").css("color","black");
    }
    });
});</script>

<script type="text/javascript">
  jQuery(document).ready(function()  {
    var maxlines = 15;
    var characters = 600;
    var lines = maxlines;
    jQuery("#counter").append("You have <strong>"+  lines +"</strong> lines or <strong>" + characters + "characters remaining");
    jQuery("#message").keyup(function(){
        	x = jQuery(this).val().split('\n');
            lines = maxlines - x.length + 1;
            while ( lines < 0 ) {
                var li = jQuery(this).val().lastIndexOf('\n');
                jQuery(this).val(jQuery(this).val().substr(0, li));
                x = jQuery(this).val().split('\n');
                lines = maxlines - x.length;
            }
        if(jQuery(this).val().length > characters){
           	jQuery(this).val(jQuery(this).val().substr(0, characters));
        }
        var remaining = characters -  jQuery(this).val().length;
        jQuery("#counter").html("You have <strong>"+  lines +"</strong> lines or <strong>" + remaining + " characters remaining");
            
        if(lines <= 3 || remaining <= 10)
        {
            jQuery("#counter").css("color","red");
        }
        else
        {
            jQuery("#counter").css("color","black");
        }
    });
});</script>
-->
  
  <tr class="wp-greet-form">
    <td class="wp-greet-form-left" colspan="2">{%message_label%}:</td>
  </tr>
  
  <tr class="wp-greet-form">
      <td class="wp-greet-form-left" colspan="2">{%message_input%}<br/><!-- <div id="counter"></div> --></td>
  </tr>
  

<?php if ( $wpg_options['wp-greet-smilies'] == 1): ?>  
  <tr class="wp-greet-form">
    <td class="wp-greet-form-left">{%smilies_label%}:</td>
    <td class="wp-greet-form">{%smilies_input%}</td>
  </tr>
<?php endif; ?>
		
<?php if ( $captcha != 0): ?>
  <tr class="wp-greet-form">
    <td class="wp-greet-form-left">{%captcha_label%}</td>
    <td class="wp-greet-form" >{%captcha_input%}</td>
  </tr>
<?php endif; ?>


<?php if ( $wpg_options['wp-greet-touswitch'] == 1): ?>
  <tr class="wp-greet-form">
    <td class="wp-greet-form" colspan="2">{%tou_input%} {%tou_label%}</td>
  </tr>
<?php  endif; ?>

<?php if ( defined('BWCARDS') or defined('AMACARDS') ): ?>
  <tr class="wp-greet-form">
    <td class="wp-greet-form-left">Package code:</td>
    <td class="wp-greet-form" >{%vcode_input%}
    <br />
	<div style='font-size: 8px;'><?php  _e("If you have a package code, enter it here", "wp-greet") ?></div>
	</td>
  </tr>
<?php endif; ?>

<tr class="wp-greet-form">
  <td colspan='2' class="wp-greet-form">
    <div class='wp-greet-submitters' >&nbsp;
    {%preview_button%}&nbsp;
    {%send_button%}&nbsp;
    {%reset_button%}&nbsp;
    <input type="button" onclick="javascript:history.back()" value="<?php _e("Back","wp-greet")?>"/>
    </div>
  </td>
</tr>
</table>