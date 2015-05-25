<?
function holder_uiWidgetEdit($val, $data)
{
	if (!access('write', "holder:")) return;
	
	$holderName	= $data['holderName']?$data['holderName']:getValue('holderName');
	
	$widgetID	= $val?$val:getValue('widgetID');
	$widget		= module("holderAdmin:getWidget:$widgetID");
	if (!$widget) return;
	
	$widget			= module("holderAdmin:widgetPrepare", $widget);

	if ($className = getValue('adminWidgetReplace'))
	{
		$rawWidget	= module("holderAdmin:findWidget:$className");
		if (!$rawWidget) return;
		$widget['className']= $className;
		module("holderAdmin:setWidget:$widgetID", $widget);
		return module("holderAdmin:widgetLoad:$widgetID");
	}

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
		makeWidgetUpdate($widgetID, $holderName?false:true);
		
		if ($holderName)
			return module("holderAdmin:uiEdit:$holderName");
	}
?>
{{page:title=Редактирование $widgetID}}
{{ajax:template=ajax_edit}}
{{script:jq}}
{{script:ajaxLink}}
{{script:ajaxForm}}
<link rel="stylesheet" type="text/css" href="css/adminWidget.css">
<script src="script/adminWidgets.js"></script>

<form action="{{url:admin_holderWidgetEdit=holderName:$holderName;widgetID:$widgetID}}" method="post" class="admin admiWidget ajaxForm ajaxReload">
<? module('admin:tab:holder_widgetTab', $widgetID) ?>
</form>
<? } ?>

<? function makeWidgetUpdate($widgetID, $bClose){?>
<script>
updateWidget("{$widgetID}");
<? if ($bClose){ ?>
$().overlay("close");
<? } ?>
</script>
<? } ?>

<?	//	+function holder_widgetTab_edit
function holder_widgetTab_edit($widgetID)
{
	$widget	= module("holderAdmin:getWidget:$widgetID");
	$config	= $widget[':config'] or array();
	$data	= $widget['data'];
?>
<b>{$widget[name]}</b>
<div>{$widget[desc]}</div><br>
<table width="100%" cellpadding="2" cellspacing="0">
<? foreach($config as $name =>$cfg ){ ?>
<tr>
	<td nowrap="nowrap" valign="top">{$cfg[name]}:</td>
    <td width="100%" valign="top">
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
	$count	= count($rawWidgets);

	$wMenu	= array();
	foreach($rawWidgets as $w)
		$wMenu[$w['category']][]	= $w;
?>
{{script:preview}}
<div class="adminWidgetReplace adminAccardion widgetsLib">
<? foreach($wMenu as $wCategory => $rawWidgets){ ?>
    <h3>{$wCategory} {!$rawWidgets|count|tag:sup}</h3>
    <div>
<? foreach($rawWidgets as $rawWidget)
{
	$preview= array(
		'preview_prefix'=> 'widget_preview_',
		'widgetType'	=> $rawWidget['className']
	);
?>
    <a href="{{url:#=widgetType:$rawWidget[className]}}" class="preview" rel="{$preview|json}">
        {$rawWidget[name]}
    </a>
<? } ?>
</div>
<? } ?>
</div>
<? return "Заменить на виджет ($count)"; } ?>

<?	//	+function holder_widgetTab_dev
function holder_widgetTab_dev($widgetID)
{
	$widget		= module("holderAdmin:getWidget:$widgetID");
?>
<? printWidgetFields($widget); ?>
<? return "Данные виджета"; } ?>

<? function printWidgetFields($val, $deep = 0)
{
	if ($deep) echo "<div style='padding-left: 20px'>";
	foreach($val as $name => $v){
		$name	= htmlspecialchars($name);
		echo "<div>";
		if (is_array($v)){
			echo "<b>$name:</b>";
			printWidgetFields($v, $deep+1);
		}else{
			$v		= htmlspecialchars($v);
			echo "<b>$name:</b> $v";
		}
		echo "</div>";
	}
	if ($deep) echo "</div>";
}?>




