<? function import_ui($val, &$data)
{
	mkDir(importFolder);
	m('page:title', 'Импорт');
	m('script:jq_ui');
	m('script:adminTabs');
	m('script:ajaxLink');
	
	$tabs	= array();
	$tabs['Загрузка']			= array('import',		'import:import');
	$tabs['Сопоставление']		= array('import_commit','import:commit');
	$tabs['Обновление сайта']	= array('import_synch',	'import:synch');
	$tabs['Экспорт']				= array('import_export','import:export');
	
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
