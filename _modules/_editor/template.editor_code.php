<? function editor_code($val, $data)
{
	m("script:jq");
	m("fileLoad", "_editor/ckeditor/plugins/codemirror/js/codemirror.min.js");
	m("fileLoad", "_editor/ckeditor/plugins/codemirror/css/codemirror.min.css");
	m("fileLoad", "_editor/ckeditor/plugins/codemirror/js/codemirror.mode.htmlmixed.min.js");
	m("fileLoad", "_editor/ckeditor/plugins/codemirror/js/codemirror.addons.min.js");
?>
<script src="script/codemirror.js"></script>
<? } ?>