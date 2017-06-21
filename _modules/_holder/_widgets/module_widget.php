<?
function module_widget($fn, $data)
{
	list($widgetClass, $widgetID) = explode(':', $fn, 2);
	if (!$widgetID) return;

	$widgets	= getCacheValue(':holderWidgets');
	//	Обновить кеш виджетов
	if (!$widgets)
	{
		$widgets= getStorage("holder/widgets", 'ini') or array();
		foreach($widgets as &$w){
			$w	= module("holderAdmin:widgetPrepare", $w);
		}
		setCacheValue(':holderWidgets', $widgets);
	}

	$widget	= $widgets[$widgetID];
	if (!$widget)
	{
		$widget	= widgetHolder::findWidget($widgetClass, NULL);
		if (!$widget) return;

		$widget	= module("holderAdmin:widgetPrepare", $widget);
		$widgets[$widgetID] = $widget;
		setCacheValue(':holderWidgets', $widgets);
	}

	$exec	= $widget[':exec'];
	if ($widget['hide']) return;

	list($m, $p, $v) = explode(':', $exec['code'], 3);
	//	Skip re-call module widget
	if ($m != "widget") 
		return module($exec['code'], $exec['data']);	

	$fn	= getFn("widget_$p");
	if (!$fn) return;
	
	return $fn($v, $exec['data']);
}
?>