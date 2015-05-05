<?
function holder_uiWidgetEdit($val, $data)
{
	if (!access('write', "holder:")) return;
	
	$holderName	= $data['holderName']?$data['holderName']:getValue('holderName');
	
	$widgetID	= $val?$val:getValue('widgetID');
	$widget		= module("holderAdmin:getWidget:$widgetID");
	if (!$widget) return;
	
	$widget			= module("holderAdmin:widgetPrepare", $widget);
	$widgetConfig	= getValue('widgetConfig');
	if (is_array($widgetConfig))
	{
		foreach($widgetConfig as $name => $val)
		{
			$cfg= $widget['config'][$name];
			if (!$cfg) continue;
			
			$fn	= getFn(array(
				"holderInput_$cfg[type]".'_update',
				'holderInput_default_update'
			));
			$widget['config'][$name]['value']	= $fn($widget, $name, $val);
		}
		if ($className = getValue('adminWidgetReplace')){
			$rawWidget	= module("holderAdmin:findWidget:$className");
			if ($rawWidget){
				$widget['className']= $className;
			}
		}
		module("holderAdmin:setWidget:$widgetID", $widget);
		
		if ($holderName){
			makeWidgetUpdate($widgetID, false);
			return module("holderAdmin:uiEdit:$holderName");
		}
		makeWidgetUpdate($widgetID, true);
	}
?>
{{page:title=Редактирование $widgetID}}
{{script:ajaxLink}}
{{ajax:template=ajax_edit}}
<link rel="stylesheet" type="text/css" href="css/adminWidget.css">
<script src="script/adminWidgets.js"></script>

<form action="{{url:admin_holderWidgetEdit=holderName:$holderName;widgetID:$widgetID}}" method="post" class="admin ajaxForm ajaxReload">
  <? module('admin:tab:holder_widgetTab', $widgetID) ?>
</form>
<? } ?>

<?	//	+function holder_widgetTab_edit
function holder_widgetTab_edit($widgetID)
{
	$widget	= module("holderAdmin:getWidget:$widgetID");
	$config	= $widget[':config'] or array();
	$data	= $widget['data'];
?>
<b>{$widget[name]}</b>
<div>{$widget[desc]}</div>
<table>
<? foreach($config as $name =>$cfg ){ ?>
<tr>
	<td>{$cfg[name]}:</td>
    <td>
<?
	$fn	= getFn(array(
		"holderInput_$cfg[type]",
		'holderInput_default'
	));
	$fn($widget, $name, $cfg);
?>
    </td>
</tr>
<? } ?>
</table>
<? return 'Настройка виджета'; } ?>

<?	//	+function holder_widgetTab_replace
function holder_widgetTab_replace($widgetID)
{
	$widget		= module("holderAdmin:getWidget:$widgetID");
	$rawWidgets	= array();
	event('holder.widgets', $rawWidgets);

	foreach($rawWidgets as $ix => $rawWidget)
	{
		if (!$widget['cap'] || !$rawWidget['className'] ||
			!array_intersect(explode(',', $widget['cap']), explode(',', $rawWidget['cap'])))
			unset($rawWidgets[$ix]);
	}
	usort($rawWidgets, function($a, $b){
		return $a['name'] > $b['name'];
	});

?>
<div class="adminWidgetReplace">
<? foreach($rawWidgets as $rawWidget){?>
<div><a href="#" rel="{$rawWidget[className]}">{$rawWidget[name]}</a></div>
<? } ?>
</div>
<? return 'Заменить виджет'; } ?>


<? function holderInput_default_update($holder, $name, $val){
	return $val;
}
function holderInput_default($holder, $name, $cfg){ ?>
   	<input type="text" class="input" name="widgetConfig[{$name}]" value="{$cfg[value]}" placeholder="{$cfg[default]}" />
<? }?>


<? function holderInput_checkbox($holder, $name, $cfg){ ?>
   	<input type="hidden" name="widgetConfig[{$name}]" value=""  />
   	<input type="checkbox" name="widgetConfig[{$name}]" value="{$cfg[default]}" {checked:$cfg[value]==$cfg[default]}  />
<? } ?>

<? function _holderInput_doc_filter_update($holder, $name, $val)
{
	$value	= array();
	foreach($val as $n => $v){
		$value[]	= "$n:$v";
	}
	return implode(';', $value);
}
function _holderInput_doc_filter($holder, $name, $cfg)
{
	$val	= array();
	setDataValues($val,	$cfg['value']);
	$def	= array();
	setDataValues($def,	$cfg['default']);
	
	$input				= array();
	$input['Родитель']	= 'parent';
	$input['Тип']		= 'type';
	$input['Шаблон']	= 'template';
	$input['Свойства']	= 'property';
	
	foreach($def as $n => $v){
		if (array_search($n, $input)) continue;
		$input[$n]	= $n;
	}
?>
<table>
<? foreach($input as $n => $v)
{
	$value	= $val[$v];
	$default= $def[$v];
?>
<tr>
   	<td>{$n}: </td>
    <td><input type="text" class="input" name="widgetConfig[{$name}][{$v}]" value="{$value}" placeholder="{$default}" /></td>
</tr>
<? } ?>
</table>
<? } ?>

<? function makeWidgetUpdate($widgetID, $bClose){ ?>
{{script:jq}}
<script>
$(function(){
	$(document).trigger("widgetUpdate", "{$widgetID}");
<? if ($bClose){ ?>
	$().overlay("close");
<? } ?>
});
</script>
<? } ?>