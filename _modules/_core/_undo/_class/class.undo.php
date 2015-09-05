<?
class undo
{
/********************************/
	static function db()
	{
		return new dbRow('log_tbl', 'log_id');
	}
/********************************/
//	Начать сбор действий в пакет
	static function begin()
	{
		global $_CONFIG;
		
		if ($_CONFIG[':undo'] == 0){
			$_CONFIG[':undo_data']	= array();
		}
		$_CONFIG[':undo'] += 1;
	}
//	Окончить сбор действий, создать запись отмены
	static function end()
	{
		global $_CONFIG;
		
		$_CONFIG[':undo'] -= 1;
		if ($_CONFIG[':undo']) return;
		
		$data	= $_CONFIG[':undo_data'];
		$_CONFIG[':undo_data']	= array();
		
		$first	= $data[0];
		
		if (count($data) == 0)
			return;
		if (count($data) == 1)
			return undo::addUndo($first);;
	
		$first['action']	= 'undo:exec';
		$first['info']		= 'undo:exec_info';
		$first['data']		= $data;
		return undo::addUndo($first);;
	}
//	Добавить слепок для отмены действия пользователя
	static function add($message, $source, $data)
	{
		$data['message']	= $message;
		$data['source']		= $source;
		return undo::addUndo($data);
	}
//	Добавить сообщение о действия пользователя
	static function addLog($message, $source = '')
	{
		return undo::addUndo(array(
			'message'	=> $message,
			'source'	=> $source
		));
	}
	/********************************/
//	Заблокировать запись действий отмены
	static function lock()
	{
		global $_CONFIG;
		$_CONFIG[':undo:lock'] += 1;
	}
//	Разблокировать запись действий
	static function unlock()
	{
		global $_CONFIG;
		$_CONFIG[':undo:lock'] -= 1;
	}
	/********************************/
//	Получить тип текущего действия
	static function getUndoAction()
	{
		global $_CONFIG;
		return $_CONFIG[':undoAction'];
	}
	/********************************/
	private static function addUndo($data)
	{
		global $_CONFIG;
	
		if ($_CONFIG[':undo:lock']) return;;
		if ($_CONFIG[':undo'])
		{
			$_CONFIG[':undo_data'][]	= $data;
			return;
		}
	
		$d				= array();
		$d['user_id']	= userID();
		$d['userIP']	= userIP();
		$d['session']	= sessionID;
		$d['date']		= time();
	
		if (is_array($data) && $data['action']){
			list($action, $id)	= explode(':', undo::getUndoAction());
			$d['action'] = ($action == 'undo')?'redo':'undo';
		}
		
		$d['message']	= $data['message'];
		$d['source']	= $data['source'];
		$d['data']		= $data;
	
		$db	= undo::db();
		$db->update($d);
	}
/*********************************/
	static function doUndo($id, $action)
	{
		if (!access('write', "undo:$id")) return;
		
		$db		= undo::db();
		$data	=$db->openID($id);
		$action	= $data['action'];
		$undo	= $action?$data['data']:NULL;
		if (!$undo || !$undo['action']) return;
	
		global $_CONFIG;
		$_CONFIG[':undoAction']	= "$action:$id";
		$bOK	= module($undo['action'], $undo['data']);
		$_CONFIG[':undoAction']	= '';
		accessUpdate();
	
		if (!$bOK)
			return "Неудачная отмена действия '$undo[action]'";
	
		$clean	= $undo['clean'];
		if ($clean) module($clean, $undo['data']);
		
		$data['action']		= '';
		$data['message']	= "$action: $data[message]";
		$data['data']		= array();
		$db->setValues($id, $data);

		return "Отмена действия $data[message]";
	}
};
?>