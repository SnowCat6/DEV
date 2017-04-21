<?
//	Проверить права доступа пользователя к документам
//	$mode - проверяемый режим
//	$data - результат разбора регулярного выражения
function module_doc_access($mode, &$data)
{
	$id	= (int)$data[1];
	
	switch($mode){
		//	Разрешить чтение всем пользователям
		case 'read': 
			return true;
		//	Проверить права на добавление
		case 'add':
			return module_doc_add_access($mode, $data);
		//	Проверить права на визуальное редактирование
		case 'edit':
			return hasAccessRole('edit,admin,developer,writer,manager,SEO');
		//	Проверить права на изменение документа
		case 'write':
			return hasAccessRole('admin,developer,writer,manager,SEO');
		//	Проверить права на удаление документа
		case 'delete':
			if ($id){
				$db = module('doc');
				$d	= $db->openID($id);
				return $d['fields']['denyDelete'] != 1 && hasAccessRole('admin,developer,writer');
			}
			return hasAccessRole('admin,developer,writer');
	}
}

function module_doc_add_access($mode, &$data)
{
	if ($mode != 'add') return false;

	$baseType	= $data[1];
	$newType	= $data[2];
	$newTemplate= $data[3];
	
	if ((int)$baseType){
		$db = module('doc');
		$d	= $db->openID($baseType);
		$baseType	= $d['doc_type'];

		$allowAddType	= docConfig::getTemplate("$d[doc_type]:$d[template]");
		$allowAddType	= $allowAddType['allowAddType'];
		if ($allowAddType["$newType:$newTemplate"]) return true;
	}else
	if (!$baseType) $baseType = '';
	
	switch("$baseType:$newType")
	{
		case 'page:':
		case ':page':
		case 'catalog:page':
		case 'catalog:':
		case ':catalog':
			if ($d && $newType){
				$access	= $d['fields']['access'];
				return $access['page'] && hasAccessRole('admin,developer,writer');
			}
			return hasAccessRole('admin,developer,writer');
		case 'page:page':
		case 'page:article':
			if ($d){
				$access	= $d['fields']['access'];
				return $access[$newType] && hasAccessRole('admin,developer,writer');
			}
			return hasAccessRole('admin,developer,writer');

		case 'article:':
		case ':article':
		case 'product:':
		case ':product':
		case 'page:catalog':
		case 'catalog:product':
		case 'catalog:catalog':
			return hasAccessRole('admin,developer,writer,manager');

		case 'page:comment':
		case 'article:comment':
			if ($d){
				$access	= $d['fields']['access'];
				return $access[$newType];
			}
			return false;
		case 'product:comment':
			return true;
	}
	return false;
}
//	Проверить путь к файлу на предмет возможной записи
function module_doc_file_access(&$mode, &$data)
{
	//	Если это новый документ и идентификатор пользователя совпадает, то дать доступ
	if (preg_match('#new(\d+)#', $data[1], $var)){
		if (userID() == $var[1]) return true;
	}
	//	Проверить стандартные права
	$id	= (int)$data[1];
	return access($mode, "doc:$id");
}
//	При изменении файлов в хранилище документа, обновить кеш
function module_doc_file_update(&$val, &$path)
{
	$id	= filePath2doc($path);
	if (is_null($id)) return;
	m("doc:cacheClear:$id");
}
//	Получить идентификатор документа из пути файла
function filePath2doc(&$path){
	if (preg_match('#/doc/(\d+)/(File|Gallery|Image|Title)#', $path, $var))
		return (int)$var[1];
	return NULL;
}
?>