<?php
if ( !function_exists('add_action') ) {
	exit();
}

if ( isset($_GET["app_id"]) && is_numeric($_GET["app_id"]) ) {
	update_option('readygraph_api', intval($_GET["app_id"]));
}

$readygraph_api = get_option('readygraph_api');
if ( isset($readygraph_api) && $readygraph_api > 0 ) {
?>
	<div class="wrap">
		<div id="icon-plugins" class="icon32"></div>
		<h2><?php _e('Final Step: Place the widget on your site to get started', 'subscribe2'); ?></h2>
		<h3><?php _e('Drag the widget to a prominent place to maximize signups', 'subscribe2'); ?>.</h3>
		<p class="submit"><a class="button-primary" href="widgets.php"><?php _e('Place Widget Now', 'subscribe2'); ?></a></p>
		<p><?php _e('Tips', 'subscribe2'); ?></p>
		<ul>
		<li>-<?php printf( __('Already have the widget in place? Manage your ReadyGraph account <a href="%s">here</a>', 'subscribe2'), esc_url('http://readygraph.com/application/insights/') ); ?></li>
		<li>-<?php printf( __('Need help? Email <a href="%1$s">%2$s</a> or click <a href="%3$s">here</a>', 'subscribe2'), esc_attr('mailto:nick@readygraph.com'), esc_attr('nick@readygraph.com'), esc_url('http://readygraph.com') ); ?></li>
		</ul>

		<p class="submit"><a class="button-secondary" href="<?php echo admin_url('admin.php?page=s2_readygraph&app_id=0');?>"><?php _e('Unlink ReadyGraph Account', 'subscribe2'); ?></a></p>
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
			<input type="text" id="register-url" name="register-url">
			</p>

			<p>
			<label for="register-name"><?php _e('Name', 'subscribe2'); ?></label>
			<input type="text" id="register-name" name="register-name">
			</p>

			<p>
			<label for="register-email"><?php _e('Email', 'subscribe2'); ?></label>
			<input type="text" id="register-email" name="register-email">
			</p>

			<p>
			<label for="register-password"><?php _e('Password', 'subscribe2'); ?></label>
			<input type="password" id="register-password" name="register-password">
			</p>

			<p>
			<label for="register-password1"><?php _e('Confirm Password', 'subscribe2'); ?></label>
			<input type="password" id="register-password1" name="register-password1">
			</p>

			<p style="max-width:180px;font-size: 10px;"><?php printf( __('By signing up, you agree to our <a href="%1$s">Terms of Service</a> and <a href="%2$s">Privacy Policy</a>', 'subscribe2'), esc_url('http://www.readygraph.com/tos'), esc_url('http://readygraph.com/privacy/') ); ?>.</p>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Continue to place widget', 'subscribe2'); ?>" id="register-app-submit" class="button" name="Submit">
			</p>
			</div>
		</div>

		<div class="register-mid" style="float: left;width:25%;">
			<div class="form-wrap">
			<h3><?php _e('Already a member?', 'subscribe2'); ?></h3>
			<p>
			<label for="signin-email"><?php _e('Email', 'subscribe2'); ?></label>
			<input type="text" id="signin-email" name="signin-email">
			</p>

			<p>
			<label for="signin-password"><?php _e('Password', 'subscribe2'); ?></label>
			<input type="password" id="signin-password" name="signin-password">
			</p>

			<p class="submit">
			<input type="submit" value="<?php _e('Sign In', 'subscribe2'); ?>" id="signin-submit" class="button-primary" name="Submit">
			</p>
			</div>
		</div>
		<div class="register-right" style="float:left;width:35%;">
			<div class="form-wrap alert" style="font-size: 16px;background-color: #F9F8F3;border: 2px solid #EBECE8;border-radius: 6px;padding: 16px 45px 16px 30px;">
			<br>
			<h3><?php _e('Signup For These Benefits', 'subscribe2'); ?>:</h3>
			<p>-<?php _e('Grow your subscribers faster', 'subscribe2'); ?></p>
			<p>-<?php _e('Engage users with automated email updates', 'subscribe2'); ?></p>
			<p>-<?php _e('Enhanced email deliverablility', 'subscribe2'); ?></p>
			<p>-<?php _e('Track performace with user-activity analytics', 'subscribe2'); ?></p>
			</div>
		</div>
	</div>
	<?php }
include(ABSPATH . 'wp-admin/admin-footer.php');
// just to be sure
die;
?>