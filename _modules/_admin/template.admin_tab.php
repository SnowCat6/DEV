<?
//	Создать JQuery tab по названиям моуля
//	$filter	- RegExp названия функций для создания вкладок
//			- Фильтр разделяется двоеточеем, разделяющим вызовы функций по двум событиям для возможности обработки общих событий
//			- event("admin.tab.article", $ev);
//			- event("admin.tab.article:custom_article", $ev);
//	$data	- Данные передаваемые в функции
function admin_tab($filter, &$data)
{
	list($filter, $filter2, $submitName, $submitTitle) = explode(':', $filter, 4);
	$filter	= "$filter:$filter2";
	
	if (!$submitName)	$submitName = 'docSave';
	if (!$submitTitle)	$submitTitle = 'Сохранить';
	//	Запустить все модули и записать контекст в массив вкладок
	$tabsCtx= array();
	//	Фильтровать модули по фильтру
	$tabs	= getTabsTabs($filter, $data);
	foreach($tabs as $fileFn => $path)
	{
		ob_start();
		include_once($path);
		ob_clean();
	
		if (function_exists($fileFn)) $name = $fileFn($data);
		$ctx = trim(ob_get_clean());
		
		if (is_array($name)){
			$tabData	= $name;
			$name		= $tabData['name'];
		}else $tabData	= array();

		if (!$name) $name = "999-$file";
		$tabData['ctx']	= $ctx;

		//	Если вкладка не вернула результат, удалить вкладку
		if ($ctx == '' && !$tabData['URL']) continue;
		
		//	Сохранить вкладку
		if (preg_match('#^([\d+-]+)(.*)#', $name, $val)){
			$tabsCtx[(int)$val[1]][$val[2]] = $tabData;
		}else{
			$tabsCtx[999][$name] = $tabData;
		}
	}

	ksort($tabsCtx);
	if (!$tabsCtx) return;
	
	//	Создадим вкладки
	echo "<div class=\"adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all\">";
	echo '<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">';

	//	Создать заголоаки
	foreach($tabsCtx as $c)
	foreach($c as $name => $tabData)
	{
		$tabIID	= md5($name);
		$name	= htmlspecialchars($name);
		echo "<li class=\"ui-corner-top\">";
		
		$URL	= $tabData['URL'];
		if ($URL){
			$URL .= strpos($URL, '?')===false?'?':'&';
			$URL .= "ajax=ajaxResult";
			echo "<a href=\"$URL\">$name</a></li>";
		}else{
			echo "<a href=\"#tab_$tabIID\">$name</a></li>";
		}
	}
	//	Добавим кнопку сохранитьт, если переданы данные
	if ($data || is_array($data)){
		echo "<li style=\"float:right\"><input name=\"$submitName\" type=\"submit\" value=\"$submitTitle\" class=\"ui-button ui-widget ui-state-default ui-corner-all\" /></li>";
	}
	echo '</ul>';

	//	Создать данные
	foreach($tabsCtx as $c)
	foreach($c as $name => $tabData)
	{
		if (!$tabData['ctx']) continue;
		$tabIID	= md5($name);
		$name	= htmlspecialchars($name);
		echo "<!-- $name -->\r\n";
		echo "<div id=\"tab_$tabIID\" class=\"ui-tabs-panel ui-widget-content ui-corner-bottom \">$tabData[ctx]</div>\r\n";
	}
	echo '</div>';

	m('script:jq_ui');
	m('script:clone');
	m('script:adminTabs');
?>
<link rel="stylesheet" type="text/css" href="css/admin.css"/>
<? } ?>


<?
//	Вызвать функции обновления вкладок
//	+function admin_tabUpdate
function admin_tabUpdate($filter, &$data)
{
	$tabs	= getTabsTabs($filter, $data);
	foreach($tabs as $fileFn => $path)
	{
		$fileFn .= '_update';
		include_once($path);
		if (function_exists($fileFn)) $fileFn($data);
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
		'moduleName'=> &$name,
		'moduleFile'=> &$path,
		'eventData'	=> &$data	//	Use this way
		);
	//	Получить общую вкладку
	event("admin.tab.$filter", $ev);
	if ($name && $path) $modules[$name] = $path;

	//	Фильтровать модули по фильтру
	$tabs	= array();
	foreach($modules as $name => $path)
	{
		if (!preg_match("#$filter#", $name)) continue;
		$ext	= explode('_', $name); $ext	= end($ext);
		if ($ext == 'update') continue;
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
function script_adminTabs(){ ?>
{{script:jq_ui}}
<script src="script/jq.adminTabs.js"></script>
<? } ?>
