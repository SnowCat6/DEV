<?
function module_style($val, $data)
{
	setCacheData("style:$val", $data);

	$styles	= config::get(':style');
	if (!is_null($styles[$val])) return;
	
	$styles[$val] = '';
	config::set(':style', $styles);
	
	$fn = getFn("style_$val");				//	Получить функцию (и загрузка файла) модуля
	ob_start();
	if ($fn) $fn($data);
	
	$styles	= config::get(':style');
	$styles[$val] 	= ob_get_clean();
	config::set(':style', $styles);
}
function module_script($val, $data)
{
	setCacheData("script:$val", $data);

	$scripts	= config::get(':script');
	if (!is_null($scripts[$val])) return;
	
	$scripts[$val] = '';
	config::set(':script', $scripts);
	
	$fn		= getFn("script_$val");				//	Получить функцию (и загрузка файла) модуля
	
	//	Присоеденить стиль, если есть такой
	m("style:$val", $data);
	
	ob_start();
	if ($fn) $fn($data);
	//	Для сохранения зависимостей скрипты вызванные ранее должны быть первыми
	$scripts	= config::get(':script');
	$scripts[$val] 	= ob_get_clean();
	config::set(':script', $scripts);
}
function hasScriptUser($val){
	$scripts	= config::get(':script');
	return $scripts[$val];
}
function isModernBrowser()
{
	return true;
	$agent		= strtolower($_SERVER['HTTP_USER_AGENT']);
	$browsers	= array("firefox", "opera", "chrome", "safari"); 
	foreach($browsers as $browser){
		if (strpos($agent, $browser)) return true;
	}
	return false;
}
function script_jq($val)
{
	$jQuery	= getCacheValue('jQuery');
	if (isModernBrowser()) $ver = $jQuery['jQueryVersion2'];
	else $ver = $jQuery['jQueryVersion'];
	
	if (!testValue('ajax'))
		return m('scriptLoad', "script/$ver");
	
	ob_start();
?>
<script>
var jQuerySourcePath = "";
/*<![CDATA[*/
function loadScriptFile(filename){
	document.write('<' + 'script type="text/javascript" src="' + filename + '">' + '<' + '/script>');
}
if (typeof jQuery == 'undefined'){
	loadScriptFile('<?= globalRootURL?>/script/<?= $ver ?>');
}
/*]]>*/
</script>
<?	m('page:display:head', ob_get_clean()); ?>
<? } ?>

<? function script_jq_modern($val){ ?>
<? } ?>


<? function script_cookie($val){
	m('script:jq');
	m('scriptLoad', 'script/jquery.cookie.min.js');
}
?>

<? function script_reload($val){ ?>
<script>
document.location.reload();
</script>
<? } ?>
