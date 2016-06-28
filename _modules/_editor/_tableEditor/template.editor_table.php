<? function editor_table($val, $data)
{
	module('script:jq_ui');

//	module('fileLoad', '_editor/handsontable/script/handsontable.js');
//	module('fileLoad', '_editor/handsontable/css/handsontable.full.min.css');
	m('fileLoad',  '_editor/handsontable-pro-1.4.1/handsontable.full.min.js');
	m('fileLoad',  '_editor/handsontable-pro-1.4.1/handsontable.full.min.css');

	module('fileLoad', 'script/jQuery.tableedit.js');
	module('fileLoad', 'css/jQuery.tableedit.css');
}
?>