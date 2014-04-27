function htmlEncode( html ) {
	return String(html)
			.replace(/&/g, '&amp;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;');
};

CKEDITOR.config.imageselect_button_label = 'Картинки';
CKEDITOR.config.imageselect_button_title = 'Вставить картинку';
CKEDITOR.config.imageselect_button_voice = 'Вставить картинку';

if (typeof window.globalFolders == 'undefined') window.globalFolders = new Array();

CKEDITOR.plugins.add('imageselect',
{
	requires : ['richcombo'],
	init : function( editor )
	{
		try{
			var element = $(editor.element);
			var cfg = $.parseJSON(element.attr("rel"));
			var folder = cfg["folder"];
			if (!folder) return;
			editor.config.cfg = cfg;

			if (!window.globalFolders[folder])
			{
				window.globalFolders[folder] = new Array();
				$.ajax('file_images_get.htm?fileImagesPath=' + folder).done(function(data){
					window.globalFolders[folder] = $.parseJSON(data);
				});
			}
		}catch(e){
			return;
		}

		var config = editor.config;
		// Gets the list of insertable strings from the settings.
		var strings = config.imageselect_strings;
		// add the menu to the editor
		editor.ui.addRichCombo('strinsert',
		{
			label: 		config.imageselect_button_label,
			title: 		config.imageselect_button_title,
			voiceLabel: config.imageselect_button_voice,
			toolbar: 	'insert',
			className: 	'cke_format',
			multiSelect:false,
			panel:
			{
				css: [ editor.config.contentsCss, CKEDITOR.skin.getPath('editor') ],
				voiceLabel: editor.lang.panelVoiceLabel
			},

			init: function()
			{
				var cfg = editor.config.cfg;
				var folder = cfg["folder"];

				var folders = window.globalFolders[folder];
				for(var group in folders)
				{
					this.startGroup( group );
					var files = folders[group];
					for(var file in files)
					{
						value = files[file]['size']+':'+files[file]['path'];
						this.add(value, file, files[file]['size']);
					}
				}
			},

			onClick: function( value )
			{
				var o = value.split(':', 2);
				var size = o[0].split('x');
				var path = o[1];
				
				var value = '<img src="' + path + '"'
					+ ' width="' + size[0] + '"'
					+ ' height="' + size[1] + '"'
					+ ' />';
				
				editor.focus();
				editor.fire( 'saveSnapshot' );
				editor.insertHtml(value);
				editor.fire( 'saveSnapshot' );
			},

		});
	}
});