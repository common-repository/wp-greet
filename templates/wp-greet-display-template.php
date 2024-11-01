<?php
/*
 *  Template for the display a wp-greet card
 *  
 *  The following placeholders can be used:
 *  {%sendername%}					- name of the sender
 *  {%sendermail%}					- email address of the sender
 *  {%ccsender%}					- should the sender get a copy of the greet card mail?
 *  {%recvname%}					- name of the receiver
 *  {%recvmail%}					- email address of the receiver
 *  {%subject%}						- subject of the message
 *  {%wp-greet-default-header%}		- gives the wp-greet header (can be set in the admin dialog)
 *  {%wp-greet-default-footer%}		- gives the wp-greet footer (can be set in the admin dialog)
 *  {%image_url%}					- gives an img tag to show the greet card picture
 *  {%message%}						- the message
 */
?>

<h2><?php _e("A Greeting Card for you","wp-greet") ?></h2>

<table>
  <tr>
    <th style='text-align:left'><?php _e("From","wp-greet")?>:</th>
    <td>{%sendername%}&nbsp;&lt;{%sendermail%}&gt;</td>
  </tr>
  
  <tr>
    <th style='text-align:left'><?php _e("To","wp-greet")?>:</th>
    <td>{%recvname%}&nbsp;&lt;{%recvmail%}&gt;</td>
  </tr>
  
  <tr>
    <th><?php _e("Subject","wp-greet")?>:</th>
    <td>{%subject%}</td>
  </tr>
</table>

<?php if ($wpg_options['wp-greet-audio']): ?>
<script>
	var sound = new Howl({
		src: ['{%audiourl%}'],
		autoplay: true,
		loop: true,
		volume: 0.5,
	});
	sound.play();
</script>
<div>
<button onclick="sound.play();">Musik an</button>
<button onclick="sound.stop();">Musik aus</button>
</div>
<?php endif; ?>

<div>{%wp-greet-default-header%}</div>

{%image_url%}



<p>{%message%}</p>
{%wp-greet-default-footer%}