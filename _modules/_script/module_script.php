<?
function module_style(&$val, &$data)
{
	$style = &$GLOBALS['_SETTINGS']['style'][$val];
	if (!is_null($style)) return;
	$style = '';
	
	$fn = getFn("style_$val");				//	Получить функцию (и загрузка файла) модуля
	ob_start();
	if ($fn) $fn($data);
	$style	= ob_get_clean();
}
function module_script(&$val, &$data)
{
	$script = &$GLOBALS['_SETTINGS']['script'][$val];
	if (!is_null($script)) return;
	
	$script = '';
	$fn		= getFn("script_$val");				//	Получить функцию (и загрузка файла) модуля
	
	//	Присоеденить стиль, если есть такой
	m("style:$val", $data);
	
	ob_start();
	if ($fn) $fn($data);
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
<script>
/*<![CDATA[*/
function loadScriptFile(filename)
{
	var fileref=document.createElement('script')
	fileref.setAttribute("type","text/javascript")
	fileref.setAttribute("src", filename);
	document.getElementsByTagName("head")[0].appendChild(fileref);
}

if (typeof jQuery == 'undefined')
	loadScriptFile('<?= globalRootURL?>/script/<?= $ver ?>');

/*]]>*/
</script>
<? if (testValue('ajax')) return; ?>
<? m('scriptLoad', "script/$ver"); ?>
<? } ?>
<? function script_cookie($val){
	m('script:jq');
	m('scriptLoad', 'script/jquery.cookie.min.js');
}
function script_CrossSlide($val){
	m('script:jq');
	m('script::load', 'script/jquery.cross-slide.min.js');
} ?>


