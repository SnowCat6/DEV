<?
function preview_widget($val, $data)
{
	setTemplate('');

	$widget	= getValue('widgetData');
	if (!$widget)
	{
		$widgetID	= getValue('widgetID');
		if (!$widgetID) $widgetID = getValue('widgetAdd');
		if (!$widgetID) return;
		
		$widgets	= module("holderAdmin:getWidgets");
		$widget		= $widgets[$widgetID];
		if (!$widget) return;
	}

	$widget		= module("holderAdmin:widgetPrepare", $widget);
	$preview	= $widget[':preview'];
	if (!$preview['code']) return;
?>
<div class="previewImage"><? module($preview['code'], $preview['data']) ?></div>
<h2 class="previewTitle">{$widget[name]}</h2>
<div class="previewProperty">{$widget[title]}</div>
<? } ?>