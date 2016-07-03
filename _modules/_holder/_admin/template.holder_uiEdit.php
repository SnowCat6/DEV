<? function holder_uiEdit($val, $data)
{
	$holderName	= $val?$val:getValue('holderName');
	if (!$holderName) return;

	if (!access('write', "holder:$holderName")) return;
/////////////////////////////////////////////
	$holderDelete	= getValue('holderDelete');
	if (is_array($holderDelete))
	{
//		$widgets	= module("holderAdmin:getHolderWidgets:$holderName");
		$widgets	= widgetHolder::getHolderWidgets($holderName);
		foreach($holderDelete as $ix){
			$widgets[$ix] = '';
			unset($widgets[$ix]);
		}
//		module("holderAdmin:setHolderWidgets:$holderName", $widgets);
		widgetHolder::setHolderWidgets($holderName, $widgets);
	}
/////////////////////////////////////////////
	$widgetDelete	= getValue('widgetDelete');
	if (is_array($widgetDelete))
	{
//		$widgets	= module("holderAdmin:getWidgets");
		$widgets	= widgetHolder::getWidgets();
		foreach($widgetDelete as $widgetID){
			$widgets[$widgetID]	= '';
			unset($widgets[$widgetID]);
		}
		widgetHolder::setWidgets($widgets);
//		module("holderAdmin:setWidgets", $widgets);
	}
/////////////////////////////////////////////
	$widgetID	= getValue('addWidgetID');
	$className	= getValue('className');
	
	if ($widgetID){
//		$widget		= module("holderAdmin:getWidget:$widgetID");
		$widget		= widgetHolder::getWidget($widgetID);
		if ($widget)
			$widget		= widgetHolder::addWidget($holderName, $widget);
//			module("holderAdmin:addWidget:$holderName", $widget);
	}else
	if ($className)
	{
//		$rawWidget	= module("holderAdmin:findWidget:$className");
		$rawWidget	= widgetHolder::findWidget($className);
		if ($rawWidget)
		{
//			$widgetID	= module("holderAdmin:addWidget:$holderName", $rawWidget);
			$widgetID	= widgetHolder::addWidget($holderName, $rawWidget);
			if ($widgetID)
				return module("holderAdmin:uiWidgetEdit:$widgetID", array(
					'holderName'	=> $holderName
			));
		}
	}
/////////////////////////////////////////////
?>
<module:page:title @="Выберите виджет для добавления к $holderName" />
<module:ajax:template @="ajax_edit" />

<module:script:ajaxLink />
<module:script:preview />
<module:script:jq />
<module:script:jq_ui />
<script src="script/adminWidgets.js"></script>
<link rel="stylesheet" type="text/css" href="css/adminWidget.css">

<form action="{{url:admin_holderEdit=holderName:$holderName}}" method="post" class="admiWidget ajaxForm ajaxReload">

<table class="table" width="100%">
  <tr class="noBorder">
    
  <td width="300" valign="top" nowrap="nowrap" class="holderAdminSort ui-widget-content ui-corner-all">
<module:startDrop
    :accept 			= "widget"
    :sortable.axis		= "y"
    :sortable.action	= "ajax_widget_sort.htm"
	holderName 			= "$holderName"
     />
<? widgetAdminDropZone($holderName) ?>
<module:endDrop />
  </td>
    
  <td valign="top" class="adminWidgetTabs">
  <? module('admin:tab:holder_tab', $holderName) ?>
  </td>
</tr>
</table>
</form>

<? } ?>

<?
//	+function holder_ajaxWidgetSort
function holder_ajaxWidgetSort($val, $data)
{
	setTemplate('ajaxResult');
	$drop_data	= getValue('drop_data');
	$holderName	= $drop_data['holderName'];
	
	$widgets	= array();
	$ids		= getValue('sort_data');
	foreach($ids as $widgetID)
		$widgets[]	= widgetHolder::getWidget($widgetID);
//		$widgets[]	= module("holderAdmin:getWidget:$widgetID");

//	module("holderAdmin:setHolderWidgets:$holderName", $widgets);
	widgetHolder::setHolderWidgets($holderName, $widgets);
	
	widgetAdminDropZone($holderName);
}
?>

<?
//	+function holder_ajaxWidgetAdd
function holder_ajaxWidgetAdd($val, $data)
{
	setTemplate('ajaxResult');
	$drop_data	= getValue('drop_data');
	$holderName	= $drop_data['holderName'];
	
	$widgetID	= getValue('widgetID');
	$className	= getValue('className');
	
	if ($widgetID){
//		$widget		= module("holderAdmin:getWidget:$widgetID");
		$widget		= widgetHolder::getWidget($widgetID);
		if ($widget)
			widgetHolder::addWidget($holderName, $widget);
//			module("holderAdmin:addWidget:$holderName", $widget);
	}else
	if ($className){
//		$rawWidget	= module("holderAdmin:findWidget:$className");
		$rawWidget	= widgetHolder::findWidget($className);
		if ($rawWidget)
			widgetHolder::addWidget($holderName, $rawWidget);
//			module("holderAdmin:addWidget:$holderName", $rawWidget);
	}
	widgetAdminDropZone($holderName);
}
?>

<? function widgetAdminDropZone($holderName)
{
m('script:draggable');
//$widgets	= module("holderAdmin:getHolderWidgets:$holderName");
$widgets	= widgetHolder::getHolderWidgets($holderName);
foreach($widgets as $ix => $widget){
	$widget	= module("holderAdmin:widgetPrepare", $widget);
?>
  <div>
    <span class="ui-icon ui-icon-arrowthick-2-n-s admin_sort_handle" style="float:left" id="{$widget[id]}"></span>
    <a href="{{url:admin_holderWidgetEdit=holderName:$holderName;widgetID:$widget[id]}}" id="ajax">cfg</a>
    <label title="{$widget[desc]}">
    	<input type="checkbox" name="holderDelete[]" value="{$ix}" />
        {$widget[name]}
        <? if ($widget['hide']) echo " - скрыт" ?>
    </label>
<? if ($widget['note']){ ?>
    <blockquote>{$widget[note]}</blockquote>
<? } ?>
  </div>
<? } ?>
<? } ?>

<?
//	+function holder_tabLib
function holder_tabLib($holderName)
{
$preview= array('preview_prefix' => 'widget_preview_');

$rawWidgets	= array();
event('holder.widgets', $rawWidgets);
usort($rawWidgets, function($a, $b){
	return $a['name'] > $b['name'];
});

$count	= count($rawWidgets);
$wMenu	= array();
foreach($rawWidgets as $w)
	$wMenu[$w['category']][]	= $w;
?>
{{script:jq_ui}}
<script src="script/adminWidgets.js"></script>
<div class="adminAccardion widgetsLib">
<?
foreach($wMenu as $wCategory => $widgets){ ?>
    <h3>{$wCategory} {!$widgets|count|tag:sup}</h3>
    <div class="seekLink">
<? foreach($widgets as $rawWidget)
{
	$preview['drag_data']	= array(
		'drag_type'	=> 'widget',
		'overlay'	=> true,
		'actionAdd'		=> getURL('ajax_widget_add', 	"className=$rawWidget[className]"),
		'actionRemove'	=> getURL('ajax_widget_remove', "className=$rawWidget[className]"),
		'className'		=> $rawWidget['className']
	);
?>
<a href="{{url:admin_holderEdit=holderName:$holderName;className:$rawWidget[className]}}" title="{$rawWidget[desc]}" class="preview" rel="{$preview|json}">
    {$rawWidget[name]}
</a>
  <? } ?>
    </div>	
<? } ?>
</div>
<? return "Библиотека ($count)"; } ?>

<?
//	+function holder_tabExists
function holder_tabExists($holderName)
{
$holders	= getStorage('holder/holders', 'ini') or array();
$counters	= array();
foreach($holders as $holder)
{
	$widgetsID	= $holder['widgets'] or array();
	foreach($widgetsID as $widgetID)
		$counters[$widgetID]++;
}

$preview= array('preview_prefix' => 'widget_preview_');

//$widgets= module("holderAdmin:getWidgets");
$widgets	= widgetHolder::getWidgets();
usort($widgets, function($a, $b){
	return $a['name'] > $b['name'];
});
$count	= count($widgets);

$wMenu	= array();
foreach($widgets as $w)
	$wMenu[$w['category']][]	= $w;
?>
<div class="seekLink adminAccardion widgetsLib">
<? foreach($wMenu as $wCategory => $widgets){ ?>
    <h3>{$wCategory} {!$widgets|count|tag:sup}</h3>
    <div>
<?
foreach($widgets as $widget)
{
	$widgetID	= $widget['id'];
	$name		= $widget['name'];
	if (!$name) $name = $widgetID;
	$c	= (int) $counters[$widgetID];
	
	$preview['drag_data']	= array(
		'drag_type'	=> 'widget',
		'overlay'	=> true,
		'actionAdd'		=> getURL('ajax_widget_add', 	"widgetID=$widgetID"),
		'actionRemove'	=> getURL('ajax_widget_remove', "widgetID=$widgetID"),
		'widgetID'	=> $widgetID
	);
?>
<div>
    <a href="{{url:admin_holderWidgetEdit=holderName:$holderName;widgetID:$widgetID}}" id="ajax">cfg</a>
    {$c}
   <input type="checkbox" name="widgetDelete[]" value="{$widgetID}"/>
    <a href="{{url:admin_holderEdit=holderName:$holderName;addWidgetID:$widgetID}}" title="{$widget[desc]}" class="preview" rel="{$preview|json}">
    	{$name}
    </a>
<? if ($widget['note']){ ?>
	- {$widget[note]}
<? } ?>
</div>
<? } ?>
</div>
<? } ?>
</div>

<? return "Используемые ($count)"; } ?>

<?
//	+function holder_tabComment
function holder_tabComment($holderName)
{
	$holders= getStorage('holder/holders', 'ini');
?>
<b>Комментарий к {$holderName}</b>
<div>
<textarea class="input w100" name="holder[note]" rows="3">{$holders[$holderName][note]}</textarea>
</div>

<b>Глобальные переменные виджетов</b>
<?
$vars		= array();
$widgets	= getStorage('holder/widgets', 'ini') or array();
foreach($widgets as $widget)
{
	$cfg	= $widget['config'] or array();
	foreach($cfg as $c)
	{
		$def	= $c['default'];
		if (!preg_match('/#([^:]+)(:.*|.*)/', $def, $val)) continue;
		
		$name		= $val[1];
		$default	= ltrim($val[2], ':');
		$vars[$name]= $default;
	}
}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tbody>
<? foreach($vars as $name => $default){ ?>
<tr>
    <td>{$name}</td>
    <td><input type="text" class="input w100" placeholder="{$default}" /></td>
</tr>
<? } ?>
</tbody>
</table>

<? return "Настройки"; } ?>

