<?
//	doc:update::add:page		=> ret id, sample 20
//	doc:update:20				= ret 20, ok
//	doc:update:20:add:page		=> ret id, new hierarhy, added page
//	doc:update:20:add:article	=> ret id, new hierarhy, added article
function doc_update(&$db, $val, &$data)
{
	$db->sql	= '';

	list($id, $action, $type) = explode(':', $val, 3);
	$fn	= getFn("doc_update_$action");
	if ($fn) return $fn($db, $val, $data);
}
?>
<?
//	Скопировать корректные поля из данных в массив для обновления базы данных
function docPrepareData($db, $baseData, $data, &$d)
{
	$bTitleSearch	= false;
	
	if (isset($data['title'])){
		$bTitleSearch		= true;
		$d['title']			= (string)$data['title'];
//		$d['searchTitle']	= docPrepareSearch($d['title']);
	}
	//	Подготовка базовый данных, проверка корректности
	//	Видимость
	if (isset($data['visible'])){
		$d['visible']	= (int)$data['visible'];
	}
	//	Шаблон документа
	if (isset($data['template'])){
		$d['template']	= "$data[template]";
	}
	//	Sime abstract local fields
	if (isset($data['fields']))
	{
		$d['fields']	= $baseData['fields'];
		//	SEO fields
		if (is_array($data['fields']['SEO']) && hasAccessRole('admin,developer,SEO')){
			$d['fields']['SEO'] = $data['fields']['SEO'];
		}
		if(isset($data['fields']['hiddenSearch'])){
			$bTitleSearch	= true;
			$d['fields']['hiddenSearch'] = (string)$data['fields']['hiddenSearch'];
		}
		if(isset($data['fields']['class']) && hasAccessRole('admin,developer,SEO')){
			$d['fields']['class'] = $data['fields']['class'];
		}
		if(isset($data['fields']['page']) && hasAccessRole('admin,developer')){
			$d['fields']['page'] = $data['fields']['page'];
		}
		if(isset($data['fields']['pageFn']) && hasAccessRole('admin,developer')){
			$d['fields']['pageFn'] = $data['fields']['pageFn'];
		}
		if(isset($data['fields']['access']) && hasAccessRole('admin,developer')){
			$d['fields']['access'] = $data['fields']['access'];
		}
		if(isset($data['fields']['denyDelete']) && hasAccessRole('admin,developer')){
			$d['fields']['denyDelete'] = $data['fields']['denyDelete'];
		}
		if(isset($data['fields']['redirect']) && hasAccessRole('admin,developer,SEO'))
		{
			$url = $data['fields']['redirect'];
			$url = preg_replace('#^.*://#',	'', $url);
			$url = preg_replace('#^.*/#',	'', $url);
			$url = preg_replace('#\..*#',	'',	$url);
			$url = preg_replace('#\s+#',	'',	$url);
			$d['fields']['redirect'] = $url?"/$url.htm":'';
		}
		if(isset($data['fields']['note'])){
			$d['fields']['note'] = $data['fields']['note'];
		}
		if(isset($data['fields']['any']))
		{
			$d['fields']['any'] = $baseData['fields']['any'];
			if (!is_array($d['fields']['any']))		$d['fields']['any']		= array();
			if (!is_array($data['fields']['any']))	$data['fields']['any']	= array();

			foreach($data['fields']['any'] as $name => $val){
				$d['fields']['any'][$name] = $val;
			}
			$baseData['fields']['any']	= $d['fields']['any'];
		}
	}
	
	if(isset($data['+fields']['any']))
	{
		$d['fields']['any'] = $baseData['fields']['any'];
		if (!is_array($d['fields']['any']))		$d['fields']['any']		= array();
		if (!is_array($data['+fields']['any']))	$data['+fields']['any']	= array();

		foreach($data['+fields']['any'] as $name => $val)
		{
			dataMerge($val, $d['fields']['any'][$name]);
			$d['fields']['any'][$name] = $val;
		}
		$baseData['fields']['any']	= $d['fields']['any'];
	}

	if ($data['doc_type'] && hasAccessRole('admin,developer')){
		$d['doc_type']	= $data['doc_type'];
	}
	//	Сортировка элементов
	if (isset($data['sort']) && hasAccessRole('admin,developer,SEO'))
	{
		$d['sort'] = (int)$data['sort'];
	}
	//	Дата публикации
	if (isset($data['datePublish']))
	{
		if ($data['datePublish']){
			$d['datePublish'] = makeDateStamp($data['datePublish']);
		}else{
			$d['datePublish'] = NULL;
		}
	}
	
	if ($bTitleSearch)
	{
		$title		= array();
		$title[]	= isset($d['title'])?$d['title']:$baseData['title'];
		$title[]	= isset($d['fields']['hiddenSearch'])?$d['fields']['hiddenSearch']:$baseData['fields']['hiddenSearch'];
		$d['searchTitle']	= docPrepareSearch(implode(' ', $title));
	}
}
function docBeforeUpdate($db, $action, $baseData, &$data, &$d)
{
	//	Пользовательская обработка данных
	if (!$d['doc_type'])
		$d['doc_type']	= $baseData['doc_type'];
		
	if (!isset($d['template']))
		$d['template']	= $baseData['template'];

	//	Пользовательская обработка данных
	$error	= '';
	$base	= array(&$d, &$data, &$error);
	event("doc.update:$action", $base);
	return $error;
}
//	Выполнить дополнительные действия с документом в других модулях
function docAfterUpdate($db, $iid, $data)
{
	//	Обновить ссылки на документ
	$links = $data[':links'];
	if (is_array($links)){
		$url = "/page$iid.htm";
		module("links:set:$url", $links);
	}

	//	Найти все названия начинающиеся с @ и сделать их свойствами
	foreach($data as $name=>$v)
	{
		if ($name[0] != '@') continue;
		unset($data[$name]);
		$name	= substr($name, 1);
		$data[':property'][$name]	= $v;
	}

	//	Добавить свойства
	$propAdd	= $data['+property'];
	//	Удалить свойства
	$propUnset	= $data['-property'];
	//	Заменить свойства, если имеются
	$prop 		= $data[':property'];
	dataMerge($prop, $data['property'], true);
	//
	if (is_array($propAdd))
	{
		foreach($propAdd as $name=>$val)
		{
			if (!isset($prop[$name])) continue;
			
			$val		= array_merge(explode(',', $val), explode(',', $prop[$name]));
			$prop[$name]= implode(', ', $val);
			unset($propAdd[$name]);
		}
	}
	
	if (is_array($prop)){
		moduleEx("prop:set:$iid", $prop);
	}
	if (is_array($propAdd)){
		moduleEx("prop:add:$iid", $propAdd);
	}
	if (is_array($propUnset)){
		moduleEx("prop:unset:$iid", $propUnset);
	}
	//	Очистить кеш
	clearCache('', "doc$iid");
//	$upd		= config::get('doc_update', array());
//	$upd[$iid]	= $iid;
//	config::set('doc_update', $upd);
}
?>
<?
//	+function doc_undo_edit
function doc_undo_edit($db, $id, $data)
{
	if (!access('write', 'undo')) return;
	if (!$id) return;

	$undo	= $db->openID($id);
	if (!$undo) return;
	
	$key		= $db->key;
	$data[$key]	= $id;
	unset($data[$key]);
	
	$data['cache']	= NULL;
	$db->setValues($id, $data, false);
	$db->clearCache($id);

	undo::add("\"$undo[title]\" $id изменен", "doc:$id",
		array('action' => "doc:undo_edit:$id", 'data' => $undo)
	);
		
	clearCache();
	
	return true;
}
//	+function doc_undo_delete
function doc_undo_delete($db, $id, $data)
{
	if (!access('write', 'undo')) return;
	if (!$id) return;

	$key		= $db->key;
	$data[$key]	= $id;

	$table		= $db->table;
	dbEncode($db, $db->dbFields, $data);
	$db->delete($id);
	$id = $db->insertRow($table, $data);

	undo::add("\"$data[title]\" $id добавлен", "doc:$id",
		array('action' => "doc:update:$id:delete")
	);

	clearCache();
	
	return $id > 0;
}