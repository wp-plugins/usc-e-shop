(function() {
    tinymce.create('tinymce.plugins.JeButtons', {
        getInfo : function() {
        return {
            longname : 'Welcart JE CustomButtons',
            author : 'Joint Elements',
            authorurl : '',
            infourl : '',
            version : "1.0"
            };
        },
        init : function(ed, url) {
            var t = this;
            t.editor = ed;
            ed.addCommand('jemember',//member
				function() {
						var str = t._JeMember();
						ed.execCommand('mceInsertContent', false, str);
				});
			ed.addButton('jemember', {
				title : 'メンバー/ノンメンバー・ショートコード', 
				cmd : 'jemember', 
				image : uscesL10n.USCES_PLUGIN_URL + '/images/m_button.png'
			});
            ed.addCommand('jecart',//cart button
				function() {
						var str = t._JeCart();
						ed.execCommand('mceInsertContent', false, str);
				});
			ed.addButton('jecart', {
				title : 'カート投入ボタン・ショートコード', 
				cmd : 'jecart', 
				image : uscesL10n.USCES_PLUGIN_URL + '/images/c_button.png'
			});
		},
		_JeMember : function(d, fmt) {
			str = '[member][/member]<br />\n[nonmember][/nonmenber]';
			return str;
		},
		_JeCart : function(d, fmt) {
			str = '[cart_buttom code="" sku=""]';
			return str;
		}
    });
    tinymce.PluginManager.add('JeButtons', tinymce.plugins.JeButtons);
	
})()