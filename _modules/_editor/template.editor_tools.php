<? function editor_tools($name, $data){
	$folder	= $data['folder'];
?>
<link rel="stylesheet" type="text/css" href="css/editorTools.css">
<div class="adminEditorTools">
    <div class="adminEditorItem">{{editor:images=$folder}}</div>
    <div class="adminEditorItem">{{snippets:tools:$name}}</div>
</div>
<? } ?>