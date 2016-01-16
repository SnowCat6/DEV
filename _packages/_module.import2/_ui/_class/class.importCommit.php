<?
//	Сопоставить товары в базе импорта с товарами на сайте

class importCommit
{
	static function getSynch()
	{
		return  new baseSynch(importFolder . '/importBulk/synch.txt');
	}
	static function clear()
	{
		importBulk::clear();
		$synch	= self::getSynch();
		$synch->delete();
	}
	static function reset()
	{
		$synch	= self::getSynch();
		$synch->delete();
	}
	static function doCommit($action)
	{
		$synch	= self::getSynch();
		if ($synch->lockTimeout()) return;

		$synch->lock();
		$synch->read();
///////////////////////////////////
		$ret	= self::doSynchCommit($synch, $action);
		if ($ret)
		{
			$synch->setValue('status', 'complete');
		}
///////////////////////////////////
		//	Записать данные
		if ($synch->write()){
			$synch->unlock();
		}
		return $ret;
	}
	static function doSynchCommit(&$synch, $action)
	{
		$import	= new importBulk();
		$db		= $import->db();

		$db->open('`pass` = 0');
		if ($db->rows() == 0) return true;
		
		$cache	= $synch->getValue('cacheCommit');
		if (!is_array($cache) || $synch->getValue('status') == 'cache')
		{
			if (!is_array($cache)) $cache	= array();
			$synch->setValue('status', 'cache');
			$synch->write();
			
			$seek	= (int)$synch->getValue('cacheSeek');
			$ddb	= module('doc:find', array('type'=>'catalog,page,product'));
			$ddb->seek($seek);
			while($data = $ddb->next())
			{
				if (sessionTimeout() < 5)
				{
					$synch->setValue('cacheSeek', $seek);
					$synch->setValue('cacheCommit', $cache);
					return;
				}
				
				$type	= $data['doc_type'];
	
				$fields	= $data['fields'];
				$any	= $fields['any'];
				$im		= $any['import'];
				if (!$im) $im = array();

				$article= $im[':importArticle'];
				if ($article)
				{
					$cache["$type:$article"]	= $ddb->id();
				}
				++$seek;
			}
			$synch->setValue('cacheCommit', $cache);
			$synch->setValue('cacheSeek', $seek);
			$synch->setValue('status', '');
		}
		
		$synch->setValue('status', 'commit');
		$synch->write();

		$ddb	= module('doc');
		while($data = $db->next())
		{
			if (sessionTimeout() < 5) return;
			
			$d			= array();
			$d['id']	= $db->id();
			$d['pass']	= 1;
			
			$article	= "$data[doc_type]:$data[article]";
			$docID		= (int)$cache[$article];

			$d['doc_id']	= $docID;
			$upd			= self::compare($ddb, $d, $data);
			if ($upd)
			{
				$d['updated']	= 0;
				$d['update']	= $upd;
			}else{
				$d['updated']	= 1;
			}
			$db->update($d);
		}
		return true;
	}
	
	static function compare($db, &$d, $data)
	{
		$fields	= $data['fields'];
		$id		= $d['doc_id'];
		$doc	= $id?$db->openID($id):array();
		
		$updated= array();
		
		switch($data['doc_type'])
		{
			case 'product':
			$price	= round($fields['price'], 2);
			if ($doc['price'] == $price)	break;
			$updated['price']	= $price;
			break;
		}

		if ($doc['title'] != $fields['name'])
		{
			$updated['title']	= $fields['name'];
		}
		
		$property	= $fields[':property'];
		if (is_array($property))
		{
			$docProperty	= module("prop:get:$id");
			foreach($property as $name => $val)
			{
				if (!is_array($val)) $val	= explode(', ', $val);
				foreach($val as $n=>$v) $val[$n] = trim($v);
				removeEmpty($val);

				$docVal	= explode(', ', $docProperty[$name]);
				foreach($docVal as $n=>$v) $docVal[$n] = trim($v);
				removeEmpty($docVal);
				
				$diff	= array_diff($val, $docVal);
				if ($diff)
				{
					$updated[':property'][$name] 	= implode(', ', $val);
				}
/*
				$p	= array();
				foreach($val as $v)
				{
					if (array_search($v, $docVal) !== false) continue;
					$p[] 	= $v;
				}
				if ($p) $updated['+property'][$name] 	= implode(',', $p);
*/
			}
		}
		
		$d2	= $fields[':data'];
		if (!is_array($d2)) $d2 = array();
		foreach($d2 as $name => $val)
		{
			if ($data[$name] == $d[$name]) continue;
			$updated[':data'][$name]	= $val;
		}
		
		if (!$doc)
		{
			$updated['fields']['any']['import'][':importArticle']	= $data['article'];
			$updated['doc_type']	= $data['doc_type'];
		}
		
		return $updated;
	}
};

class importCommit2
{
	static function doCommit($action)
	{
//	set_time_limit(5*60);

	$import	= new importBulk();
	$db		= $import->db();
	$docs	= array();
	
	$db->open('`pass`=0');
	if ($db->rows() == 0) return;
	
	$ddb	= module('doc:find', array('type'=>'catalog,page,product'));
	while($data = $ddb->next())
	{
		$type	= $data['doc_type'];
		switch($data['doc_type'])
		{
			case 'catalog':
			case 'page':
				$type	= 'catalog';
//				$docs[$type][$data['title']]	= $ddb->id();
/*				
				$path	= getPageParents($ddb->id());
				$article= array();
				foreach($path as $iid)
				{
					$d	= module("doc:data:$iid");
					$article[]	= $d['title'];
				}
*/
				$article[]	= $data['title'];
				$article	= implode('/', $article);
//				$article	= importArticle($article);
//				$docs[$type][$article]	= $ddb->id();
		}

		$fields	= $data['fields'];
		$any	= $fields['any'];
		$im		= $any['import'];
		if (!$import) $import = array();

		//	Получить артикул товара
		$article= $im[':importArticle'];
		if (!$article) continue;
		
		//	Запомнить артикул
		$article	= explode(',', $article);
		foreach($article as $v){
			$v = trim($v);
			if ($v) $docs[$type][":$v"]	= $ddb->id();
		}
	}
	
	//	Родители
	$parents= array();
	
	$table	= $db->table();
	$db->exec("UPDATE $table SET `doc_id`=0, `parent_doc_id`=0 WHERE `pass`=0");

	$catalogs	= array();
	$db->open("`doc_type` IN ('catalog')");
	while($data = $db->next()){
		$catalogs[":$data[article]"]	= $db->id();
	}

	$db->open('`pass`=0');
	while($data = $db->next())
	{
		$fields	= $data['fields'];
		$article= $data['article'];
		//	Найти по артикулу код товара
		$article= explode(', ', $article);
		foreach($article as $v)
		{
			$v		= trim($v);
			$docID	= $docs[$data['doc_type']][":$v"];
			if ($docID) break;

			switch($data['doc_type'])
			{
				case 'catalog':
				case 'page':
					$docID	= $docs['catalog'][$v];
			}
			if ($docID) break;
		}

		$d	= array();
		$d['pass']	= 1;
		//	Если элемент с артикулом есть, присвоить
		if ($docID != $data['doc_id']) $d['doc_id']	= $docID;
		
		$parent		= $fields['parent'];
		$parentID	= importDoSynchCatalog($import, $docs, $catalogs, $parent, $parent, "");
		if ($parent && $parentID != $data['parent_doc_id']) $d['parent_doc_id']	= $parentID;
/*		
		$parent		= $fields['parent2'];
		$parentID	= importDoSynchCatalog($import, $docs, $catalogs, $parent, $parent, $fields['parent']);
		if ($parent && $parentID != $data['parent_doc_id']) $d['parent_doc_id']	= $parentID;
		
		$parent		= $fields['parent3'];
		$parentID	= importDoSynchCatalog($import, $docs, $catalogs, $parent, $parent, $fields['parent2']);
		if ($parent && $parentID != $data['parent_doc_id']) $d['parent_doc_id']	= $parentID;
*/		
		if ($d)	$db->setValues($db->id(), $d);
	}
	}
};

?>


<?

function importDoSynchCatalog(&$import, &$docs, &$catalogs, $name, $article, $parent)
{
	$article	= importArticle($article);
	$parent		= importArticle($parent);
	if (!$name || !$article) return;
	
	$docID	= $docs['catalog'][":$article"];
	if (!$docID) $docID	= $docs['catalog'][$article];

/*	
	$synch		= NULL;
	$f			= array();
	$f['parent']= $parent;
	$iid	= $import->addItem($synch, 'catalog', $article,  $name, $f);
	if ($iid){
		$catalogs[":$article"]	= $iid;
		$import->db()->setValue($iid, 'doc_id', $docID);
	}
*/	
	return $docID;
}
?>
