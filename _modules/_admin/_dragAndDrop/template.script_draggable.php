<?
function script_draggable()
{
	setNoCache();
	define('noCache', true);
	m('script:jq_ui');
	m('fileLoad', 'css/admin.css');
	m('fileLoad', 'script/ajaxDraggable.js');
}
?>
