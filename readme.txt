=== wp-greet ===
Contributors: tuxlog
Donate link: http://www.tuxlog.de
Tags: send, greeting, card, email, greetcard
Requires at least: 4.1
Tested up to: 6.6
Stable tag: 6.2
License: GPLv2 or later

wp-greet sends greeting cards from your WordPress blog. 
It works with WordPress-, NextGen- or NextCellentGallery.

== Description ==
wp-greet is a plugin for the famous WordPress blogging system,
giving your users the ability to send greeting cards from your blog.

Features:

   + uses WordPress native gallery NextCellentGallery or NextGenGallery for maintainig the greeting card picture
   + storing statistics about the sent greeting cards 
   + adding your own css
   + control who can send cards
   + add default subject, header and footer to the greeting cards
   + various settings for sending the mails (SMTP, SSL, PHPMail)
   + supports Antispam Plugins CaptCha! and Math-Comment-Spam-Protection-Plugin
   + sign your greeting cards with your own stamp
   + backgroundmusic for your cards
   + supports individual terms of usage 
   + supports confirmation mail processing
   + supports fetching the card online or sent it by mail
   + can send cards in the future
   + WPML certified
   
Using HOWLER.js (https://github.com/goldfire/howler.js)
Copyright (c) 2013-2018 James Simpson and GoldFire Studios, Inc.
Released under the MIT License.


== requirements ==

* Wordpress >= 4.1.x
* Optional: NextCellentGallery, NextGenGallery >= v1.90


== Installation ==
	
1.  Upload to your plugins folder, usually `wp-content/plugins/`, keeping the directory structure intact (e.g. `wp-greet.php` should end up in `wp-content/plugins/wp-greet/`).

1.  Activate the plugin on the plugin screen.

1.  Visit the configuration page (Options -> wp-greet) to configure the plugin (do not forget to add the forms page id)

1.  Optional: If you would like to change the style, just edit wp-greet.css

== Frequently Asked Questions ==

= How can I make wp-greet work with NextgenGallery Version >= 2.0.0? =

Unfortunately Photocrati did a major redesign of NGG and therfore the connecting filters were removed. You can read about this redesign in the wordpress.org forums. 
But we can make it work by editing nextgen-gallery/products/photocrati_nextgen/modules/nextgen_basic_gallery/templates/thumbnails/index.php and change one line.
from
 <a href="<?php echo esc_attr($storage->get_image_url($image))?>"
to
 <a href="<?php echo apply_filters('ngg_create_gallery_link', esc_attr($storage->get_image_url($image)), $image)?>"
You can also fetch the patched file (index.php) from the wp-greet/patch directory and copy it 
to nextgen-gallery/products/photocrati_nextgen/modules/nextgen_basic_gallery/templates/thumbnails

If this seems to difficult try using the new interface to WordPress native gallery to embed your greetingcard pictures.
 
= My greetcard form is wider than my theme. What can I do? =

To adjust the design of your greetingcard page edit the file wp-greet.css.
If you have a narrow theme you might adjust the width of the textarea
textarea.wp-greet-form { width: 90%; } by replacing the 90% with something smaller than this.


= How can I use the Math Comment Spam Protection Plugin with wp-greet? =

Upload the unzipped directory "math-comment-spam-protection" on your webspace into wp-content/plugins and activate the plugin. Under Settings -> Math Comment Spam klick "Update Options" once even without having changed any options, otherwise the plugin won't work. You don't have to change the text of the error messages, as these are fixed within wp-greet.


= I want to use WordPress media gallery but the links of the images don't work. What can I do? =

Please set the link target of the WordPress galleries to "media file" in the gallery options.


== Screenshots ==

1. Sending a greetingcard with wp-greet (shows the user interface for entering a greetingcard)
2. Preview a greetingcard with wp-greet 
3. Admin-Dialog of wp-greet


== update from prior v1.1 ==   
IMPORTANT:
   	Please be sure to remove all files belonging to versions prior 
	to v1.1 before uploading v1.1

   	Please be sure to remove the patched version of nggfunctions.php
	which was necessary to integrate wp-greet with NextGenGallery 
	prior to version 1.1

== update to  v1.7 and higher ==   
IMPORTANT:
   	Please be sure to deactivate and activate the plugin one time
	because the database updates will only be executed during 
	plugin activation

== usage from v1.1 on ==
1. Create a page or posting containing the tag [wp-greet].
1. Remember the permalink of this page/post
1. Enter the page/post number at the wp-greet admin dialog into the field Form-Post/Page and switch to your favourite gallery plugin
1. Create a page with your favourite gallery on it using the following syntax, e.g. for ngg: [gallery=1]
1. thats it, just klick on a picture on the gallery page and send it

For more details see the online documentation of wp-greet.
http://www.tuxlog.de/wordpress/2008/wp-greet-documentation-english/	 

== translations ==

   wp-greet comes with english and german translations.
   if you would like to add a new translation, just take the file
   wp-greet.pot (in the wp-greet main directory) copy it to
   <iso-code>.po and edit it to add your translations (e.g. with poedit).

   Meanwhile a swedish, french, italians and vietnames translation was 
   kindly build by other users. See Changelog for credits.


== Changelog ==

= v6.2 (2023-03-03) =
* fixed html error in admin dialog
* come closer to WP coding standards


= v6.1 (2021-12-19) =
* replaced dtpicker from Trend Richardson with flatpickr because dtpicker is not longer supported
* fixed a typo with determine timezone of visitor

= v6.0 (2021-05-02) =
* fixed a typo in html of wpg-form.php
* make wp-greet recognize gutenberg gallery blocks again
* renamed a check_email function which misses the wpg_ prefix in its name

= v5.9 (2020-11-18) =
* extend support for PHPMailer 6 in newer WordPress releases
* fixed some PHP notices about undefined index


= v5.8 (2020-09-18) =
* fixed deprecated function each since PHP7.2
* use PHPMailer 6 if on WP >= 5.5

= v5.7 (2020-05-20) =
* fixed WPLANG warning

= v5.6 (2019-02-16) =
* extend support for cross-brwoser background music using Howler.js

= v5.5 (2018-11-05) =
* fixed deprecated warning each()
* switch from wp-recaptcha to google-captcha since wp-recaptcha is not longer supported

= v5.4 (2018-05-05) =
* added the possibility to edit the label for the terms of use checkbox

= v5.3 (2017-09-16) =
* fixed use of HTML Mailtexts for confirmation mails
* fixed use of default string for mail confirmation
* fixed typo of sent card message
* fixed tomail frommail in confirmation mail


= v5.2 (2017-02-02) =
* fixed template using is_plugin_active
* added condition for adding NGG image infos
* fixed empty fetchuntil date
* fixed default SMTP ports
* added de-DE-formal translation


= v5.1 (2016-08-13) =
* removed home path from stamp url for security reasons
* add background music feature
* updated readme.txt and plugin icons
* removed inline default value for fixed image width and replaced it by a standard setting of 100%
* added dkim support
* added support for wp-greet-autotext extension 
* switched back link in form to back button
* added support for selectable stamps (incl. BuddyPress Group Avatars)
* extended database to store long image urls
* fixed some PHP7 compatibility issues
* removed some PHP warnings
* added option to sen card as Blindcopy

= v5.0 (2015-12-23) =
* fixed thickbox terms of use URL to allow changing width and height in wpg-form.php
* added several mail parameters to give users more control about mail addresses 
* separated admin dialog for mail settings
* reordered admin dialog to make it (hopefully) easier to understand
* added customizable message to be displayer after a confirmation mail has beent sent
* fixed reschedule future cards thickbox dialog 
* fixed problem with single quotes in message and subject

= v4.9 (2015-12-17) =
* secured html input with wp_kses
* fixed lost linebreaks using tinymce
* made message to be displayed after a card has been sent customizable

= v4.8 (2015-12-12) =
* renamed form field title to wpgtitle to avoid collision with WP-Core title field in OEmbed code
* added pre filled form for use with Premium Selling Cards Plugin
* fixed problem with missing captcha plugin
* clean up old dependency for bw-cards and NGG custom fields
* turn terms of usage thickbox in an inline thickbox to improve security


= v4.7 (2015-03-07) =
* fixed handling of apostrophe in mail addresses
* added reschedule feature to reorganize future cards
* fixed static sender in mails other than card mail with attachment
* adopted to wp-recaptcha >= 4.1
* use client timezone for future send cards
* fixed confirmation an cc to sender checkbox setting when send a card to another recipient
* excluded wp-greet-formpage from homepage view
* wp-greet.css will only be loaded if you are on a wp-greet form page
* preset time in timepicker for futuresend
* removed php notices about missing index
* adjusted width of terms of usage thickbox

= v4.6 (2014-12-25) =
* fixed a problem with jetpack gallery extensions
* fixed a bug with WP4 and future send
* fixed future send problem

= v4.5 (2014-09-06) =
* added greek language thanks to George
* added NGG/NCG description to mail if activated
* fixed some typos
* load javascript only on formspage where is it needed

= v4.4 (2014-07-28) =
* integrated SMTP parameters in to admin dialog
* added support for SMTP SSL transfer

= v4.3 (2014-06-25) =
* improved compatibility with JetPack Carousel and Jetpack tiled gallery 
* fixed problem with html in link-mail
* added first responsive approach to the form
* adopted TinyMCE toolbar to API V4
* work around WordPress bug 26649
* added support for WPML
* fixed html entitties in receiver/sender mail labels
* new extended dutch translation (thanks to Danny)
* new extended french translation (thanks to Madelaine)
* load smilies javascript and css only in frontend
* added support link 

= v4.2 (2014-04-20) =
* fixed quote handling in form when switching from preview back to form
* fixed some sting handling issues in admin dialog
* fetch some php warnings
* adopted to tinyMCE 4 (WP 3.9)

= v4.1 (2014-03-23) =
* added finish translation. Thanks to Pekka Ollikainen
* fixed checkbox handling in admin dialog

= v4.0 (2014-03-14) =
* fixed cross browser bug with standard text editor
* added danish translation. Thanks to Cristian Goga.
* added the possibility to use external links behind images when using WordPress native gallery
* fixed native gallery and no permalinks gallery view
* load thickbox only on wp-greet form page for showing the terms of use
* small fix to make it work with responsive lightbox too
* fix tinymce and smilies
* added option to disable load of wp-greet default css rules
* fixed warning when next cellent gallery
* fixed some phrases in admin dialog
* adopted css to TwentyFourteen Theme
* fixed some typos in english translation

= v3.9 (2013-12-02) =
* update spanish translation. Thanks to Pascal Cousseran
* added catalan translation. Thanks to Pascal Cousseran
* fixed problems with card display and escaped html

= v3.8 (2013-12-01) =
* fixed inline images with stamp and some email clients like (outlook.com)
* fixed inline images for smilies
* removed some deprecated messages and warnings

= v3.7 (2013-11-08) =
* added some stamps. Thanks to Sam Krieg
* added TinyMCE support for the greet card form. Nice :-)

= v3.6 (2013-10-20) =
* added russian translation. Thanks to Papuna.
* fixed some problems with permalinks and native galleries
* now works with Jetpack Carousel
* fixed interface to new version of Captcha! plugin  

= v3.5 (2013-08-31) =
* added support for WordPress native gallery 
* fixed ereg deprecated warnings
* fixed wpdb:Escape deprecated warnings

= v3.4 (2013-08-23) =
* added patch for NGG 2.11
* adopt css to TwentyThirteen

= v3.3 (2013-08-11) =
* fixed one translation string
* make resend link work with online and offline cards
* only create log entry if log/card cleaned really delete at least one record
* added a dirty hack to make wp-greet work again with ngg version >=2.0.0
* added an admin notice with workaround when a broken NGG is used
 
= v3.2 (2013-05-09) =
* switched from load_textdomain to load_plugin_textdomain for compatibility reasons
* added dutch translation. Thanks to Danny van Leeuwen
* added spanish translation. Thanks to Pascal Cousseran
* template vars are now accepted in mailheader and mailfooter too
* check if email was already sent to avoid conflicts with other plugins which are using wp_redirect or similiar
* clean up some php warnings
* send each recipient one mail instead of one mail to all recipients for data protection reasons
* generate unique cid's for image attachments
* make sure datepicker extension is only loaded with wp-greet form
* added support for bwcards plugin a selling plattform for greetings cards
* fixed deprecated warning from add_option
* fixed dbDelta Syntax
* fixed sender got multiple CC's when sending to more than one recipient
* updated datepicker and jqueryui to new version for compatibility with WP >=3.5
* added romanian translation (Thanks to Bogdan)
* added resend this card feature
* extend future send to allow using UTC or timezone settings

= v3.1 (2012-06-07) =
* added support for WP-reCAPTCHA (must be installed and setup correctly to use)
* fixed some PHP Notices 
* replaced user_level to capability in add_menu_* to get rid of these deprecated warnings

= v3.0 (2012-04-27) =
* use sender mail and name as replyto address even when static sender is set and mailreturnpath is not set
* fixed sendername in link mails was set to blogname not to entered sendername

= v2.9 (2012-04-21) =
* fixed copy to sender was not sent when using greetcardlinkmails
* changed to new php mailer interface for adding attachments
* added css class wp-greet-error for formatting the warning and error messages

= v2.8 (2012-02-07) =
* fixed a problem using online cards without confirmation
* fixed german translation
* added english translation for datepicker 

= v2.7 (2011-12-21) =
* adopted to HTML5 for compatibility with 3.3

= v2.6 (2011-12-05) =
* added static sender address feature to better support secure providers
* added a bit more javascript to mark disabled fields in admin dialog 
* added wp collation in create table statements (caused problem with asian collations)
* fixed received confirmation was sent on every fetch on some configs

= v2.5 (2011-11-12) =
* fixed another conflict wit wordpress mu

= v2.4 (2011-11-05) =
* added partial vietnamese translation, thanks to Diana
* fix allow gallery and form on onepage
* added option to use/display the data from ngg in img tag
* added option to allow sending to multiple recipients
* added option to allow sending cards in the future (using jquery datepicker, thanks folks, great job)
* added option to allow sending a confirmation to the sender when the card is fetched
* fixed incompatibility with wordpress mu in register_activation and deactivation
* add support for captcha >=V2.08

= v2.3 (2010-12-01) =
* added swedish translation (thanks to Helene)
* fixed confirmation mail method link generation (get_permalink problem)
* fixed gallery link geneartion when not using permalinks
* cc to sender now works even if you are using the "link variant"

= v2.2 (2010-10-01) =
* changed link generation to use wordpress default method
* added french translation (thanks to Patrick)
* update plugin to use new MathCommentSpamPlugin interface

= v2.1 (2009-12-05) =
* fixed problem with apostrophs in greetcard subject
* fixed problem with some permalinks settings
* added italian translation (thanks to Daniele)

= v2.0 (2009-11-21) =
* fixed process for fetching online without confirmation
* fixed invalid xhtml in alt attribut during card fetch
* fixed quote escaping when going back to greet form
* fixed stamped image condition in conjunction with send image inline
* fixed invalid xhtml in alt attribut on logging dialog
* added note when stamp image is not found
* fixed default value for stamp image
* fixed re-show of greet form while confirmation
* fixed admin dialog for control read-only status for stamp input field
* fixed onlinecard plausi to be only validated if fetch cards online is active
* fixed empty img width attribute when width parameter is empty (resulted in invalid XHTML)
* fixed confirmation-link expiration if number of hours is 0 (= never expires)
* fixed missing translation to german in security dialog
* added some tooltips to admin dialog

= v1.9 (2009-11-03) =
* fixed XHTML errors in formdialog when using stamps
* added mandatory field selection feature

= v1.8 (2009-10-12) =
* fixed some XHTML errors in admin dialog
* fixed timestamp incompatibility between mysql < v4.1 and mysql >= v4.1
* added admin dialog checkings carddays > fetch online days

= v1.7 (2009-10-11) = 
* fixed some minor xhtml errors
* added new admin dialog security
* added feature to use an email for sender address verification
* added terms of usage feature
* added automatic deletion of log and card entries and parameters
* added feature to fetch the card online instead of sending it via mail


= v1.6 (2009-08-15) = 
* changed debug function name to avoid collision
* check for checkdnsrr function to exist before using it
* extend email address validation to be more correct (e.g. accept .co.uk addresses)
* switched readme.txt to new changelog format

= v1.5 (2009-06-06) =
* clean up code to avoid warnings in wordpress debug mode
* add stamp function to add a stamp to greetingcards
* readme.txt validated
* added screenshots to package
* added icon for wordpress menu entry
* added parameter to set width of stamp

= v1.4 (2009-02-08) = 
* fixed missing semicolon in phpmailer-conf.php
* added none option to disable spam protection
* fixed bug with quotes in mail-header and mail-footer
* added option to control the mailtransfer method (smtp or php-mail)
* fixed Spamlabel was showed, even when no captcha support was selected

= v1.3 (2009-01-03) = 
* add support for Math-Comment-Spam-Protection-Plugin
* add paging for logfile
* fix bug with ngg >v0.99 and thickbox effect

= v1.2 (2008-11-30) =
* fixed some typos
* added smiley support
* added remote ip adress to log information
* added automatic sender an receiver name
* disable options deletion during plugin deactivation (seems people like it more having a bit trash in their tables, instead of setting up the plugin every time ;-) )
* added fields for sender and receiver name

= v1.1 (2008-10-04) =
* integrate ngg without patching it (thanks to Alex Rabe for adding the needed filter hooks)
* add gallery selection to admin dialog
* add form page selector to admin dialog
* fixed quote handling in textarea
* disable captcha parameter during installation
* extended css to be more flexible with different themes

= v1.0 (2008-04-14) = 
* added captcha support
* removed dependency to phpmailer package

= v0.9 (2008-04-06) =
* Initial release

== Upgrade Notice ==
= 5.0 =
New flexible settings for all mail addresses 
fixed reschedule future cards and fixed single quotes in message and subject
