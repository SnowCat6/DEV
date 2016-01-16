<?
//	Все обработанные позиции таблицы импорта синхронизировать с сайтом
class importSynch
{
	static function doSynch($action)
	{
		if (!importCommit::doCommit($action))
			return false;
			
		$synch	= importCommit::getSynch();
		if ($synch->lockTimeout()) return;

		$synch->lock();
		$synch->read();
		if ($synch->getValue('status') != 'complete')
		{
			$synch->unlock();
			return false;
		}
		
		$ret	= self::doSynch2($synch);
		if ($ret)
		{
			$synch->setValue('synchStatus', 'complete');
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

		$synch->setValue('synchStatus', 'synch');
		$synch->write();
		
		$db->open("`doc_type` ='product' AND `pass` = 1 AND `updated` = 0 AND `delete`=0 AND `ignore` = 0");
		while($data = $db->next())
		{
			if (sessionTimeout() < 5) return;
			
			undo::lock();
			$update	= $data['update'];
			$iid	= $data['doc_id'];
			if ($iid)
			{
				$iid	= module("doc:update:$iid:edit", $update);
			}else{
				$iid	= module("doc:update::add:$data[doc_type]", $update);
			}
			undo::unlock();
			if (!$iid) continue;
			
			$db->setValue($db->id(), 'updated', 1);
		}
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
