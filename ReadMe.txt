=== Subscribe2 ===
Contributors: MattyRob, Skippy, Ravan
Donate link: http://subscribe2.wordpress.com/donate/
Tags: posts, subscription, email
Requires at least: 2.0.x
Tested up to: 2.1

Sends a list of subscribers an email notification when new posts are published to your blog

== Description ==

Subscribe2 provides a comprehensive subscription management system for WordPress blogs that sends email notifications on a per-post or periodical basis to a list of subscribers when you publish new content to your blog. The plugin also handles subscription requests allowing users to publically subscribe by submitting their email address in an easy to use form or to register with your blog which enables greater flexibility for email content for the subscriber. Admins are given control over the presentation of the email notifications, can bulk manage subscriptions for users and manually send email notices to subscribers.

Subscribe2 supports two classes of subscribers: the general public, and registered users of the blog.  The general public may subscribe and unsubscribe.  They will receive an email notification whenever a new post is made (unless that post is assigned to one of the excluded categories you defined).  The general public will receive a plaintext email with an excerpt of the post: either the excerpt you created when making the post, the portion of text before a <!--more--> tag (if present), or the first 55 words of the post.

Registered users of the blog can elect to receive email notifications for specific categories.  The Users->Subscription menu item will allow them to select the delivery format (plaintext or HTML), amount of message (excerpt or full post), and the categories to which they want to subscribe.  You, the blog owner, have the option (Options->Subscribe2) to allow registered users to subscribe to your excluded categories or not.

When you post a new item, subscribe2 will generate (at least) one email for each of the following combinations of subscribers:

* plaintext excerpt
* plaintext full post
* HTML full post

So for each new post you make, you may receive up to three emails.

== Installation ==

1. Copy buttonsnap.php into your /wp-content/plugins directory.
2. Copy the entire /subscribe2/ directory into your /wp-content/plugins/ directory.
3. Activate the plugin.
4. Click the "Options" admin menu link, and select "Subscribe2".
5. Configure the options to taste, including the email template and any categories which should be excluded from notification
6. Click the "Manage" admin menu link, and select "Subscribers".
7. Manually subscribe people as you see fit.
8. Create a WordPress Page (http://codex.wordpress.org/Pages) to display the subscription form.  When creating the page, you may click the "S2" button on the QuickBar to automatically insert the subscribe2 token.  Or, if you prefer, you may manually insert the subscribe2 token:
     <!--subscribe2-->
     ***Ensure the token is on a line by itself and that it has a blank line above and below.***
This token will automatically be replaced by the subscription form, and will display all messages as necessary.
9. In the subscribe2.php file define S2PAGE to point at your WordPress page created in step 8.

== Frequently Asked Questions ==

= Which version should I be using, I'm on WordPress x.x.x? =
WordPress 2.0.x will be supported until 2010. Subscribe2 will remain supported (but not actively developed) until WordPress 2.0.x is no longer supported. For WordPress 2.0.x use the Subscribe code from the 2.x stable.

WordPress 2.1.x and 2.2.x are also supported. For the latest information visit http://subscribe2.wordpress.com. For versions of WordPress use Subscribe2 code from the 3.x stable.

Finally, there is a development version for WordPress 2.3 but as WordPress 2.3 is not recommended for active websites you should only get this code from the branch if you are testing it.

= How do I upgrade from a previous version? =
If you are upgrading from any 2.1.x version of subscribe2, please delete both the /subscribe.php and /wp-content/plugins/subscribe2.php files before copying the new files into place.  Your subscriber list will remain intact, and your options should be preserved. Now create a WordPress Page as described above.

= Why doesn't the form appear in my WordPress page? =
This is usually caused by one of two things. Firstly, it is possible that the form is there but as you haven't logged out of WordPress yourself you are seeing a message about managing your profile instead. Try logging out and see if it appears.

Secondly, make sure that the token (<!--subscribe2-->) is correctly entered in your page with a blank line above and below. The easient way to do this is to deactivate the plugin, visit your WordPress page and view the source. The token should be contained in the source code of the page. If it is not there you either have not correctly entered the token or you have another plguin that is stripping the token from the page code.

= Some or all email notifications fail to send, why?  =
In the first instance check this with your hosting provider, they have access to your server logs and will be able to tell you where and why emails are being blocked.

Some hosting providers place a restriction on the maximum number of recipients in any one email message.  For example, the venerable Dreamhost (http://www.dreamhost.com/) does not allow any message to contain more than 30 recipients.

Subscribe2 provides a facility to work around this restriction by sending batches of emails.  To enable this feature, edit subscribe2.php in a text editor and go to line 35:
     define('BCCLIMIT', 0);
Change the 0 to the number of allowed outgoing email recipients as set by your host.

Reminder: because subscribe2 places all recipients in BCC fields, and places the blog admin in the TO field, the blog admin will receive one email per batched delivery.  So if you have 90 subscribers, the blog admin should receive three post notification emails, one for eah set of 30 BCC recipients.

Batches will occur for each group of message as described above.  A site on Dreamhost with many public and registered subscribers could conceivably generate a lot of email for your own inbox.

== Screenshots ==

1. The Write->Subscribers admin page generated by the plugin.
2. The Manage->Subscribers admin page generated by the plugin.
3. The Users->Subscriptions admin page generated by the plugin.
4. The Options->Subscribe2 admin page generated by the plugin.

== Version History ==

Version 2.5 by Matthew Robinson

* Added functionality to Bulk Manage registered users subscriptions

Version 2.4 by Matthew Robinson

* Added functionality to block user specified domains from public subscription

Version 2.3 by Matthew Robinson

* Added functionality to allow for Subscribe2 Sidebar Widget
* Added functionality to block public email subscriptins from domains defined under Options
* Added functionality to send an email reminder to all unconfirmed public subscriber
* Added removal of html entities (for example &copy;) from plaintext emails
* Replaced spaces with tabs in Plugin format
* Minor changes to admin layout to match WordPress admin function layout

Version 2.2

* By Scott Merrill, see http://www.skippy.net/blog/category/wordpress/plugins/subscribe2/