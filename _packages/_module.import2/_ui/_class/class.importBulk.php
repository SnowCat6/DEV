<?
//	Класс добавления сырых данных в общую таболицу
class importBulk
{
	static function db(){
		return new dbRow('import_tbl', 'import_id');
	}
	/////////////////
	static function clear()
	{
		$db		= self::db();
		$table	= $db->table();
		$db->exec("DELETE FROM $table");

		importCommit::reset();
	}
	static function getSynch()
	{
		return  new baseSynch(importFolder . '/importBulk/import.txt');
	}
	
	static function reset()
	{
		$synch	= self::getSynch();
		$synch->delete();
		importCommit::reset();
	}

	//	Начать импорт товаров с самого начала
	static function beginImport()
	{
		$synch	= self::getSynch();
		if ($synch->lockTimeout()) return;
		
		$synch->read();
		$status	= $synch->getValue('status');
		if ($status && $status == 'import')
			return;

		$synch->lock();
		$synch->setValue('status', 'import');
		
		$db		= self::db();
		$table	= $db->table();
		$db->exec("UPDATE $table SET `import` = 0");
		
		$synch->write();
		importCommit::reset();
	}
	static function endImport($currentSynch)
	{
		$synch	= self::getSynch();

		$locks	= array();
		event('import.source', $locks);
		foreach($locks as $name => &$s)
		{
			$s->read();
			if ($s->getValue('status') == 'complete')
				continue;

			$synch->unlock();
			return;
		}
//		$synch->unlock();
		$synch->delete();
		return true;
	}

	/////////////////
	static function addItem(&$synch, $type, $article, $name, $fields)
	{
		$db			= self::db();
		$key		= $db->key;
		if ($synch)	$statistic	= $synch->getValue('statistic');
		else $statistic = array();
		
		$name		= trim($name);
		if (!$name)
		{
			$statistic[$type]['error']++;
			if ($synch){
				$synch->log("No $type name: $article");
				$synch->setValue('statistic', $statistic);
			}
			return;
		}
		
//		$fields['name']	= $name;
		$article		= trim($article);
		if (!$article)
		{
			$statistic[$type]['error']++;
			if ($synch){
				$synch->log("No $type article: $name");
				$synch->setValue('statistic', $statistic);
			}
			return;
		}
		
		$d	= array(
			'article'	=> $article,
			'doc_type'	=> $type,
			'name'		=> $name,
			'fields'	=> $fields,
			'date'		=> time()
		);
		
		if ($synch)
		{
			$cache	= $synch->getValue('bulkImportCache');
			if (!is_array($cache))
			{
				$cache	= array();
				$db->open();
				while($data = $db->next())
				{
					$cacheID	= $db->id();
					$cacheType	= $data['doc_type'];
					$cacheArticle=$data['article'];
					$cache["$cacheType:$cacheArticle"]	= $cacheID;
				}
				$synch->setValue('bulkImportCache', $cache);
			}
			$id	= $cache["$type:$article"];
			
			if ($id) $data	= $db->openID($id);
			else $data = NULL;
		}else{
			$a		= dbEncString($db, $article);
			$db->open("`article`=$a AND `doc_type`='$type'");
			$data	= $db->next();
		}

		if ($data)
		{
			$id	= $db->id();;
			if (hashData($data['fields']) != hashData($fields))
			{
				$data['id']		= $id;
				$data['updated']= 0;
				$data['pass']	= 0;
				$data['import']	= 1;
				$data['fields']	= $fields;
				
				$id	= $db->update($data);
				if ($id){
					$statistic[$type]['update']++;
				}else{
					$statistic[$type]['error']++;
					if ($synch){
						$error	= $db->error();
						$synch->log("Update error: $error");
					}
				}
			}else{
				$statistic[$type]['pass']++;
				if ($id) $db->setValue($id, 'import', 1);
			}
			
		}else{
//			removeEmpty($data);
			$id	= $db->update($d);
			if ($id){
				$statistic[$type]['add']++;
				if ($synch)
				{
					$cache["$type:$article"]	= $id;
					$synch->setValue('bulkImportCache', $cache);
				}
			}else{
				$statistic[$type]['error']++;
				if ($synch){
					$error	= $db->error();
					$synch->log("Add error: $error");
				}
			}
		}
		if ($synch){
			$synch->setValue('statistic', $statistic);
			$synch->flush();
		}
		unset($cache);
		return $id;
	}
};
?>