<? function module_page_404($val){
	$template	= $GLOBALS['_CONFIG']['page']['template'];
	if ($template != 'page.default') return;
	m('page:title', 'Страница 404');
?>
<div class="page404">
<? module('read:page404')?>
</div>
<? } ?>