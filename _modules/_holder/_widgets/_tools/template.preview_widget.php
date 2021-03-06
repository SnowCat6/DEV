<?
function preview_widget($val, $data)
{
	setTemplate('');

	$widgetType	= getValue('addWidgetType');
	if (!$widgetType) $widgetType =  getValue('widgetType');
	if (!$widgetType) $widgetType =  getValue('className');
	
	if ($widgetType){
		$widget		= widgetHolder::findWidget($widgetType);
	}else{
		$widgetID	= getValue('widgetID');
		if (!$widgetID) $widgetID = getValue('widgetAdd');
		if (!$widgetID) $widgetID = getValue('addWidgetID');
		if (!$widgetID) return;
		
		$widgets	= widgetHolder::getWidgets();
		$widget		= $widgets[$widgetID];
		if (!$widget) return;
	}

	$widget		= module("holderAdmin:widgetPrepare", $widget);
	$preview	= $widget[':preview'];
	if (!$preview['code']) return;
	
	$p	= m($preview['code'], $preview['data']);
	if (!$p) return;
	
	setTemplate('');
?>
<link rel="stylesheet" type="text/css" href="../../../_doc/_preview/css/jqPreview.css">
<div class="previewImage">{!$p}</div>
<div class="previewTitle">
<h2>{$widget[name]}</h2>
{$widget[title]}
</div>
<? } ?>