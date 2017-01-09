<?
function module_widget($fn, $data)
{
	list($widgetClass, $id) = explode(':', $fn, 2);
	
	$widgets	= getCacheValue(':holderWidgets');
	//	Обновить кеш виджетов
	if (!$widgets)
	{
		$widgets= getStorage("holder/widgets", 'ini') or array();
		$widget	= $widgets[$id];
		foreach($widgets as &$w){
			$w	= module("holderAdmin:widgetPrepare", $w);
		}
		setCacheValue(':holderWidgets', $widgets);
	}

	$widget	= $widgets[$id];
	if (!$widget)
	{
		$widget	= widgetHolder::findWidget($widgetClass, NULL);
		if (!$widget) return;

		$widget	= module("holderAdmin:widgetPrepare", $widget);
		$widgets[$id] = $widget;
		setCacheValue(':holderWidgets', $widgets);
	}
	
	$fn	= getFn("widget_$widget[className]");
	if (!$fn || !$id) return;

	$exec	= $widget[':exec'];
	if (!$exec['code'] || $widget['hide']) return;
	return $fn($id, $exec['data']);
}
?>