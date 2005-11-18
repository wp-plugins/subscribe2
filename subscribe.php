<?php

// Subscribe2
// Copyright 2004 Scott Merrill skippy@skippy.net
// Distributed under the terms of the GNU Public License
// http://www.gnu.org/copyleft/gpl.html

require_once('./wp-blog-header.php');
$admin = get_userdata(1);

$s2 = get_option('s2_options');

$domain = 'subscribe2';
$locale = get_locale();
$mofile = ABSPATH . "wp-content/plugins/$domain-$locale.mo";
load_textdomain($domain, $mofile);

// the database table to use
$s2_table = $table_prefix . "subscribe2";

$email = (isset($_POST['email'])) ? $_POST['email'] : '';
$action = (isset($_POST['action'])) ? $_POST['action'] : '';
$hash = (isset($_GET['x'])) ? $_GET['x'] : '';

if ('' != $hash) {
	$foo = explode('x', $hash);
	$action = $foo[0];
	$id = intval($foo[2]);
	$sql = "SELECT email FROM " . $s2_table . " WHERE id='" . $id . "'";
	$email = $wpdb->get_var($sql);
	if ('' == $email) {
		main('invalid');
	}
	if ('a' == $action) {
		$result = s2_check($email);
		if ('0' === $result) {
			main('not_there');
		} elseif ($result > 1) {
			s2_confirm($email);
			main('added');
		} else {
			main('already_there');
		}
	} elseif ('d' == $action) {
		s2_delete($email);
		main('deleted');
	} else {
		// safety valve
		main();
	}
}

if ( ('' != $action) && ( ('' == $email) || (! is_email($email)) )) {
	main('invalid');
}

if ( strtolower($admin->user_email) == strtolower($email) ) { 
	main('self'); 
}

if ('add' == $action) { 
	$result = s2_check($email);
	if ( ($result == 1) || ($result > date('Y-m-d', strtotime('-1 day'))) ) {
		main('already_there');
	}
	s2_add($email, $result); 
	s2_send_confirmation ($email, 'add');
	main('add_confirm');
} elseif ('delete' == $action) { 
	if ('0' === s2_check($email)) {
		main('not_there');
	}
	s2_send_confirmation ($email, 'delete');
	main('delete_confirm');
} else {
	main();
}

/////////////////
// *** main() ***
// display the main page
/////////////////
function main($doing = '') {
global $s2;

// Display the page
get_header();
get_sidebar();
// display a message, depending on what was passed to main()
if ('' == $doing) {
	$doing = 'welcome';
}
echo '<div id="content" class="narrowcolumn"><div class="post"><p>' . stripslashes($s2["s2_$doing"]) . "</p>\r\n";
if ( ('not_there' == $doing) || ('already_there' == $doing) || ('self' == $doing) || ('invalid' == $doing) || ('welcome' == $doing) ) {
	echo '<form method="post" action="' . get_bloginfo('home') . '/subscribe.php"><p>';
	echo __('Your email', 'subscribe2') . ':&#160;<input type="text" name="email" value="" size="20" />&#160;<br />';
	echo '<input type="radio" name="action" value="add" checked="checked" />' . __('subscribe', 'subscribe2') . "\r\n";
	echo '<input type="radio" name="action" value="delete" />' . __('unsubscribe', 'subscribe2') . "&#160;\r\n";
	echo '<input type="submit" value="' . __('Send', 'subscribe2') . '!" />';
	echo "</p></form>\r\n";
}
echo '<p><strong>' . __('Note', 'subscribe2') . ':</strong> ' . get_settings('blogname') . ' ' . __('values personal privacy', 'subscribe2') . '.<br />';
_e('This list is used solely to inform you when new posts are added.', 'subscribe2');
echo "<br />\r\n";
_e('Your email address will not be shared with any other party', 'subscribe2');
echo ".</p>\r\n";
echo '<p><a href="' . get_settings('siteurl') . '">' . __('Return to ', 'subscribe2') . get_settings('blogname') . "</a></p>\r\n";
echo "</div></div>\r\n";

get_footer();
die;
} // main()

////////////////////
// *** s2_check() ***
// check whether an email address exists in the database
// return values:
// 0 == not present
// 1 == present, and confirmed
// YYY-MM-DD == present, and not confirmed (date of subscription)
////////////////////
function s2_check ($email = '') {
global $wpdb, $s2_table;

if ( ('' == $email) || (! is_email($email)) ) {
	// no valid email, so bail out
	return '0';
}
$query = "SELECT * FROM " . $s2_table . " WHERE email='" . $email . "'";
$foo = $wpdb->get_row($query);

if ('1' === $foo->active) {
	return '1';
} elseif ('0' === $foo->active) {
	return $foo->date;
} else {
	return '0';
}
} // s2_check

///////////////////
// *** s2_add() ***
// add an email address to the database with a status of "0" (unconfirmed)
///////////////////
function s2_add ($email = '', $status = '0') {
global $wpdb, $s2_table;
if ( ('' == $email) || (! is_email($email)) ) {
	// no valid email, so bail out
	return;
}

if ($status > 1) {
	// this is a known unconfirmed address
	// update their timestamp because we're sending them a new 
	// confirmation email
	$sql = "UPDATE $s2_table SET date = '" . date('Y-m-d') . "' WHERE email = '$email'";
	$result = $wpdb->query($sql);
	return;
}

// add this address
$sql = "INSERT INTO $s2_table (email, active, date) VALUES ('$email', '0', '" . date('Y-m-d') . "')";
$result = $wpdb->query($sql);
} // s2_add

///////////////////////
// *** s2_confirm() ***
// change the status of an email address in the database to "1" (confirmed)
///////////////////////
function s2_confirm ($email = '') {
global $s2, $wpdb, $s2_table;

if ( ('' == $email) || (! is_email($email)) ) {
        // no valid email, so bail out
        return;
}

$admin = get_userdata(1);

if (s2_check($email) > 1) {
	$sql = "UPDATE " . $s2_table . " SET active = '1' WHERE email = '" . $email . "'";
	$result = $wpdb->query($sql);
	$mailtext = __('The following email address has successfully subscribed to your blog', 'subscribe2') . ":\n\n $email\n";
	$mailheaders = "From: \"$admin->user_nickname\" <$admin->user_email>\n";
	$mailheaders .= "MIME-Version: 1.0\n";
	$mailheaders .= "Content-type: text/plain; charset=\"". get_bloginfo('charset') . "\"\n";;
	@wp_mail($admin->user_email, stripslashes($s2['s2_subscribed_admin_subject']), $mailtext, $mailheaders);
}	
} // s2_confirm

//////////////////////
// *** s2_delete() ***
// remove an email address from the database
//////////////////////
function s2_delete ($email = '') {
global $s2, $wpdb, $s2_table;

if ( ('' == $email) || (! is_email($email)) ) {
	// no valid email, so bail out
	return;
}
if ('0' === s2_check($email)) {
	// user does not exist, bail out
	return;
}
$sql = "DELETE FROM " . $s2_table . " WHERE email = '" . $email . "'";
$result = $wpdb->query($sql);

$admin = get_userdata(1);

$mailtext = __('The following email address has successfully unsubscribed from your blog', 'subscribe2') . ":\n\n $email\n";
$mailheaders = "From: \"$admin->user_nickname\" <$admin->user_email>\n";
$mailheaders .= "MIME-Version: 1.0\n";
$mailheaders .= "Content-type: text/plain; charset=\"". get_bloginfo('charset') . "\"\n";
@wp_mail($admin->user_email, $s2['s2_unsubscribed_admin_subject'], $mailtext, $mailheaders);
} // s2_delete

///////////////////////////
// *** s2_send_confirmaion() ***
// send a confirmation email to an address
///////////////////////////
function s2_send_confirmation ($email = '', $action = '') {
global $wpdb, $s2_table, $s2;

if ( ('' == $email) || (! is_email($email)) || ('' == $action) ) {
        // no valid email or action, so bail out
        return;
}

$sql = "SELECT id FROM " . $s2_table . " WHERE email = '" . $email . "'";
$id = $wpdb->get_var($sql);

if ('add' == $action) {
	// link to confirm their address
	$link = get_settings('siteurl') . "/" . basename($_SERVER['PHP_SELF']) . "?x=ax" . md5($email) . "x" . $id;
} elseif ('delete' == $action) {
	// link to confirm their address
	$link = get_settings('siteurl') . "/" . basename($_SERVER['PHP_SELF']) . "?x=dx" . md5($email) . "x" . $id;
}

$admin = get_userdata(1);

$body = stripslashes(str_replace("LINK", $link, $s2['s2_confirm_email']));
$body = str_replace("BLOGNAME", get_settings('blogname'), $body);
$body = str_replace("MYNAME", $admin->user_nickname, $body);
$body = str_replace("EMAIL", $admin->user_email, $body);

$subject = stripslashes(str_replace("BLOGNAME", get_settings('blogname'), $s2['s2_confirm_subject']));
$subject = str_replace("MYNAME", $admin->user_nickname, $subject);
$subject = str_replace("EMAIL", $admin->user_email, $subject);

$mailheaders .= "MIME-Version: 1.0\n";
$mailheaders .= "Content-type: text/plain; charset=\"". get_bloginfo('charset') . "\"\n";
$mailheaders = "From: $admin->user_nickname <$admin->user_email>";

mail ($email, $subject, $body, $mailheaders);
} // s2_send_confirmation()

?>
