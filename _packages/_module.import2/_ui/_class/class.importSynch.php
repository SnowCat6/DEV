<?
//	Все обработанные позиции таблицы импорта синхронизировать с сайтом
class importSynch
{
	static function doSynch($action)
	{
			
		$synch	= importCommit::getSynch();
		if ($synch->lockTimeout()) return;
		
		$synch->lock();
		$synch->read();
		if ($synch->getValue('status') != 'complete')
		{
			$synch->setValue('synchStatus', 'waitCommit');
			if (!$synch->write()) return;
			$synch->unlock();
			
			if (!importCommit::doCommit($action))
				return false;

			if ($synch->lockTimeout()) return;

			$synch->lock();
			$synch->read();
		}
		
		$ret	= self::doSynch2($synch);
		if ($ret)
		{
		$ret	= self::doSynchDeleted($synch);
		}
		if ($ret)
		{
			$synch->setValue('synchStatus', 'complete');
			$synch->setValue('cacheCommit', NULL);
		}

		if ($synch->write()){
			$synch->unlock();
		}
		return $ret;
	}
	static function doSynch2(&$synch)
	{
		$import	= new importBulk();
		$db		= $import->db();
		$ddb	= module('doc');

		$synch->setValue('synchStatus', 'synch');
		$synch->write();
		
		$cache	= importCommit::getCache($synch);
		if (!is_array($cache)) return;
		
		//	documents not synch

//		$synchDelete	= $synch->getValue('synchDelete');
//		if (!is_array($synchDelete))
//			$synchDelete	= array_flip(array_values($cache));
		
		$sql	= array();
//		$sql[]	= "`doc_type` ='product'";
		$sql[]	= "`pass` = 1 AND `updated` = 0 AND `delete`=0";
		$db->open($sql);
		while($data = $db->next())
		{
			if (sessionTimeout() < 5)
			{
				importCommit::setCache($synch, $cache);
//				$synch->setValue('synchDelete', $synchDelete);
				return;
			}
			
			undo::lock();
			$update				= $data['update'];
			$update['visible']	= 1;
			
			$parent	= $data['parent_article'];
			if ($parent)
			{
				$parent	= $cache["catalog:$parent"];
				if (!$parent) $parent = $cache["page:$parent"];
				if ($parent) $update['+property'][':parent'] = $parent;
			}

			$d				= array();
			$d['updated']	= 1;
			
			$iid	= $data['doc_id'];
			if ($iid)
			{
				if ($update){
					$iid	= module("doc:update:$iid:edit", $update);
				}
			}else{
				$iid	= module("doc:update::add:$data[doc_type]", $update);
				if ($iid)
				{
					$d['doc_id']	= $iid;
					$cache["$data[doc_type]:$data[article]"]	= $iid;
//					$synch->flush();
				}
			}
			undo::unlock();
			if (!$iid) continue;
	
			if ($data['image'])
			{
				$source	= $data['image'];
				$dest	= basename($source);
				module('translit', $dest);
				$dest	= $ddb->folder($iid) . "/Title/$dest";
				copy2folder($source, $dest);
			}
	
			$db->setValues($db->id(), $d);
//			if (isset($synchDelete[$iid])) unset($synchDelete[$iid]);
//			$db->setValue($db->id(), 'updated', 1);
		}
		importCommit::setCache($synch, $cache);
//		$synch->setValue('synchDelete', $synchDelete);
		$synch->write();
		
		if (sessionTimeout() < 10) return;
		
		//	HIDE NOT EXISTS
		$ids	= array();
		$db->open('doc_id > 0');
		if ($db->rows() == 0) return;
		
		$exists	= array_flip(array_values($cache));
		while($data = $db->next())
		{
			$iid	= $data['doc_id'];
			if (isset($exists[$iid])) unset($exists[$iid]);
		}
		// UNIMPORTED set value to zero
		if ($exists){
			$ddb->setValue($exists, 'quantity', 0);
		}
//		print_r(count($exists));

/*
		foreach($synchDelete as $id => $ix)
		{
			if (sessionTimeout() < 5)
			{
				$synch->setValue('synchDelete', $synchDelete);
				return;
			}

			undo::lock();
			$d		= array('quantity' => 0);
			$iid	= module("doc:update:$id:edit", $d);
			if ($iid) unset($synchDelete[$iid]);
			undo::unlock();
		}
*/
		$synch->setValue('synchDelete', $synchDelete);
		$synch->write();
		
		clearCache();
	
		return true;
	}
	static function doSynchDeleted(&$synch)
	{
		$db		= module('doc');
		$deleted= importCommit::getDeleted();
		$ids	= makeIDS($deleted);
		
		$data	= array(
				'quantity'	=> 0,
//				'visible'	=> 0,
				);
		
		$key	= $db->key();
		$db->updateRow($db->table, $data, "WHERE $key IN ($ids)");

		undo::lock();
		$duplicates	= importCommit::getDupless();
		foreach($duplicates	as $ix=>$id)
		{
			if (sessionTimeout() < 5)
			{
				importCommit::setDupless($synch, $duplicates);
				undo::unlock();
				return;
			}
				
			module("doc:update:$id:delete");
			unset($duplicates[$ix]);
		}
		importCommit::setDupless($synch, $duplicates);
		undo::unlock();
		
		return true;
	}
}

class importSynch2
{
	static function doSynch($action)
	{

	$db		= new importBulk();
	$ddb 	= module('doc');

	$rows	= 0;
	set_time_limit(10*60);
	undo::lock();
	
	//	Обработаные похиции
	$pass		= array();
	//	Необходимо добавить родителей
	$parentLink	= array();
	
	$sql	= array();
	$sql[]	= '`ignore`=0 AND `updated`=0';

	if ($import['noAddCatalog'])	$sql[]	= "(`doc_id`<>0 OR `doc_type`<>'catalog')";
	if ($import['noUpdateCatalog'])	$sql[]	= "(`doc_id`=0 OR `doc_type`<>'catalog')";

	if ($import['noAddProduct'])	$sql[]	= "(`doc_id`<>0 OR `doc_type`<>'product')";
	if ($import['noUpdateProduct'])	$sql[]	= "(`doc_id`=0 OR `doc_type`<>'product')";
	
	$bAddToMap			= $import['addToMap']?'map':'';
	$bReplacePropertty	= $import['replaceProperty']?true:false;
	$bReplaceName		= $import['replaceName']?true:false;
	
	$ids	= array();
	$db->open('`delete`=1');
	while($data = $db->next())
	{
		$id	= $data['doc_id'];
		if ($id) module("doc:update:$id:delete");
		$ids[]	= $db->id();
	}
	if ($ids) $db->delete($ids);

////////////////////////////

	$prices		= getCacheValue(':price');
	$passImport	= array();
	
	$dbDoc		= module('doc');
	//	Пройти по всем запясям импорируемого списка
	$db->open($sql);
	while($data = $db->next())
	{
////////////////////////////
		//	Заполнить $d данными для импорта
		$d		= array();
		$fields	= $data['fields'];
		$image	= $fields['image'];
		
		$d['title']		= $fields['name'];

		foreach($prices as $field){
			$fieldName		= $field[0];
			$d[$fieldName]	= parseInt($fields[$fieldName]);
		}

		$d['fields']										= $fields[':fields'];
		$d['fields']['any']['import'][':raw']				= $fields;
//		$d['fields']['any']['import'][':raw']['delivery']	= $fields['delivery'];
		dataMerge($d, $d[':data']);
		
		//	Если надо заменить свойства, заменяем
		if ($replaceProperty){
			$d['+property']	= $fields[':property'];
		}else{
			$d[':property']	= $fields[':property'];
		}
		//	Почистить перечень артикулов
		$article	= importMergeArticles(explode(',', $data['article']));
		//	Найти идентификатор документа среди возможно обновленных
		foreach($article as $v2)
		{
			$iid= $pass[$data["doc_type"]][":$v2"];
			if (!$iid) continue;
			$data['doc_id'] = $iid;
			break;
		}
		//	Попробовать подставить родителя
		$needLinkParent	= '';
		if ($data['parent_doc_id'] == 0 &&
			$fields['parent'])
			{
				$parentArticle	= $fields['parent'];
				$parentID		= $pass['catalog'][":$parentArticle"];
				if ($parentID){
					$data['parent_doc_id']	= $parentID;
				}else{
					$needLinkParent	= $parentID;
				}
		}
		//	Поместить в карту сайта, если задано настройками
		if ($bAddToMap &&
			$data['doc_type'] == 'catalog' &&
			$data['parent_doc_id'] == 0 &&
			$needLinkParent  == '')
		{
			$d['+property']['!place']	= $bAddToMap;
		}
		//	Если документ есть, обновитьь
		if ($data['doc_id'])
		{
			//	Если название заменять не надо, то удаляем из входных данных
			if (!$bReplaceName) unset($d['title']);

			$doc= $ddb->openID($data['doc_id']);
			$a	= $doc['fields']['any'];
			$a	= $a['import'][':importArticle'];
			
			//	Объеденить артикулы
			$article	= importMergeArticles($article, explode(',', $a));
			$d['fields']['any']['import'][':importArticle']	= implode(', ', $article);
			
			//	Добавить родителя
			if ($data['parent_doc_id']){
				$d[':property'][':parent']	= $data['parent_doc_id'];
			}
			//	Обновить документ
			if ($iid = moduleEx("doc:update:$data[doc_id]:edit", $d))
			{
				$passImport[]	= $db->id();
//				$id	= $db->id();
//				$db->setValues($id, array('updated' => 1, 'doc_id'=>$iid));
			}
		}else{
			$d['fields']['any']['import'][':importArticle']	= implode(', ', $article);
			//	Добавить документ
			if ($iid = moduleEx("doc:update:$data[parent_doc_id]:add:$data[doc_type]", $d))
			{
				$passImport[]	= $db->id();
//				$id	= $db->id();
//				$db->setValues($id, array('updated' => 1, 'doc_id'=>$iid));
			}else{
//				module('display:message');
//				print_r($d);
//				die;
			}
		}
		//	Добавить в обновленные для возможного повторного прохода
		if ($iid)
		{
			foreach($article as $v2){
				$pass[$data['doc_type']][":$v2"]	= $iid;
			}
			if ($needLinkParent){
				$parentLink[$iid][$needLinkParent]	= $needLinkParent;
			}
			//	Images
			if ($image)
			{
				$image		= basename($image);
				$imagePath	= importFolder . "/images/$image";
				if (file_exists($imagePath))
				{
					$dest	= $dbDoc->folder($iid) . "/Title/$image";
					if (filemtime($imagePath) != filemtime($dest)){
						copy2folder($imagePath, $dest);
					}
//					echo "$imagePath $dest"; die;
				}
			}
		}
////////////////////////////
//		if ($rows++ == 5) return;
		if (sessionTimeout() < 10) break;
	}
	//	Привязать товары к каталогам
	foreach($parentLink as $iid => $parentArticle)
	{
		$parentID	= $pass['catalog'][":$parentArticle"];
		if (!$parentID) continue;
		
		$d	= array();
		$d[':property'][':parent']	= $parentID;
		m("doc:update:$iid:edit", $d);
	}
	$db->delete($passImport);
	//	Clear all doc's caches
	undo::unlock();
	m('doc:clear');
	}
};
?>


<? function doImportSynch($import)
{
////////////////////////////
}
function importMergeArticles($a1=NULL, $a2=NULL)
{
	$ret	= array();
	if (is_array($a1)){
		foreach($a1 as $v){
			if ($v = trim($v)) $ret[$v] = $v;
		}
	}
	if (is_array($a2)){
		foreach($a2 as $v){
			if ($v = trim($v)) $ret[$v] = $v;
		}
	}
	return $ret;
}
?>
