<?
addUrl('widget_preview_(.+)',	'preview:widget');
addEvent('holder.widgets',		'widgetGenerator:widgets');

//addEvent('config.end',			'widget_config');

function module_widget_config()
{
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
}
?>