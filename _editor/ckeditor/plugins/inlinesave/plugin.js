CKEDITOR.plugins.add( 'inlinesave',
{
	init: function( editor )
	{
		try{
			var element = $(editor.element);
			var cfg = $.parseJSON(element.attr("rel"));
			var action = cfg["action"];
			if (!action) return;
			editor.config.cfg = cfg;
		}catch(e){
			return;
		}
		editor.on('change', function(){
			var cmd = editor.getCommand( 'inlinesave' );
			cmd.enable();
		});
		
		editor.addCommand( 'inlinesave',
			{
				exec : function( editor )
				{
					var cfg = editor.config.cfg;
					var action = cfg["action"];
					var field = cfg['dataName'];
					if (!field) field = 'editorData';
					cfg[field] = editor.getData();

					var cmd = editor.getCommand( 'inlinesave' );
					cmd.disable();
					jQuery.ajax({
						type: "POST",
						url: action,
						data: cfg
					})
					.done(function (data, textStatus, jqXHR) {
						var element = $(editor.element);
						element.attr("contenteditable", false);
						editor.destroy();
					})
					.fail(function (jqXHR, textStatus, errorThrown) {
						cmd.enable();
						alert("Error saving content.");
					});   
				}
			});
		editor.ui.addButton( 'Inlinesave',
		{
			label: 'Save',
			toolbar: 	'document',
			command: 'inlinesave',
			icon: this.path + 'images/inlinesave.png'
		} );
	}
} );