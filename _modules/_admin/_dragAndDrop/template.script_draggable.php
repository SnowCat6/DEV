<?
function script_draggable()
{
	setNoCache();
	define('noCache', true);
	m('script:jq_ui');
	m('fileLoad', 'script/ajaxDraggable.js');
}
?>
