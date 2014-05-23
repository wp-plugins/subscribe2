<?php
if ( !function_exists('add_action') ) {
	exit();
}

if ( isset($_GET["app_id"]) && is_numeric($_GET["app_id"]) ) {
	$this->subscribe2_options['readygraph_id'] = $_GET["app_id"];
	update_option('subscribe2_options', $this->subscribe2_options);
}

if ( isset($this->subscribe2_options['readygraph_id']) && $this->subscribe2_options['readygraph_id'] > 0 ) {
?>
	<div class="wrap">
		<div id="icon-plugins" class="icon32"></div>
		<h2><?php _e('Final Step: Place the widget on your site to get started', 'subscribe2'); ?></h2>
		<h3><?php _e('Drag the widget to a prominent place to maximize signups', 'subscribe2'); ?>.</h3>
		<a class="button add-new-h2" style="text-shadow:none;background:#36812E;background-color:#36812E;color:white;" href="widgets.php"><?php _e('Place Widget Now', 'subscribe2'); ?></a>
		<p><?php _e('Tips', 'subscribe2'); ?></p>
		<ul>
		<li>-<?php printf( __('Already have the widget in place? Manage your ReadyGraph account <a href="%s">here</a>', 'subscribe2'), esc_url('http://readygraph.com/application/insights/') ); ?></li>
		<li>-<?php printf( __('Need help? Email <a href="%1$s">%2$s</a> or click <a href="%3$s">here</a>', 'subscribe2'), esc_attr('mailto:nick@readygraph.com'), esc_attr('nick@readygraph.com'), esc_url('http://readygraph.com') ); ?></li>
		</ul>
	</div>

	<?php } else { ?>

	<div class="wrap">
	<div id="icon-plugins" class="icon32"></div>
		<h2><?php _e('Subscribe2, Now with Readygraph', 'subscribe2'); ?></h2>
		<h3><?php _e('Activate Readygraph features to optimize Subscribe2 functionality', 'subscribe2'); ?></h3>
		<p style="display:none;color:red;" id="error"></p>
		<div class="register-left" style="float: left; width:25%;">
		<div class="form-wrap">
			<h3><?php _e('Free Signup', 'subscribe2'); ?></h3>
			<p>
			<label for="register-url"><?php _e('Site URL', 'subscribe2'); ?></label>
			<input type="text" id="register-url" name="eemail_on_homepage">
			</p>

			<p>
			<label for="register-name"><?php _e('Name', 'subscribe2'); ?></label>
			<input type="text" id="register-name" name="eemail_on_homepage">
			</p>

			<p>
			<label for="register-email"><?php _e('Email', 'subscribe2'); ?></label>
			<input type="text" id="register-email" name="eemail_on_homepage">
			</p>
			<p>
			<label for="register-password"><?php _e('Password', 'subscribe2'); ?></label>
			<input type="password" id="register-password" name="eemail_on_homepage">
			</p>
			<p>
			<label for="register-password1"><?php _e('Confirm Password', 'subscribe2'); ?></label>
			<input type="password" id="register-password1" name="eemail_on_homepage">
			</p>

			<p style="max-width:180px;font-size: 10px;"><?php printf( __('By signing up, you agree to our <a href="%1$s">Terms of Service</a> and <a href="%2$s">Privacy Policy</a>', 'subscribe2'), esc_url('http://www.readygraph.com/tos'), esc_url('http://readygraph.com/privacy/') ); ?>.</p>
			<p style="margin-top:10px;">
			<input type="submit" style="text-shadow:none;background:#36812E;width:193px;background-color:#36812E;color:white;" value="Continue to place widget" id="register-app-submit" class="button" name="Submit">
			</p>
		</div>

		</div>
		<div class="register-mid" style="float: left;width:25%;">
			<div class="form-wrap">
			<p>
			<h3><?php _e('Already a member?', 'subscribe2'); ?></h3>
			<label for="signin-email"><?php _e('Email', 'subscribe2'); ?></label>
			<input type="text" id="signin-email" name="eemail_on_homepage">
			</p>
			<p>
			<label for="signin-password"><?php _e('Password', 'subscribe2'); ?></label>
			<input type="password" id="signin-password" name="eemail_on_homepage">
			</p>
			<p style="padding-top:10px;">
			<input type="submit" style="width:193px;color:" value="Sign In" id="signin-submit" class="button add-new-h2" name="Submit">
			</p>
		</div>
		</div>
		<div class="register-right" style="float:left;width:35%;">
			<div class="form-wrap alert" style="font-size: 16px;background-color: #F9F8F3;border: 2px solid #EBECE8;border-radius: 6px;padding: 16px 45px 16px 30px;">
			<p>
			<h3><?php _e('Signup For These Benefits', 'subscribe2'); ?>:</h3>
			<p>-<?php _e('Grow your subscribers faster', 'subscribe2'); ?></p>
			<p>-<?php _e('Engage users with automated email updates', 'subscribe2'); ?></p>
			<p>-<?php _e('Enhanced email deliverablility', 'subscribe2'); ?></p>
			<p>-<?php _e('Track performace with user-activity analytics', 'subscribe2'); ?></p>
			</div>
		</div>
	<?php } ?>

<script type="text/javascript">
jQuery('#signin-submit').click(function(e){
	var email = jQuery('#signin-email').val();
	var password = jQuery('#signin-password').val();
	if (!email) {
		alert('email is empty!');
		return;
	}
	if (!password) {
		alert('password is empty');
		return;
	}
	jQuery.ajax({
		type: 'GET',
		url: 'https://readygraph.com/api/v1/wordpress-login/',
		data: {
			'email' : email,
			'password' : password
		},
		dataType: 'json',
		success: function(response) {
			if (response.success) {
				var pathname = window.location.href;
				window.location = pathname + "&app_id="+response.data.app_id;
			} else {
				jQuery('#error').text(response.error);
				jQuery('#error').show();
			}
		}
	});
});

jQuery('#register-app-submit').click(function(e){
	var email = jQuery('#register-email').val();
	var site_url = jQuery('#register-url').val();
	var first_name = jQuery('#register-name').val();
	var password = jQuery('#register-password').val();
	var password2 = jQuery('#register-password1').val();
	if (!site_url) {
		alert('Site Url is empty.');
		return;
	}
	if (!email) {
		alert('Email is empty.');
		return;
	}
	if ( !password || password != password2 ) {
		alert('Password is not matching.');
		return;
	}

	jQuery.ajax({
		type: 'POST',
		url: 'https://readygraph.com/api/v1/wordpress-signup/',
		data: {
			'email' : email,
			'site_url' : site_url,
			'first_name': first_name,
			'password' : password,
			'password2' : password2,
			'source' : 'subscribe2'
		},
		dataType: 'json',
		success: function(response) {
			if (response.success) {
				var pathname = window.location.href;
				window.location = pathname + "&app_id="+response.data.app_id;
			} else {
				jQuery('#error').text(response.error);
				jQuery('#error').show();
			}
		}
	});

});
</script>
<?php
include(ABSPATH . 'wp-admin/admin-footer.php');
// just to be sure
die;
?>