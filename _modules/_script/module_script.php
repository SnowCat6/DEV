<?
function module_style(&$val, &$data)
{
	setCacheData("style:$val", $data);

	global $_SETTINGS;
	$style = &$_SETTINGS['style'][$val];
	if (!is_null($style)) return;
	$style = '';
	
	$fn = getFn("style_$val");				//	Получить функцию (и загрузка файла) модуля
	ob_start();
	if ($fn) $fn($data);
	$style	= ob_get_clean();
}
function module_script(&$val, &$data)
{
	setCacheData("script:$val", $data);

	global $_SETTINGS;
	$script = &$_SETTINGS['script'][$val];
	if (!is_null($script)) return;
	
	$script = '';
	$fn		= getFn("script_$val");				//	Получить функцию (и загрузка файла) модуля
	
	//	Присоеденить стиль, если есть такой
	m("style:$val", $data);
	
	ob_start();
	if ($fn) $fn($data);
	//	Для сохранения зависимостей скрипты вызванные ранее должны быть первыми
	unset($_SETTINGS['script'][$val]);
	//	Пересоздать значение
	$_SETTINGS['script'][$val]	= ob_get_clean();
}
function hasScriptUser($val){
	global $_SETTINGS;
	return @$_SETTINGS['script'][$val];
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
function script_jq($val)
{
	$jQuery	= getCacheValue('jQuery');
	if (isModernBrowser()) $ver = $jQuery['jQueryVersion2'];
	else $ver = $jQuery['jQueryVersion'];
?>
<script>
/*<![CDATA[*/
function loadScriptFile(filename)
{
	document.write('<' + 'script type="text/javascript" src="' + filename + '">' + '<' + '/script>');
}

if (typeof jQuery == 'undefined')
	loadScriptFile('<?= globalRootURL?>/script/<?= $ver ?>');

/*]]>*/
</script>
<?  if (testValue('ajax')) return; ?>
<? m('scriptLoad', "script/$ver"); ?>
<? } ?>
<? function script_cookie($val){
	m('script:jq');
	m('scriptLoad', 'script/jquery.cookie.min.js');
}
function script_CrossSlide($val){
	m('script:jq');
	m('scriptLoad', 'script/jquery.cycle.lite.js');
?>
<script>
$(function(){
	$(".slide .adminEditArea").each(function(){
		$(this).height($(this).height());
	});
	$(".slide").cycle({
		slideExpr: 'img',
		cssFirst:{left: 0, top: 0}
	}).css({"z-index": 0});
});
</script>
<? } ?>


