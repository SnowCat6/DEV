CKEDITOR.plugins.add( 'inlinesave',
{
	init: function( editor )
	{
		try{
			var element = $(editor.element);
			var cfg = $.parseJSON(element.attr("rel"));
			var action = cfg["action"];
			if (!action) return;
		}catch(e){
			return;
		}
		
		editor.addCommand( 'inlinesave',
			{
				exec : function( editor )
				{
					var element = $(editor.element);
					var cfg = $.parseJSON(element.attr("rel"));
					var action = cfg["action"];
					var field = cfg['dataName'];
					if (!field) field = 'editorData';
					cfg[field] = editor.getData();

					var cmd = editor.getCommand( 'inlinesave' );
					cmd.disable();
					jQuery.ajax({
						type: "POST",
						//Specify the name of the file you wish to use to handle the data on your web page with this code:
						//<script>var dump_file="yourfile.php";</script>
						//(Replace "yourfile.php" with the relevant file you wish to use)
						//Data can be retrieved from the variable $_POST['editabledata']
						//The ID of the editor that the data came from can be retrieved from the variable $_POST['editorID']
						url: action,
						data: cfg
					})
					.done(function (data, textStatus, jqXHR) {
						cmd.enable();
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
			command: 'inlinesave',
			icon: this.path + 'images/inlinesave.png'
		} );
	}
} );