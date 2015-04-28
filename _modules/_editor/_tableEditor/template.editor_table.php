<? function editor_table($val, $data)
{
	module('script:jq_ui');

	module('fileLoad', '_editor/handsontable/script/handsontable.js');
	module('fileLoad', '_editor/handsontable/css/handsontable.full.min.css');

	module('fileLoad', 'script/jQuery.tableedit.js');
	module('fileLoad', 'css/jQuery.tableedit.css');
}
?>