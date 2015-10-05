<? function module_page_404($val, &$ev)
{
	define('statPages', true);
	m('page:title', 'Страница 404');
?>
<? ob_start() ?>
<div class="page404">
<module:read:page404 default="@">

<p>Мы обновили сайт, возможно вы перешли по старой ссылке.</p>
<p>Если это так, то зайдите на <a href="{{url:#}}">главную страницу сайта</a>, и найдите то, что вас интересует.</p>

</module:read:page404>
</div>
<? $ev['content']	= ob_get_clean(); ?>
<? } ?>