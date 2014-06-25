<?php
  // Extension Configuration
  //
  $plugin_slug = basename(dirname(__FILE__));
  $menu_slug = 'readygraph-app';
  $main_plugin_title = 'Subscribe2';
  
  // Email Subscription Configuration
  //
  $url = S2URL;
  $app_id = get_option('readygraph_application_id', '');
  /*$readygraph_email_subscribe = <<<EOF
  function subscribe(email, first_name, last_name) {
    function submitPostRequest(url, parameters) 
    {
      http_req = false;
      if (window.XMLHttpRequest) 
      {
        http_req = new XMLHttpRequest();
        if (http_req.overrideMimeType) http_req.overrideMimeType('text/html');
      } 
      else if (window.ActiveXObject) 
      {
        try { http_req = new ActiveXObject("Msxml2.XMLHTTP"); } 
        catch (e) {
          try { http_req = new ActiveXObject("Microsoft.XMLHTTP"); } 
          catch (e) { }
        }
      }
      if (!http_req) return;
      http_req.onreadystatechange = eemail_submitresult;
      http_req.open('POST', url, true);
      http_req.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      http_req.send(parameters);
    }
    
    var rg_url = 'https://readygraph.com/api/v1/wordpress-enduser/';
    var str = "email=" + encodeURI(email) + "&app_id=$app_id";
		if ('$app_id') submitPostRequest(rg_url, str);
    
    str= "txt_email_newsletter="+ encodeURI(email) + "&action=" + encodeURI(Math.random());
    submitPostRequest('$url/eemail_subscribe.php', str);
  }
EOF;
*/
  // RwadyGraph Engine Hooker
  //
  include_once('extension/readygraph/extension.php');
/*    
  function add_readygraph_admin_menu_option() 
  {
    global $plugin_slug, $menu_slug;
    append_submenu_page($plugin_slug, 'Readygraph App', __( 'Readygraph App', $plugin_slug), 'administrator', $menu_slug, 'add_readygraph_page');
  }
  
  function add_readygraph_page() {
    include_once('extension/readygraph/admin.php');
  }
*/  
  function on_plugin_activated_readygraph_s2_redirect(){
	update_option('readygraph_connect_notice','true');
	global $menu_slug;
    $setting_url="admin.php?page=$menu_slug";    
    if (get_option('rg_s2_plugin_do_activation_redirect', false)) {  
      delete_option('rg_s2_plugin_do_activation_redirect'); 
      wp_redirect(admin_url($setting_url)); 
    }  
  }
  
 // remove_action('admin_init', 'on_plugin_activated_redirect');
  
//  add_action('admin_menu', 'add_readygraph_admin_menu_option');
  add_action('admin_notices', 'add_readygraph_plugin_warning');
  add_action('wp_head', 'readygraph_client_script_head');
  add_action('admin_init', 'on_plugin_activated_readygraph_s2_redirect');

?>