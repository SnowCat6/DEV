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
			if (hashData($data['fields']) != hashData($fields))
			{
/*
				dataMerge($fields, $data['fields']);
				$data['fields']	= $fields;
				dataMerge($data, $d);
				unset($data[$key]);
				removeEmpty($data);
*/				
				$data['id']		= $db->id();
				$data['updated']= 0;
				$data['pass']	= 0;
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
			}
			
		}else{
//			removeEmpty($data);
			$id	= $db->update($d);
			if ($id){
				$statistic[$type]['add']++;
				if ($synch)
				{
					$cache["$type:$article"]	= $id;
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
		return $id;
	}
};
?>