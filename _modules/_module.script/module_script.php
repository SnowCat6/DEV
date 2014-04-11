<?
function module_style(&$val)
{
	$style = &$GLOBALS['_SETTINGS']['style'][$val];
	if (!is_null($style)) return;
	$style = '';
	
	$fn = getFn("style_$val");				//	Получить функцию (и загрузка файла) модуля
	ob_start();
	if ($fn) $fn($val);
	$style	= ob_get_clean();
}
function module_script(&$val)
{
	$script = &$GLOBALS['_SETTINGS']['script'][$val];
	if (!is_null($script)) return;
	
	$script = '';
	$fn		= getFn("script_$val");				//	Получить функцию (и загрузка файла) модуля
	
	//	Присоеденить стиль, если есть такой
	m("style:$val");
	
	ob_start();
	if ($fn) $fn($val);
	//	Для сохранения зависимостей скрипты вызванные ранее должны быть первыми
	unset($GLOBALS['_SETTINGS']['script'][$val]);
	//	Пересоздать значение
	$GLOBALS['_SETTINGS']['script'][$val]	= ob_get_clean();
}
function module_scriptLoad(&$val, &$data)
{
	if (!$data) return;
	$GLOBALS['_SETTINGS']['scriptLoad'][$data] = $data;
}

function hasScriptUser($val){
	return @$GLOBALS['_SETTINGS']['script'][$val];
}
function isModernBrowser()
{
	$agent		= strtolower($_SERVER['HTTP_USER_AGENT']);
	$browsers	= array("firefox", "opera", "chrome", "safari"); 
	foreach($browsers as $browser){
		if (strpos($agent, $browser)) return true;
	}
	return false;
}
function script_jq($val){
	$jQuery	= getCacheValue('jQuery');
	if (isModernBrowser()) $ver = $jQuery['jQueryVersion2'];
	else $ver = $jQuery['jQueryVersion'];
?>
<? if (testValue('ajax')){ ?>
<script language="javascript" type="text/javascript">
/*<![CDATA[*/
if (typeof jQuery == 'undefined'){  
	document.write('<' + 'script type="text/javascript" src="<?= globalRootURL?>/script/<?= $ver ?>"></script' + '>');
}
 /*]]>*/
</script>
<? return; } ?>
<? m('scriptLoad', "script/$ver"); ?>
<? } ?>


<? function script_cookie($val){ module('script:jq'); ?>
<script type="text/javascript" src="<?= globalRootURL?>/script/jquery.cookie.min.js"></script>
<? } ?>

<? function script_CrossSlide($val){
	m('script:jq');
	m('script::load', "script/jquery.cross-slide.min.js");
} ?>


