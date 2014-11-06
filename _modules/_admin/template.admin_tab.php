<?
//	Создать JQuery tab по названиям моуля
//	$filter	- RegExp названия функций для создания вкладок
//			- Фильтр разделяется двоеточеем, разделяющим вызовы функций по двум событиям для возможности обработки общих событий
//			- event("admin.tab.article", $ev);
//			- event("admin.tab.article:custom_article", $ev);
//	$data	- Данные передаваемые в функции
function admin_tab($filter, &$data)
{
	//	Запустить все модули и записать контекст в массив вкладок
	$tabsCtx= array();
	//	Фильтровать модули по фильтру
	$tabs	= getTabsTabs($filter, $data);
	foreach($tabs as $file => $path)
	{
		ob_start();
		include_once($path);
		ob_clean();
		if (function_exists($file)) $name = $file($data);
		$ctx = trim(ob_get_clean());
		//	Если вкладка не вернула результат, удалить вкладку
		if ($ctx == '') continue;

		//	Если функция не вернула название вкладки то создадим временное название
		if (!$name) $name = "999-$file";

		//	Сохранить вкладку
		if (preg_match('#^([\d+-]+)(.*)#', $name, $val)){
			$tabsCtx[(int)$val[1]][$val[2]] = $ctx;
		}else{
			$tabsCtx[999][$name] = $ctx;
		}
	}

	ksort($tabsCtx);
	if (!$tabsCtx) return;
	
	m('script:jq_ui');
	m('script:clone');
	m('script:adminTabs');
	
	//	Создадим вкладки
	echo "<div class=\"adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all\">";
	echo '<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">';

	//	Создать заголоаки
	foreach($tabsCtx as &$c)
	foreach($c as $name => &$ctx)
	{
		$tabIID	= md5($name);
		$name	= htmlspecialchars($name);
		echo "<li class=\"ui-corner-top\">";
		echo "<a href=\"#tab_$tabIID\">$name</a></li>";
	}
	//	Добавим кнопку сохранитьт, если переданы данные
	if ($data || is_array($data)) echo '<li style="float:right"><input name="docSave" type="submit" value="Сохранить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>';
	echo '</ul>';

	//	Создать данные
	foreach($tabsCtx as &$c)
	foreach($c as $name => &$ctx)
	{
		$tabIID	= md5($name);
		$name	= htmlspecialchars($name);
		echo "<!-- $name -->\r\n";
		echo "<div id=\"tab_$tabIID\" class=\"ui-tabs-panel ui-widget-content ui-corner-bottom\">$ctx</div>\r\n";
	}
	echo '</div>';
?>
<link rel="stylesheet" type="text/css" href="css/admin.css"/>
<? } ?>


<?
//	Вызвать функции обновления вкладок
//	+function admin_tabUpdate
function admin_tabUpdate($filter, &$data)
{
	$tabs	= getTabsTabs($filter, $data);
	foreach($tabs as $file => $path)
	{
		$file .= '_update';
		include_once($path);
		if (function_exists($file)) $file($data);
	}
}
?>
<? function getTabsTabs($filter, &$data)
{
	list($filter, $template) = explode(':', $filter, 2);
	$modules= getCacheValue('templates');
	//	Подготовить данные для обработки
	$name	='';
	$path	= '';
	$ev = array(
		&$name, &$path, &$data,											//	Compatible with old code
		'moduleName'=>&$name, 'moduleFile'=>&$path, 'eventData'=>&$data	//	Use this way
		);
	//	Получить общую вкладку
	event("admin.tab.$filter", $ev);
	if ($name && $path) $modules[$name] = $path;

	//	Фильтровать модули по фильтру
	$tabs	= array();
	foreach($modules as $name => $path)
	{
		if (!preg_match("#$filter#", $name)) continue;
		//	Подготовить данные для обработки
		$ev = array(
			&$name, &$path, &$data,											//	Compatible with old code
			'moduleName'=>&$name, 'moduleFile'=>&$path, 'eventData'=>&$data	//	Use this way
			);
		//	Получить общую вкладку
		event("admin.tab.$name", $ev);
		//	Получить уточненную вкладку
		event("admin.tab.$name:$template", $ev);
		if ($name && $path) $tabs[$name] = $path;
	}
	return $tabs;
}
?>
<?
//	+function script_adminTabs
function script_adminTabs(&$val){
	m('scrupt:jq_ui');
?>
<script>
$(function()
{
	$("div.adminTabs")
	.uniqueId()
	.tabs({
		beforeLoad: function(event, ui) {
			// if the target panel is empty, return true
			return ui.panel.html() == "";
		},
		load: function( xhr, status ) {
			$(document).trigger("jqReady");
		}
	});
});
</script>
<? } ?>