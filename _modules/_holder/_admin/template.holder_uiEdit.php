<? function holder_uiEdit($val, $data)
{
	$holderName	= getValue('holderName');
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

	$widgetAdd	= getValue('widgetAdd');
	if ($widgetAdd){
		$widget	= module("holderAdmin:getWidget:$widgetAdd");
		m("holderAdmin:addWidget:$holderName", $widget);
	}
	
	$widgetData	= getValue('widgetData');
	if (is_array($widgetData)){
		m("holderAdmin:addWidget:$holderName", $widgetData);
	}
	endUndo();
//////////////////////////////////////////////////	
?>
{{page:title=Выберите виджет для добавления к $holderName}}
{{script:ajaxLink}}
{{script:jq_ui}}
<script>
$(function(){
	$(".holderAdminSort").sortable({
		axis: 'y'
	});
});
</script>

<form action="{{url:#=holderName:$holderName}}" method="post" class="ajaxForm ajaxReload seekLink">

<table class="table" width="100%">
  <tr>
    <td width="300" nowrap="nowrap">Отмеченные виджеты будут удалены</td>
    <td width="100%">Библиотека</td>
    <td><input type="submit" class="button" value="Сохранить" /></td>
  </tr>
  <tr class="noBorder">

<td valign="top" nowrap="nowrap" class="holderAdminSort">
<?
$widgets	= module("holderAdmin:getHolderWidgets:$holderName");
foreach($widgets as $ix => $widget){?>
<div>
	<span class="ui-icon ui-icon-arrowthick-2-n-s admin_sort_handle" style="float:left"></span>
	<a href="{{url:admin_holderWidgetEdit=holderName:$holderName;widgetID:$widget[id]}}">cfg</a>
	<label><input type="checkbox" name="holderDelete[]" value="{$ix}"/>{$widget[name]}</label>
    <input type="hidden" name="holderSort[]" value="{$ix}" />
<? if ($widget['note']){ ?>
	<blockquote>{$widget[note]}</blockquote>
<? } ?>
</div>
<? } ?>
</td>

<td colspan="2" valign="top" style="padding-left:50px">
  <?
$rawWidgets	= array();
event('holder.widgets', $rawWidgets);

$wMenu	= array();
foreach($rawWidgets as $w)
	$wMenu[$w['category']][]	= $w;

foreach($wMenu as $wCategory => $widgets){ ?>
  <div class="menu inline">
  <h3>{$wCategory}</h3>
  <? foreach($widgets as $widget){?>
    <a href="{{url:#=holderName:$holderName;widgetData:$widget}}">{$widget[name]}</a>
  <? } ?>
  </div>
  <? } ?>
  <h3>Имеющиеся виджеты</h3>
  <?
$widgets	= module("holderAdmin:getWidgets");
foreach($widgets as $widgetID => $widget){
	$name	= $widget['name'];
	if (!$name) $name = $widgetID;
?>
  <div>
    <a href="{{url:admin_holderWidgetEdit=holderName:$holderName;widgetID:$widgetID}}">cfg</a>
    <label><input type="checkbox" name="widgetDelete[]" value="{$widgetID}"/></label>
    <a href="{{url:#=holderName:$holderName;widgetAdd:$widgetID}}">{$name}</a>
  <? if ($widget['note']){ ?>
    <blockquote>{$widget[note]}</blockquote>
  <? } ?>
  </div>
  <? } ?>
</td>
</tr>
</table>
</form>
<? } ?>

