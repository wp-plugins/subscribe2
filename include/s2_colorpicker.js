jQuery(document).ready(function(){var id;jQuery('.colorpickerField').focus(function(){id=this}).live('focusin',function(){jQuery(this).ColorPickerShow()}).ColorPicker({onBeforeShow:function(){jQuery(this).ColorPickerSetColor(this.value)},onShow:function(el){jQuery(el).fadeIn(500);return false},onHide:function(el){jQuery(el).fadeOut(500);return false},onChange:function(hsb,hex,rgb){a=hex.toUpperCase();id.value=a},onSubmit:function(hsb,hex,rgb,el){a=hex.toUpperCase();id.value=a;jQuery('.colorpicker').fadeOut(500);return false}}).keyup(function(){if(this.value.length==6){id.value=this.value.toUpperCase();jQuery(this).ColorPickerSetColor(a)}})});