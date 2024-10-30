(function () {
    tinymce.create('tinymce.plugins.InPlayer', {
        init: function (ed, url) {
            ed.addButton('inplayer', {
                title: 'Your PayWall Assets',
                onclick: function () {
                    tb_show('Your Assets:', 'admin-ajax.php?action=assets_shortcodes&height=600&width=600');
                    jQuery('#TB_window').addClass('inplayerTB');
                }
            });
        },
        createControl: function (n, cm) {
            return null;
        },
        getInfo: function () {
            return {
                longname: 'InPlayer Shortcodes',
                author: 'InPlayer',
                authorurl: 'www.inplayer.com',
                infourl: '',
                version: '1.0'
            };
        }
    });

    tinymce.PluginManager.add('inplayer', tinymce.plugins.InPlayer);
})();