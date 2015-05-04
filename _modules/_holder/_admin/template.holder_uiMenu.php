<? function holder_uiMenu($holderName, $data)
{
	if (!access('write', "holder:$holderName")) return;
	
	m('script:jq');
	m('script:overlay');
	
	$menu	= array();
	$holders= getStorage('holder/holders', 'ini');

	global $_CONFIG;
	$v	= $_CONFIG[':holders'] or array();
	foreach($h as $ix => $hn)
	{
		$note	= $holders[$hn]['note'];
		$menu[($ix + 1) . '#ajax']	= array(
			'href'	=> getURL('admin_holderEdit', array('holderName' => $hn)),
			'title'	=> $note
		);
	}
	
	$note			= $holders[$holderName]['note'];
	$menu[':type']	= 'left';
	$menu[':class']	= 'adminHolderMenu';
	$menu[':attr']['rel']	= $holderName;
	$menu['Изменить контейнер#ajax']	= array(
		'href' 	=> getURL('admin_holderEdit', array('holderName' => $holderName)),
		'title'	=> $note
	);

	$_CONFIG[':holders'][]	= $holderName;
	
	beginAdmin($menu);

	$widgetsID	= $holders[$holderName]['widgets'] or array();
	//	Показать виджеты
	echo '<div class="adminHolderWidgets">';
	foreach($widgetsID as $widgetID)
		holder_uiMenuWidget($widgetID);
	echo '</div>';
	
	endAdmin();
	array_pop($_CONFIG[':holders']);
}?>

<?
//	+function holder_uiMenuWidget
function holder_uiMenuWidget($widgetID)
{
	if (!access('write', "holder:")) return;

	$widget	= module("holderAdmin:getWidget:$widgetID");
	$exec	= $widget[':exec'];
	if (!$exec['code']) return;
	
	$className	= $widget['className'];
	if ($className) $className = " ($className)";
?>
{{script:jq_ui}}
{{script:ajaxLink}}
<link rel="stylesheet" type="text/css" href="css/adminWidget.css">
<script src="script/adminWidgets.js"></script>

<div class="adminWidget" id="{$widgetID}">
	<div class="adminWidgetMenu">
 	<span class="ui-icon ui-icon-arrowthick-2-n-s admin_sort_handle" style="float:left" title="Сортировать"></span>
   	<a href="{{url:admin_holderWidgetEdit=widgetID:$widgetID}}" title="Изменить" id="ajax">
        <b>WIDGET:</b> {$widget[name]} {$className}
    </a>
    </div>
	<? module($exec['code'], $exec['data']) ?>
</div>
<? } ?>