<?
class widgetHolder
{
	static function findWidget($className, $widget)
	{
	
		if (!is_array($rawWidgets)){
			$rawWidgets	= array();
			event('holder.widgets', $rawWidgets);
		}
	
		if (!$className)
			$className	= $widget['className'];
		
		foreach($rawWidgets as $rawWidget){
			$rawClassName	= $rawWidget['className'];
			if ($rawClassName && $rawClassName == $className)
				return $rawWidget;
		};
		
		$className	= $widget['name'];
		foreach($rawWidgets as $rawWidget){
			$rawClassName	= $rawWidget['name'];
			if ($rawClassName && $rawClassName == $className)
				return $rawWidget;
		};
	}
	static function deleteWidget($widgetID, $data)
	{
		$widgets	= getWidgets('', '');
		if (!$widgets[$widgetID]) return;
		unset($widgets[$widgetID]);
		self::setWidgets($widgets);
	}
	static function setWidget($widgetID, $widget)
	{
		if (!access('write', "holder:")) return;
		
		widgetHolder::holderMakeUndo();
	
		$widgets= getStorage("holder/widgets", 'ini') or array();
	
		$id		= $widgetID;	
		if (!$id)	$id	= $widget['id'];
		if (!$id)	$id	= 'widget_' . time() . rand(100);
		$widget['id']	= $id;
	
		$widgets[$id]	= module("holderAdmin:widgetPrepare", $widget);
	
		setStorage("holder/widgets", $widgets, 'ini');
		
		$a	= NULL;
		setCacheValue(':holderWidgets', $a);
		
		return $id;
	}
	static function getWidget($widgetID)
	{
		$widgets	= getStorage("holder/widgets", 'ini');
		return module('holderAdmin:widgetPrepare', $widgets[$widgetID]);
	}
	static function getWidgets($data)
	{
		$widgets	= getStorage("holder/widgets", 'ini') or array();
		return $widgets;
	}
	static function setWidgets($widgets)
	{
		if (!access('write', "holder:")) return;
	
		widgetHolder::holderMakeUndo();
	
		$oldWidgets	= getStorage("holder/widgets", 'ini') or array();
		$holders	= getStorage("holder/holders", 'ini') or array();
	
		foreach($widgets as $widgetID => $widget)
		{
			$widgets[$widgetID]		= module("holderAdmin:widgetPrepare", $widget);
			$oldWidgets[$widgetID]	= '';
			unset($oldWidgets[$widgetID]);
		}
		
		foreach($oldWidgets as $widgetID => $widget)
		{
			foreach($holders as $holderName => $holder)
			{
				$wids	= $holder['widgets'];
				if (!is_array($wids)) continue;
				
				foreach($wids as $ix => $wid)
				{
					if ($wid != $widgetID) continue;
					unset($wids[$ix]);
				}
				$holders[$holderName]['widgets']	= $wids;
			}
			
			$widget	= module("holderAdmin:widgetPrepare", $widget);
			$delete = $widget[':delete'];
			if (is_array($delete)) module($delete['code'], $delete['data']);
		}
		
		setStorage("holder/widgets", $widgets, 'ini');
		setStorage("holder/holders", $holders, 'ini');
		clearCache();
	}
	
	static function addWidget($holderName, $widgetData)
	{
		if (!access('write', "holder:$holderName")) return;
	
		undo::begin();
		widgetHolder::holderMakeUndo();
	
		$id			= widgetHolder::setWidget('', $widgetData);
		$holders	= getStorage("holder/holders", 'ini');
		$holders[$holderName]['widgets'][]	= $id;
		setStorage("holder/holders", $holders, 'ini');
		
		undo::end();
		clearCache();
	
		return $id;
	}
	static function getHolderWidgets($holderName, $data)
	{
		$widgets	= getStorage("holder/widgets", 'ini');
		$holders	= getStorage("holder/holders", 'ini');
		$widgetsID	= $holders[$holderName]['widgets'];
		if (!is_array($widgetsID)) $widgetsID = array();
		
		$modules	= array();
		foreach($widgetsID as $widgetID){
			$modules[] = $widgets[$widgetID];
		}
		$a	= NULL;
		setCacheValue(':holderWidgets', $a);
		return $modules;
	}
	static function setHolderWidgets($holderName, $widgets)
	{
		if (!access('write', "holder:$holderName")) return;
	
		undo::begin();
		widgetHolder::holderMakeUndo();
	
		$widgetsID	= array();
		foreach($widgets as $widget){
			$widgetsID[]	= widgetHolder::setWidget('', $widget);
		}
		$holders	= getStorage("holder/holders", 'ini');
		$holders[$holderName]['widgets']	= $widgetsID;
		setStorage("holder/holders", $holders, 'ini');
	
		undo::end();
		clearCache();
	
		return $widgetsID;
	}
	static function holderMakeUndo()
	{
		$undo	= array(
			'widgets'	=> getStorage("holder/widgets", 'ini'),
			'holders'	=> getStorage("holder/holders", 'ini')
		);
		undo::add("Виджеты измененены", 'holder',
			array('action' => "holderAdmin:undoWidgets", 'data' => $undo)
		);
	}
}
?>
