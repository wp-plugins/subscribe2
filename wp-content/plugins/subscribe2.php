<?php
/*
Plugin Name: Subscribe2
Plugin URI: http://www.skippy.net/blog/2005/02/17/subscribe2
Description: Notifies an email list when new entries are posted. 
Version: 2.0.9
Author: Scott Merrill
Author URI: http://www.skippy.net/
*/

// ****************************************
// CHANGE THIS TO 1 IF YOU ARE ON DREAMHOST
// ****************************************
$dreamhost = 1;

/////////////////////
// main program block
add_action ('admin_menu', 'subscribe2_menu');
add_action ('publish_post', 'subscribe2', 8);
//////////// END MAIN PROGRAM /////////////


//////////// BEGIN FUNCTIONS //////////////
function subscribe2_menu() {
	add_management_page('Subscribers', 'Subscribers', 9, __FILE__, 's2_manage');
	add_options_page('Subscribe2 Options', 'Subscribe2', 9, __FILE__, 's2_options');
}

//////////////////////////////////
if (! function_exists('subscribe2')) {
function subscribe2 ($post_ID = 0) {
global $dreamhost, $table_prefix, $wpdb;

$s2_table = $table_prefix . "subscribe2";

// gets the name of your blog
$blogname = get_settings('blogname');
// gets the link to the new post
$postlink = get_permalink($post_ID);
$postdata = get_postdata($post_ID);
$cats = wp_get_post_cats('1', $post_ID);

// is this post's date set in the future?
if ($postdata['Date'] > current_time('mysql')) {
	// if so, let's not tell anyone about this
        return $post_ID;
}

// get our options
$s2 = get_option('s2_options');

// should we bypass the email notice on this post?
$skip = explode(',', $s2['s2_cats_to_skip']);
$bypass = '0';
foreach ($skip as $skippy) {
	if ('1' == $bypass) { break; }
	if (in_array($skippy, $cats)) {
		$bypass = '1';
	}
}
if ('1' == $bypass) { return $post_ID; }

// do we send as admin, or post author?
if ('author' == $s2['s2_sender']) {
	// get author details
	$user = get_userdata($postdata['Author_ID']);
} else {
	// get admin detailts
	$user = get_userdata(1);
}
$myname = $user->user_nicename;
$myemailadd = $user->user_email;

// gets the path to your blog directory
$s2_link = get_settings('siteurl') . "/subscribe.php";

// get the list of active recipients from the database
$sql = "SELECT email FROM " . $s2_table . " WHERE active='1'";
$recipients = $wpdb->get_col($sql);
if (count($recipients) == 0) {
	// no one to send to!
	return $post_ID;
}

// Set email subject
$subject = stripslashes($s2['s2_subject']);
// do any substitutions that are necessary
$subject = str_replace('BLOGNAME', $blogname, $subject);
$subject = str_replace('TITLE', $postdata['Title'], $subject);
$subject = str_replace('MYNAME', $myname, $subject);
$subject = str_replace('EMAIL', $myemailadd, $subject);

// Set sender details
$headers = "From: " . $myname . " <" . $myemailadd . ">\r\n";

// BCC all recipients
// with batching for Dreamhost
if (1 == $dreamhost) {
	$count = 1;
	$bcc = '';
	$batch = array();
	foreach ($recipients as $recipient) {
		$recipient = trim($recipient);
		if (! empty($recipient)) {
			$bcc .= "Bcc: " . $recipient . "\r\n";
		}
		if (30 == $count) {
			$count = 1;
			$batch[] = $bcc;
			$bcc = '';
		} else {
			$count++;
		}
	}
	if (0 == count($batch)) {
		// we have less than 30 subscribers, so let's skip batching
		$headers .= $bcc;
		unset($batch);
	}
} else {
	// we're not on dreamhost, so do it normal
	foreach ($recipients as $recipient) {
		$recipient = trim($recipient);
		if (! empty($recipient)) {
			$headers .= "Bcc: " . $recipient . "\r\n";
		}
	}
}

// prepare the message template
$mailtext = stripslashes($s2['s2_mailtext']);
$mailtext = str_replace('BLOGNAME', $blogname, $mailtext);
$mailtext = str_replace('BLOGLINK', get_bloginfo('url'), $mailtext);
$mailtext = str_replace('TITLE', $postdata['Title'], $mailtext);
$mailtext = str_replace('PERMALINK', $postlink, $mailtext);
$mailtext = str_replace('S2LINK', $s2_link, $mailtext);
$mailtext = str_replace('MYNAME', $myname, $mailtext);
$mailtext = str_replace('EMAIL', $myemailadd, $mailtext);
if ('post' == $s2['s2_excerpt']) {
	$content = $postdata['Content'];
} elseif ('excerpt' == $s2['s2_excerpt']) {
	$content = $postdata['Excerpt'];
} else {
	$content = '';
}
$mailtext = str_replace('EXCERPT', $content, $mailtext);

if ('html' == $s2['s2_html']) {
	// To send HTML mail, the Content-type header must be set
	$headers .= 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: ' . get_bloginfo('html_type') . '; charset='. get_bloginfo('charset');
	$mailtext = apply_filters('the_content', $mailtext);
	$mailtext = str_replace(']]>', ']]&gt;', $mailtext);
	$mailtext = "<html><head><title>$subject</title></head><body>" . $mailtext . "</body></html>";
} else {
	 $headers .= 'MIME-Version: 1.0' . "\r\n";
	 $headers .= 'Content-type: text/plain; charset='. get_bloginfo('charset');
	$mailtext = strip_tags($mailtext);
}

// And away we go...
if (isset($_POST['publish'])) { // we only want to send on publish
	// handle batches for Dreamhost
	if ( (1 == $dreamhost) && (isset($batch)) ) {
		foreach ($batch as $bcc) {
			$newheaders = $headers . $bcc;
			mail($myemailadd, $subject, $mailtext, $newheaders);
		}
	} else {
		mail($myemailadd, $subject, $mailtext, $headers);
	}
}
return $post_ID;
} // end subscribe2 
}

///////////////////////
function s2_install() {
// include upgrade-functions for maybe_create_table;
if (! function_exists('maybe_create_table')) {
	require_once(ABSPATH . '/wp-admin/upgrade-functions.php');
}

global $table_prefix;
$s2_table = $table_prefix . "subscribe2";
$s2_table_sql = "CREATE TABLE " . $s2_table . "( id int(11) NOT NULL auto_increment, email varchar(64) NOT NULL default '', active tinyint(1) default 0, PRIMARY KEY (id) )";

// create the table, as needed
maybe_create_table($s2_table, $s2_table_sql);

s2_reset();
} // s2_install

///////////////////
function s2_reset() {
$s2 = array ('s2_html' => 'text',
        's2_sender' => 'author',
        's2_excerpt' => 'excerpt',
        's2_subject' => 'BLOGNAME has been updated!',
        's2_mailtext' => "BLOGNAME has posted a new item, 'TITLE'\r\nEXCERPT\r\nYou may view the latest post at\r\nPERMALINK\r\nYou received this e-mail because you asked to be notified when new updates are posted.\r\nIf you no longer wish to receive notifications of new posts then please visit:\r\nS2LINK\r\n\nBest regards,\r\nMYNAME\r\nEMAIL",
        's2_welcome' => 'By subscribing to this service you will be notified every time a new post is added.',
        's2_confirm_subject' => 'Confirmation Request from BLOGNAME',
	's2_confirm_email' => "In order to confirm your request for BLOGNAME, please click on the link below:\n\nLINK\n\nIf you did not request this, please feel free to disregard this notice!\n\nThank you,\nMYNAME.",
        's2_invalid' => 'Sorry, but that does not look like an email address to me!',
        's2_self' => "Thanks, but I'll make my own decisions about my email!",
        's2_already_there' => 'I already know about that email address.',
        's2_not_there' => "That email address wasn't in the system.",
        's2_add_confirm' => 'Thank you for subscribing, a confirmation email is on its way!',
        's2_delete_confirm' => 'An email has been sent to you with further instructions.',
        's2_added' => 'Your email address has been successfully subscribed. Thank you!',
        's2_deleted' => 'Your email has been removed from the list.',
        's2_subscribed_admin_subject' => 'New subscriber!',
        's2_unsubscribed_admin_subject' => 'Subscriber removed.',
	's2_cats_to_skip' => ''
        );

update_option('s2_options', $s2, '', 'no');

} // end s2_reset

////////////////////
function s2_options() {
global $wpdb, $table_prefix, $cache_categories;

$s2_table = $table_prefix . "subscribe2";

// check if we need to install the table
$sql = "SELECT COUNT(id) FROM " . $s2_table;
// turn errors off, for the momemnt
$errors = $wpdb->hide_errors();
$foo = $wpdb->get_var($sql);
// turn errors back on
$errors = $wpdb->show_errors();
if ('' == $foo) { s2_install(); }

// now try to figure out what we're supposed to do
if (isset($_POST['s2_admin'])) {
	$admin = $_POST['s2_admin'];
}
if ('options' == $admin) {
	s2_options_update();
} elseif ('RESET' == $admin) {
	s2_reset();
}

$s2 = get_option('s2_options');
?>
<div class='wrap'>
<h2>Notification Settings</h2>
<form method="POST">
<input type='hidden' name='s2_admin' value='options' />
<fieldset class="options"><legend>Email Options:</legend>
Send email as:&nbsp;&nbsp;
<input type='radio' name='s2_html' value='html' <?php if ('html' == $s2['s2_html']) { echo "checked='checked' "; } ?>/> HTML &nbsp;&nbsp;
<input type='radio' name='s2_html' value='text' <?php if ('text' == $s2['s2_html']) { echo "checked='checked' "; } ?>/> Plain Text
<br /><br />
Send Email From:&nbsp;&nbsp;
<input type='radio' name='s2_sender' value='author' <?php if ('author' == $s2['s2_sender']) { echo "checked='checked' "; } ?>/> Author of the post &nbsp;&nbsp;
<input type='radio' name='s2_sender' value='admin' <?php if ('admin' == $s2['s2_sender']) { echo "checked='checked' "; } ?>/> Blog Admin
<br /><br />
Amount of post to deliver:&nbsp;&nbsp;  
<?php
$foo = array ('none' => 'None', 'excerpt' => 'Excerpt Only', 'post' => 'Full Post');
foreach ($foo as $value => $key) {
        echo "<input type='radio' name='s2_excerpt' value='$value'";
        if (strtolower($value) == strtolower($s2['s2_excerpt'])) {
                echo " checked='checked'";
        }
        echo " /> $key &nbsp;&nbsp;";
}
?>
</fieldset>

<fieldset class="options"><legend>Email Template</legend>
Subject: (must not be empty)<br />
<input type="text" name="s2_subject" size="60" value="<?php echo stripslashes($s2['s2_subject']); ?>" />
<br /><br />
Message: (must not be empty)<br />
<textarea rows='15' cols='90' name='s2_mailtext'><?php echo stripslashes($s2['s2_mailtext']); ?></textarea>

<fieldset class="options"><legend>Message substitions:</legend>
<table width="100%">
<tr><td width="50%">
<ul>
<li><b>BLOGNAME</b>: replaced with <?php bloginfo('name'); ?></li>
<li><b>BLOGLINK</b>: replaced with <?php bloginfo('url'); ?></li>
<li><b>TITLE</b>: replaced with the post's title</li>
<li><b>EXCERPT</b>: replaced with blank, the excerpt, or the entire post, based on the option set above</li>
</ul>
</td><td>
<ul>
<li><b>PERMALINK</b>: replaced with the post's permalink</li>
<li><b>S2LINK</b>: replaced with a link to your subscribe.php file</li>
<li><b>MYNAME</b>: replaced with the post author's name</li>
<li><b>EMAIL</b>: replaced with the post author's email</li>
</ul>
</td></tr>
</table>
</fieldset>
</fieldset>

<h2>Subscription Messages</h2>
<fieldset class="options"><legend>Website messages:</legend>
<table width="100%" cellspacing="2" cellpadding="5" class="editform">
<tr><td colspan="2" align="center">
Welcome message:<br />
<input type='text' size='90' name='s2_welcome' value="<?php echo stripslashes($s2['s2_welcome']); ?>" />
</td></tr>
<tr><td>
Invalid email was supplied:<br />
<input type='text' size='53' name='s2_invalid' value="<?php echo stripslashes($s2['s2_invalid']); ?>" />
</td><td>
Your email was supplied:<br />
<input type='text' size='53' name='s2_self' value="<?php echo stripslashes($s2['s2_self']); ?>" />
<td></tr>
<tr><td>
Known email was supplied:<br />
<input type='text' size='53' name='s2_already_there' value="<?php echo stripslashes($s2['s2_already_there']); ?>" />
</td><td>
Non-existant email supplied:<br />
<input type='text' size='53' name='s2_not_there' value="<?php echo stripslashes($s2['s2_not_there']); ?>" />
<td></tr>
<tr><td>
Subscribe confirmation email dispatched:<br />
<textarea cols='50' rows='3' name='s2_add_confirm'><?php echo stripslashes($s2['s2_add_confirm']); ?></textarea>
</td><td>
Unsubscribe confirmation email dispatched:<br />
<textarea cols='50' rows='3' name='s2_delete_confirm'><?php echo stripslashes($s2['s2_delete_confirm']); ?></textarea>
</td><tr>
<tr><td>
Successful subscription message:<br />
<input type='text' size='53' name='s2_added' value="<?php echo stripslashes($s2['s2_added']); ?>" />
</td><td>
Successful deletion message:<br />
<input type='text' size='53' name='s2_deleted' value="<?php echo stripslashes($s2['s2_deleted']); ?>" />
</td></tr>
</table>
</fieldset>

<fieldset class="options"><legend>Email messages:</legend>
<table width="100%" cellspacing="2" cellpadding="5" class="editform">
<tr><td colspan="2">
Subject line for all confirmation emails:<br />
<input type='text' size='50' name='s2_confirm_subject' value="<?php echo stripslashes($s2['s2_confirm_subject']); ?>" />
</td></tr>
<tr><td colspan="2">
Subscribe / Unsubscribe confirmation email:<br />
<textarea cols='80' rows='5' name='s2_confirm_email'><?php echo stripslashes($s2['s2_confirm_email']); ?></textarea>
</td></tr>
<tr><td>
Subscribe notification subject sent to admin:<br />
<input type='text' size='50' name='s2_subscribed_admin_subject' value="<?php echo stripslashes($s2['s2_subscribed_admin_subject']); ?>" />
</td><td>
Unsubscribe notification subject sent to admin:<br />
<input type='text' size='50' name='s2_unsubscribed_admin_subject' value="<?php echo stripslashes($s2['s2_unsubscribed_admin_subject']); ?>" />
</td></tr>
</table>

<fieldset class="options"><legend>Message substitions:</legend>
<ul>
<li><b>BLOGNAME</b>: replaced with the blog's name</li>
<li><b>LINK</b>: replaced with the confirmation link for the user's request</li>
<li><b>MYNAME</b>: replaced with the post author's name</li>
<li><b>EMAIL</b>: replaced with the post author's email</li>
</ul>
</fieldset>
</fieldset>

<h2>Categories to Exclude</h2>
<table width="50%" cellspacing="2" cellpadding="5" class="editform" align="center">
<tr><td width="50%" align="left"> 
<?php
// let's collect all of our excluded categories
$excluded = array();
$excluded = explode(',', $s2['s2_cats_to_skip']);

// let's get an array of all the categories
if (count($cache_categories) == 0) {
        update_category_cache();
}
$half = (count($cache_categories) / 2);
$i = 0;
$j = 0;
foreach ($cache_categories as $cat) {
	if ( ($i > $half) && (0 == $j) ){
		echo "</td><td width='50%' align='right'>";
		$j++;
	}
	if (0 == $j) {
		echo "<input type='checkbox' name='$cat->cat_ID' ";
		if (in_array($cat->cat_ID, $excluded)) {
			echo "checked='checked' ";
		}
		echo "/>$cat->cat_name<br />";
	} else {
		echo "$cat->cat_name <input type='checkbox' name='$cat->cat_ID' ";
		if (in_array($cat->cat_ID, $excluded)) {
			echo "checked='checked' ";
		}
		echo "/><br />";
	}
	$i++;
}
echo "</td></tr>";
echo "</table>";
echo "<p align='center'><input type='submit' name='submit' value='submit' /></p>";
echo "</form>";
?>
<h2>Reset Default</h2>
<fieldset class="options">
<p>Use this to reset all options to their defaults.  This <strong><em>will not</em></strong> modify your list of subscribers.</p>
<form method="POST">
<p align="center">
<input type="submit" name="s2_admin" value="RESET" />
</p>
</form>
</fieldset>
<?php
include(ABSPATH . '/wp-admin/admin-footer.php');
// just to be sure
die;

} // s2_options

////////////////////
function s2_manage() {
global $admin_sent, $table_prefix, $wpdb;

$s2_table = $table_prefix . "subscribe2";

// check if we need to install the table
$sql = "SELECT COUNT(id) FROM " . $s2_table;
// turn errors off, for the momemnt
$errors = $wpdb->hide_errors();
$foo = $wpdb->get_var($sql);
// turn errors back on
$errors = $wpdb->show_errors();
if ('' == $foo) { s2_install(); }

// now try to figure out what we're supposed to do
if (isset($_POST['s2_admin'])) {
        $admin = $_POST['s2_admin'];
}

if ('delete' == $admin) {
        s2_admin_delete();
} elseif ('send' == $admin) {
        s2_admin_send();
} elseif ('subscribe' == $admin) {
        s2_admin_subscribe();
} elseif ('toggle' == $admin) {
        s2_admin_toggle();
}

// get the list of confirmed subscribers
$sql = "SELECT email FROM " . $s2_table . " WHERE active='1' ORDER BY email ASC";
$confirmed = $wpdb->get_col($sql);

// get unconfirmed subscribers
$sql = "SELECT email FROM " . $s2_table . " WHERE active='0' ORDER BY email ASC";
$unconfirmed = $wpdb->get_col($sql);
if ('admin_sent' == $admin_sent) {
        echo '<div class="updated"><p align="center">Message delivered!</p></div>';
}
?>
<div class='wrap'>
<h2>Admin Tools</h2>
<table width="100%">
<tr><td align='left'>
<form method='POST'>
Subscribe Addresses: (one per line, or comma-seperated)<br />
<textarea rows='10' cols='55' name='addresses'></textarea>
<br />
<input type='submit' name='s2_admin' value='subscribe'>
</form>
</td><td align="right">
<form method='POST'>
Send email to all subscribers:
<input type='text' size='30' name='s2_subject' value="A message from <?php echo get_settings('blogname'); ?>" /> <br />
<textarea rows='10' cols='55' name='message'></textarea>
<br />
<input type='submit' name='s2_admin' value='send'>&nbsp;
</form>
</td></tr></table>
<div style="clear: both;"><p>&nbsp;</p></div>
<h2>Subscribers</h2>
<table width="45%" cellpadding="3" cellspacing="3" align="left">
<tr><th colspan="3"><strong>Confirmed Subscribers:</strong></th></tr>
<?php
if (is_array($confirmed)) {
	$alternate = 'alternate';
	foreach ($confirmed as $subscriber) {
		echo "<tr class='$alternate'>";
		 echo "<td width='5%' align='center'><form method='POST'><input type='hidden' name='email' value='$subscriber' /><input type='hidden' name='s2_admin' value='delete' /><input type='submit' name='submit' value=' X ' /></form></td>";
		echo "<td align='center'><a href='mailto:$subscriber'>$subscriber</a></td>";
		echo "<td width='5%' align='center'><form method='POST'><input type='hidden' name='email' value='$subscriber' /><input type='hidden' name='s2_admin' value='toggle' /><input type='submit' name='submit' value='->' /></form></td>";
		echo "</tr>";
		("alternate" == $alternate) ? $alternate = "" : $alternate = "alternate";
	}
} else {
	echo "<tr><td width='100%' align='center' colspan='3'><strong>NONE</strong></td></tr>";
}
?>
</table>
<table width="45%" cellpadding="3" cellspacing="3" align="right">
<tr><th colspan="3"><strong>Uncomfirmed Subscribers:</strong></th></tr>
<?php
if (is_array($unconfirmed)) {
	$alternate = 'alternate';
	foreach ($unconfirmed as $subscriber) {
		echo "<tr class='$alternate'>";
		echo "<td width='5%' align='center'><form method='POST'><input type='hidden' name='email' value='$subscriber' /><input type='hidden' name='s2_admin' value='toggle' /><input type='submit' name='submit' value='<-' /></form></td>";
		echo "<td align='center'><a href='mailto:$subscriber'>$subscriber</a></td>";
		echo "<td width='5%' align='center'><form method='POST'><input type='hidden' name='email' value='$subscriber' /><input type='hidden' name='s2_admin' value='delete' /><input type='submit' name='submit' value=' X ' /></form></td>";
		echo "</tr>";
		("alternate" == $alternate) ? $alternate = "" : $alternate = "alternate";	
	}
} else {
        echo "<tr><td width='100%' align='center' colspan='3'><strong>NONE</strong></td></tr>";
}
?>
</table>
<div style="clear: both;"><p>&nbsp;</p></div>
</div>
<?php
include(ABSPATH . '/wp-admin/admin-footer.php');
// just to be sure
die;
} // end s2_manage

/////////////////////////////
function s2_admin_subscribe() {
global $wpdb, $table_prefix;

$s2_table = $table_prefix . "subscribe2";

foreach (preg_split ("/[\s,]+/", $_POST['addresses']) as $email) {
	if (is_email($email)) {
		if (! $wpdb->get_var("SELECT id FROM $s2_table WHERE email='$email'")) {
			$wpdb->query("INSERT INTO $s2_table (email, active) VALUES ('$email', '1')");
		}
	}
} // foreach...

$_POST['s2_admin'] = '';
s2_manage(); 
die; // just to be sure
} // s2_admin_subscribe

//////////////////////////
function s2_admin_delete() {
global $wpdb, $table_prefix;

$s2_table = $table_prefix . "subscribe2";

if ( (isset($_POST['email'])) && ('' != $_POST['email']) && ( is_email($_POST['email'])) ) {
	$email = $_POST['email'];
	if ($wpdb->get_var("SELECT id FROM $s2_table WHERE email = '$email'")) {
		$result = $wpdb->query("DELETE FROM $s2_table WHERE email = '$email'");
	}
}
$_POST['s2_admin'] = '';
s2_manage();
die; // just to be sure
} // s2_admin_delete;

//////////////////////////
function s2_admin_toggle() {
global $wpdb, $table_prefix;

$s2_table = $table_prefix . "subscribe2";

if ( (isset($_POST['email'])) && ('' != $_POST['email']) && ( is_email($_POST['email'])) ) {
        $email = $_POST['email'];
	$sql = "SELECT active FROM $s2_table WHERE email='$email'";
	$active = $wpdb->get_var($sql);
	if ('0' === $active) {
		$foo = '1';
	} elseif ('1' == $active) {
		$foo = '0';
	}
	if (isset($foo)) {
		$sql = "UPDATE $s2_table SET active='$foo' where email='$email'";
		$result = $wpdb->query($sql);
	}
}
$_POST['s2_admin'] = '';
s2_manage();
die();
} // end s2_admin_toggle

///////////////////////
function s2_admin_send() {
global $dreamhost, $wpdb, $table_prefix, $user_identity, $user_email;

if ( (! isset($_POST['message'])) || ('' == $_POST['message'])) {
	s2_manage();
}

get_currentuserinfo();
$subject = $_POST['s2_subject'];
$mailtext = stripslashes($_POST['message']);

$s2_table = $table_prefix . "subscribe2";

// Set sender details
$headers = "From: " . $user_identity . " <" . $user_email . ">\r\n";

// get the list of active recipients from the database
$sql = "SELECT email FROM $s2_table WHERE active='1'";
$recipients = $wpdb->get_col($sql);
if (count($recipients) == 0) {
        // <admiral ackbar> it's a trap!! </ackbar>
	s2_manage();
}

// BCC all recipients
// with batching for Dreamhost
if (1 == $dreamhost) {
        $count = 1;
        $bcc = '';
        $batch = array();
        foreach ($recipients as $recipient) {
                $recipient = trim($recipient);
                if (! empty($recipient)) {
                        $bcc .= "BCC: " . $recipient . "\r\n";
                }
                if (30 == $count) {
                        $batch[] = $bcc;
			$count = 1;
                        $bcc = '';
                } else {
                        $count++;
                }
        }
        if (0 == count($batch)) {
                // we have less than 30 subscribers, so let's skip batching
                $headers .= $bcc;
                unset($batch);
        }
} else {
	foreach ($recipients as $recipient) {
        	$recipient = trim($recipient);
	        if (! empty($recipient)) {
	                $headers .= "BCC: " . $recipient . "\r\n";
	        }
	}
}

$s2 = get_option('s2_options');
if ('html' == $s2['s2_html']) {
	$mailtext = "<html><head><title>$subject</title></head><body>$mailtext</body></html>";
	$headers .= 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: ' . get_bloginfo('html_type') . '; charset='. get_bloginfo('charset');
} else {
	$headers .= 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/plain; charset='. get_bloginfo('charset');
}

if ( (1 == $dreamhost) && (isset($batch)) ) {
	foreach ($batch as $bcc) {
		$newheaders = $headers . $bcc;
		mail($myemailadd, $subject, $mailtext, $newheaders);
	}
} else {
	mail($user_email, $subject, $mailtext, $headers);
}

$_POST['s2_admin'] = '';
global $admin_sent;
$admin_sent = 'admin_sent';
s2_manage();
die();
} // s2_admin_send()

///////////////////////////
function s2_options_update() {
global $cache_categories;

if (0 == count($cache_categories)) {
        update_categories_cache();
}

$exclude_list = '';

foreach ($cache_categories as $cat) {
        if (isset($_POST[$cat->cat_ID])) {
                if ('' == $exclude_list) {
                        $exclude_list = "$cat->cat_ID";
                } else {
                        $exclude_list .= ",$cat->cat_ID";
                }
        }
}

$s2 = array ('s2_html' => $_POST['s2_html'],
	's2_sender' => $_POST['s2_sender'], 
	's2_excerpt' => $_POST['s2_excerpt'], 
	's2_subject' => $_POST['s2_subject'], 
	's2_mailtext' => $_POST['s2_mailtext'], 
	's2_welcome' => $_POST['s2_welcome'], 
	's2_confirm_subject' => $_POST['s2_confirm_subject'],
	's2_confirm_email' => $_POST['s2_confirm_email'], 
	's2_invalid' => $_POST['s2_invalid'],
	's2_self' => $_POST['s2_self'], 
	's2_already_there' => $_POST['s2_already_there'], 
	's2_not_there' => $_POST['s2_not_there'], 
	's2_add_confirm' => $_POST['s2_add_confirm'], 
	's2_delete_confirm' => $_POST['s2_delete_confirm'], 
	's2_added' => $_POST['s2_added'], 
	's2_deleted' => $_POST['s2_deleted'], 
	's2_subscribed_admin_subject' => $_POST['s2_subscribed_admin_subject'], 
	's2_unsubscribed_admin_subject' => $_POST['s2_unsubscribed_admin_subject'],
	's2_cats_to_skip' => $exclude_list
	);

update_option('s2_options', $s2);

$_POST['s2_admin'] = "";
s2_options();
die;
} // s2_options_update


?>
