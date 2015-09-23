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
		$level	= config::get(':undo');
		if ($level == 0){
			config::set(':undo_data', array());
		}
		$level += 1;
		config::set(':undo', $level);
	}
//	Окончить сбор действий, создать запись отмены
	static function end()
	{
		$level	= config::get(':undo');
		$level -= 1;
		config::set(':undo', $level);
		if ($level) return;
		
		$data	= config::get(':undo_data');
		config::set(':undo_data', array());
		
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
		$lock	= config::get(':undo:lock', 0);
		config::set(':undo:lock', $lock + 1);
	}
//	Разблокировать запись действий
	static function unlock()
	{
		$lock	= config::get(':undo:lock', 0);
		config::set(':undo:lock', $lock - 1);
	}
	/********************************/
//	Получить тип текущего действия
	static function getUndoAction()
	{
		return config::get(':undoAction');
	}
	/********************************/
	private static function addUndo($data)
	{
		if (config::get(':undo:lock')) return;
	
		$level	= config::get(':undo');
		if ($level)
		{
			$undo_data	= config::get(':undo_data', array());
			$undo_data[]= $data;
			config::set(':undo_data', $undo_data);
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
	
		config::set(':undoAction', "$action:$id");
		$bOK	= module($undo['action'], $undo['data']);
		config::set(':undoAction', '');
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