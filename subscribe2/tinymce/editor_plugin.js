tinyMCE.importPluginLanguagePack('subscribe2quicktags', 'en');

var TinyMCE_Subscribe2Quicktags = {
	getInfo : function() {
		return {
			longname : "Subscribe2 Quicktag",
			author : 'MattyRob',
			authorurl : 'http://subscribe2.wordpress.com/',
			infourl : 'http://subscribe2.wordpress.com/',
			version : tinyMCE.majorVersion + '.' + tinyMCE.minorVersion
		};
	},
	getControlHTML : function(cn) {
		switch (cn) {
			case 'subscribe2quicktags':
				buttons = tinyMCE.getButtonHTML('subscribe2', 'lang_subscribe2quicktags_subscribe2', '{$pluginurl}/../s2_button.png', 'subscribe2');
				return buttons;
		}
		return '';
	},
	execCommand : function(editor_id, element, command, user_interface, value) {
		switch (command) {
			case 'subscribe2':
				s2_insert_token();
				return true;
		}
		return false;
	},
};

tinyMCE.addPlugin('subscribe2quicktags', TinyMCE_Subscribe2Quicktags);