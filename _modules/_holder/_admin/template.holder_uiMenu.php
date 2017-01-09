<? function holder_uiMenu($holderName, $data)
{
	if (!access('write', "holder:$holderName")) return;
	
	$holders	= config::get(":adminHolders", array());
	$holders[$holderName] = $holderName;
	config::set(":adminHolders", $holders);

	m('script:jq');
	m('script:overlay');
	
	$holders= getStorage('holder/holders', 'ini');
	$note	= $holders[$holderName]['note'];

	$deep	= meta::get(':holders', array());
	meta::begin($data);
	$deep[]	= $holderName;
	meta::set(':holders', $deep);
	
	$widgetsID	= $holders[$holderName]['widgets'] or array();
	//	Показать виджеты
?>
    <module:script:jq_ui />
    <module:script:ajaxLink />
    
	<link rel="stylesheet" type="text/css" href="css/adminWidget.css">
	<div class="adminHolder" id="holder_{$holderName}" rel="{$holderName}">
        <div class="adminHolderMenu">
            <a href="{{url:admin_holderEdit=holderName:$holderName}}" title="Изменить" id="ajax">
                <b>КОНТЕЙНЕР:</b> {$holderName}
            </a>
        </div>
        <div class="adminHolderWidgets">


	<? foreach($widgetsID as $widgetID){ ?>
    	<module:holderAdmin:uiMenuWidget @="$widgetID" />
    <? } ?>

	    </div>
	</div>
<?	
meta::end();
}?>

<?
//	+function holder_uiMenuWidget
function holder_uiMenuWidget($val, $widgetID)
{
	if (!access('write', "holder:")) return;

//	$widget	= module("holderAdmin:getWidget:$widgetID");
	$widget	= widgetHolder::getWidget($widgetID);
	$exec	= $widget[':exec'];
	if (!$exec['code']) return;
	
	$className	= $widget['className'];
	if ($className) $className = " ($className)";
	
	if ($widget['hide']){
		$title	= 'СКРЫТ:';
		$class	= 'adminWidget hiddenWidget';
	}else{
		$title	= 'ВИДЖЕТ:';
		$class	= 'adminWidget';
	}
	$drag				= array();
/*
	$drag['drag_data']	= array(
		'drag_type'	=> 'widget',
		'overlay'	=> true,
		'actionAdd'		=> getURL('ajax_widget_add', 	"widgetID=$widgetID"),
		'actionRemove'	=> getURL('ajax_widget_remove', "widgetID=$widgetID"),
		'widgetID'		=> $widgetID
	);
*/
?>
{{script:jq_ui}}
{{script:ajaxLink}}
<link rel="stylesheet" type="text/css" href="css/adminWidget.css">
<script src="script/adminWidgets.js"></script>

<div class="{$class}" id="{$widgetID}">
	<div class="adminWidgetMenu">
        <span class="ui-icon ui-icon-arrowthick-2-n-s admin_sort_handle" style="float:left" title="Сортировать"></span>
        <a href="{{url:admin_holderWidgetEdit=widgetID:$widgetID}}" title="Изменить" id="ajax" rel="{$drag|json}">
            <b>{$title}</b> {$widget[name]} {$className}
        </a>
    </div>
	<? module($exec['code'], $exec['data']) ?>
</div>
<? } ?>