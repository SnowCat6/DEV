<?
//	filelist
function admin_tab($filter, &$data)
{
	$ix		= testValue('ajax')?md5($filter):md5("ajax_$filter");

	$d		= array();
	@list($filter, $template) = explode(':', $filter, 2);
	$modules= getCacheValue('templates');

	$ev = array('', '', $data);
	event("admin.tab.$filter", $ev);
	if ($ev[0] && $ev[1]) $modules[$ev[0]] = $ev[1];

	foreach($modules as $name => $path){
		if (!preg_match("#$filter#", $name)) continue;
		$ev = array($name, $path, $data);
		event("admin.tab.$name", $ev);
		event("admin.tab.$name:$template", $ev);
		if ($ev[0] && $ev[1]) $d[$ev[0]] = $ev[1];
	}
	
	$tabs = array();
	foreach($d as $file => $path)
	{
		ob_start();
		include_once($path);
		if (function_exists($file)) $name = $file($data);
		$ctx = trim(ob_get_clean());
		
		if ($ctx == '') continue;
		if (!$name) $name = $file;
		
		$tabs[$name] = $ctx;
	}
	
	if (!$tabs) return;
	
	module('script:jq_ui');
	module('script:clone');
	
	echo "<div class=\"adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all\">";
	echo '<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">';

	ksort($tabs);
	foreach($tabs as $name => &$ctx){
		$tabIID	= md5($name);
		$name	= preg_replace('#^([\d+-]+)#', '', $name);
		$name	= htmlspecialchars($name);
		echo "<li class=\"ui-corner-top\">";
		echo "<a href=\"#tab_$tabIID\">$name</a></li>";
	}
	if ($data || is_array($data)) echo '<li style="float:right"><input name="docSave" type="submit" value="Сохранить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>';
	echo '</ul>';

	foreach($tabs as $name => &$ctx){
		$tabIID	= md5($name);
		$name	= htmlspecialchars($name);
		echo "<!-- $name -->\r\n";
		echo "<div id=\"tab_$tabIID\" class=\"ui-tabs-panel ui-widget-content ui-corner-bottom\">$ctx</div>\r\n";
	}
	echo '</div>';
?>
{{script:adminTabs}}
<link rel="stylesheet" type="text/css" href="admin.css"/>
<? } ?>
