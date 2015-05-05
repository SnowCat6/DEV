<? function holder_uiEdit($val, $data)
{
	$holderName	= $val?$val:getValue('holderName');
	if (!$holderName) return;
	
	if (!access('write', "holder:$holderName")) return;

	beginUndo();
	$holderSort	= getValue('holderSort');
	if (is_array($holderSort))
	{
		$newWidgets	= array();
		$widgets	= module("holderAdmin:getHolderWidgets:$holderName");
		foreach($holderSort as $oldIndex)
		{
			$widget	= $widgets[$oldIndex];
			if ($widget) $newWidgets[]	= $widget;
		}
		$widgets	= $newWidgets;
		module("holderAdmin:setHolderWidgets:$holderName", $widgets);
	}
	
	$holderDelete	= getValue('holderDelete');
	if (is_array($holderDelete))
	{
		$widgets	= module("holderAdmin:getHolderWidgets:$holderName");
		foreach($holderDelete as $ix){
			$widgets[$ix] = '';
			unset($widgets[$ix]);
		}
		module("holderAdmin:setHolderWidgets:$holderName", $widgets);
	}

	$widgetDelete	= getValue('widgetDelete');
	if (is_array($widgetDelete))
	{
		$widgets	= module("holderAdmin:getWidgets");
		foreach($widgetDelete as $widgetID){
			$widgets[$widgetID]	= '';
			unset($widgets[$widgetID]);
		}
		module("holderAdmin:setWidgets", $widgets);
	}

	endUndo();
	
	$widgetType	= getValue('addWidgetType');
	$rawWidget	= $widgetType?module("holderAdmin:findWidget:$widgetType"):'';
	if ($rawWidget){
		$widgetID	= module("holderAdmin:addWidget:$holderName", $rawWidget);
		return module("holderAdmin:uiWidgetEdit:$widgetID", array(
			'holderName' => $holderName
		));
	}

	$widgetAdd	= getValue('widgetAdd');
	if ($widgetAdd){
		$widget		= module("holderAdmin:getWidget:$widgetAdd");
		$widgetID	= module("holderAdmin:addWidget:$holderName", $widget);
		return module("holderAdmin:uiWidgetEdit:$widgetID", array(
			'holderName' => $holderName
		));
	}

	
	$preview= array('preview_prefix' => 'widget_preview_');
	$json	=json_encode($preview);
	
	$holders= getStorage('holder/holders', 'ini');
	$data	= getValue("holder");
	if (is_array($data))
	{
		foreach($data as $name => $value){
			$holders[$holderName][$name]	= $value;
		}
		setStorage('holder/holders', $holders, 'ini');
	}
//////////////////////////////////////////////////	
?>
{{page:title=Выберите виджет для добавления к $holderName}}

{{script:ajaxLink}}
{{script:preview}}
{{script:jq}}
{{script:jq_ui}}

<script>
$(function(){
	$(".holderAdminSort").sortable({
		axis: 'y'
	});
	$('.holderTrigger > h3').click(function(){
		$(this).parent().toggleClass('selected');
	});
});
</script>

<link rel="stylesheet" type="text/css" href="css/adminWidget.css">
<form action="{{url:admin_holderEdit=holderName:$holderName}}" method="post" class="admin ajaxForm ajaxReload">

<table class="table" width="100%">
  <tr class="noBorder">
    
  <td width="250" valign="top" nowrap="nowrap" class="holderAdminSort">
<?
$widgets	= module("holderAdmin:getHolderWidgets:$holderName");
foreach($widgets as $ix => $widget){
	$widget	= module("holderAdmin:widgetPrepare", $widget);
?>
  <div>
    <span class="ui-icon ui-icon-arrowthick-2-n-s admin_sort_handle" style="float:left"></span>
    <a href="{{url:admin_holderWidgetEdit=holderName:$holderName;widgetID:$widget[id]}}" id="ajax">cfg</a>
    <label title="{$widget[desc]}"><input type="checkbox" name="holderDelete[]" value="{$ix}" />
      {$widget[name]}
      </label>
    <input type="hidden" name="holderSort[]" value="{$ix}" />
  <? if ($widget['note']){ ?>
    <blockquote>{$widget[note]}</blockquote>
  <? } ?>
  </div>
  <? } ?>
  </td>
    
  <td valign="top" class="adminWidgetTabs">
  <? module('admin:tab:holder_tab', $holderName) ?>
  </td>
</tr>
</table>
</form>

<? } ?>

<?
//	+function holder_tabLib
function holder_tabLib($holderName)
{
$preview= array('preview_prefix' => 'widget_preview_');
$json	=json_encode($preview);

$rawWidgets	= array();
event('holder.widgets', $rawWidgets);
usort($rawWidgets, function($a, $b){
	return $a['name'] > $b['name'];
});


$count	= count($rawWidgets);
$wMenu	= array();
foreach($rawWidgets as $w)
	$wMenu[$w['category']][]	= $w;

foreach($wMenu as $wCategory => $widgets){ ?>
<div class="menu inline holderTrigger seekLink">
    <h3>{$wCategory} <sup><?= count($widgets)?></sup></h3>
    <div class="content">
<? foreach($widgets as $rawWidget){?>
<a href="{{url:#=holderName:$holderName;addWidgetType:$rawWidget[className]}}" title="{$rawWidget[desc]}" class="preview" rel="{$json}">
    {$rawWidget[name]}
</a>
  <? } ?>
    </div>	
</div>
<? } ?>

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
$json	=json_encode($preview);

$widgets= module("holderAdmin:getWidgets");
usort($widgets, function($a, $b){
	return $a['name'] > $b['name'];
});
$count	= count($widgets);
?>
<?
foreach($widgets as $widgetID => $widget){
	$name	= $widget['name'];
	if (!$name) $name = $widgetID;
	$c	= (int) $counters[$widgetID];
?>
<div class="seekLink">
    <a href="{{url:admin_holderWidgetEdit=holderName:$holderName;widgetID:$widgetID}}" id="ajax">cfg</a>
    {$c}
    <label><input type="checkbox" name="widgetDelete[]" value="{$widgetID}"/></label>
    <a href="{{url:#=holderName:$holderName;widgetAdd:$widgetID}}" title="{$widget[desc]}" class="preview" rel="{$json}">
    	{$name}
    </a>
<? if ($widget['note']){ ?>
	- {$widget[note]}
<? } ?>
</div>
<? } ?>

<? return "Используемые ($count)"; } ?>

<?
//	+function holder_tabComment
function holder_tabComment($holderName)
{
	$holders= getStorage('holder/holders', 'ini');
?>
<b>Комментарий к {$holderName}</b>
<textarea class="input w100" name="holder[note]" rows="3">{$holders[$holderName][note]}</textarea>

<b>Глобальные переменные виджетов</b>
<?
$vars		= array();
$widgets	= getStorage('holder/widgets', 'ini') or array();
foreach($widgets as $widget){
	$cfg	= $widget['config'] or array();
	foreach($cfg as $c){
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

