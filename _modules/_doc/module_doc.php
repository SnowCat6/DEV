<?
//	Модкль работы с документами
function module_doc($fn, &$data)
{
	//	Присоеденяемый ко всем SQL запрос
	$sql		= array();
	//	Если есть опция показывать скрытые, то она доступна только элите, для всех остальных игнорируется
	if (getValue('showHidden') && hasAccessRole('admin,developer,writer,manager') ){
	}else{
		$sql[]		= "`visible` = 1";
	}
	//	База данных пользователей
	$db 		= new dbRow('documents_tbl', 'doc_id');
	$db->sql	= implode(' AND ', $sql);
	$db->images = images.'/doc';
	$db->url 	= 'page';
	$db->setCache();

	//	Если не не передано никаких параметров, то вернуть объект базы данных.
	if (!$fn){
		if (is_array($data)) $db->setData($data);
		return $db;
	}
	//	Разделить параметры
	list($fn, $val)  = explode(':', $fn, 2);
	//	Получить название функции для вызоба обработчика
	$fn = getFn("doc_$fn");
	//	Выполнить функцию
	return $fn?$fn($db, $val, $data):NULL;
}
//	Очистить кеш документов
function doc_clear($db, $id, $data)
{
	$table	= $db->table();
	$db->exec("UPDATE $table SET `cache` = NULL");
	m('prop:clear');
	m('cache:clear');
	clearCache();
}
//	Вернуть найденные по запросу документы
function doc_find(&$db, &$val, &$search){
	$db->open(doc2sql($search));
	return $db;
}
?>
