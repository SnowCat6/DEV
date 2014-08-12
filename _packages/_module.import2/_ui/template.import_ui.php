<? function import_ui($val, &$data)
{
	mkDir(importFolder);
	m('page:title', 'Импорт');
	
	$tabs	= array();
	$tabs['Загрузка и обработка файлов']	= array('import',		'import:import');
	$tabs['Сопоставление товаров']		= array('import_commit','import:commit');
	$tabs['Обновление товаров на сайте']	= array('import_synch',	'import:synch');
	
	if (testValue('ajax'))
	{
		$thisURL	= getURL('#');
		foreach($tabs as $name=>$val)
		{
			list($url, $module) = $val;
			if ($thisURL != getURL($url)) continue;
			return module($module);
		}
		return;
	}

	m('script:jq_ui');
	m('script:adminTabs');
?>
<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
<?
$thisModule	= '';
$thisURL	= getURL('#');
foreach($tabs as $name=>$val)
{
	list($url, $module) = $val;
	if ($thisURL == getURL($url)){
		$thisModule	= $module;
?>
    <li class="ui-corner-top ui-tabs-active ui-state-active"><a href="#importHolder">{$name}</a></li>
<? }else{ ?>
    <li class="ui-corner-top"><a href="{{url:$url=ajax}}">{$name}</a></li>
<? } ?>
<? } ?>
</ul>

<div id="importHolder"><? module($thisModule)?></div>

</div>
<? } ?>
