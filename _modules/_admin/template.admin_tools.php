<?
//	+function admin_tools
function admin_tools($val, &$data){
	if (access('write', 'admin:settings'))	$data[':admin']['Настройки сервера#ajax_edit']	= getURL('admin_settings');
	if (access('write', 'admin:serverInfo'))$data[':admin']['PHP Info']	= getURL('admin_Info');
	if (access('write', 'admin:SEO'))		$data['SEO#ajax_edit']	= getURL('admin_SEO');
}
//	+function admin_toolsService
function admin_toolsService($val, &$data){
	if (!access('clearCache', '')) return;
	$data['Удалить миниизображения#ajax_dialog']= getURL('', 'clearThumb');
	$data['Обновить документы#ajax_dialog']		= getURL('', 'recompileDocuments');
	$data['Удалить кеш#ajax_dialog']	= getURL('', 'clearCache');
	$data['Пересобрать код#ajax_dialog']	= getURL('', 'clearCode');
}
?>
<?
//	+function script_adminTabs
function script_adminTabs(&$val){
	m('scrupt:jq_ui');
?>
<script>
$(function(){
	$("div.adminTabs").uniqueId().tabs();
});
</script>
<? } ?>