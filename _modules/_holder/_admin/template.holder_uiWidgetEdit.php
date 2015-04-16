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
		foreach($widgetConfig as $name => $val){
			$widget['config'][$name]['value']	= $val;
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
<form action="{{url:#=holderName:$holderName;widgetID:$widgetID}}" method="post" class="ajaxForm ajaxReload seekLink">
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

<? function holderInput_default($holder, $name, $cfg){ ?>
   	<input type="text" class="input" name="widgetConfig[{$name}]" value="{$cfg[value]}" placeholder="{$cfg[default]}" />
<? } ?>

<? function holderInput_checkbox($holder, $name, $cfg){ ?>
   	<input type="hidden" name="widgetConfig[{$name}]" value=""  />
   	<input type="checkbox" name="widgetConfig[{$name}]" value="{$cfg[default]}" {checked:$cfg[value]==$cfg[default]}  />
<? } ?>