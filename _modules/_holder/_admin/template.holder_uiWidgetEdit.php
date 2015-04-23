<?
function holder_uiWidgetEdit($val, $data)
{
	if (!access('write', "holder:")) return;
	
	$widgetID	= getValue('widgetID');
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
		module("holderAdmin:setWidget:$widgetID", $widget);
		$widget		= module("holderAdmin:getWidget:$widgetID");
	}
	
	$config	= $widget['config'];
	if (!is_array($config)) $config = array();
	
	$data	= $widget['data'];
?>
{{page:title=Редактирование $widgetID}}
{{script:ajaxLink}}

<h1>{$widget[name]}</h1>
<h3>{$widget[title]}</h3>
<form action="{{url:#=holderName:$holderName;widgetID:$widgetID}}" method="post" class="seekLink">
<table>
<? foreach($config as $name =>$cfg ){ ?>
<tr>
	<td>{$name}:</td>
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
<p>
	<input type="submit" class="button" value="Сохранить" />
</p>
</form>
<? } ?>

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
	foreach($val as $n => $v)
	{
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