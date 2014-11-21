<? function module_page_404($val, &$ev)
{
	m('page:title', 'Страница 404');
?>
<? ob_start() ?>
<div class="page404">
<? module('read:page404')?>
</div>
<? $ev['content']	= ob_get_clean(); ?>
<? } ?>