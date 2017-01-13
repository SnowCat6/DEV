<? function editor_code($val, $data)
{
	m("script:jq");
	m("fileLoad", "_editor/codemirror-5.22.0/lib/codemirror.css");
	m("fileLoad", "_editor/codemirror-5.22.0/lib/codemirror.js");
	m("fileLoad", "_editor/codemirror-5.22.0/mode/javascript/javascript.js");
?>
<script src="script/codemirror.js"></script>
<? } ?>