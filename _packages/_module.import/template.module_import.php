<?
//	Задать папку для импорта файлов
define('importFolder', localRootPath.'/_exchange');

function module_import($fn, &$data)
{
	if (!access('write', 'doc:')) return;

	list($fn, $val) = explode(':', $fn, 2);
	$fn = getFn("import_$fn");
	return $fn?$fn($val, $data):NULL;
}
function parseInt(&$val){
	$v = preg_replace('#[^\d.,]#', '', $val);
	$v = (float)str_replace(',',  '.', $v);
	return $v;
}
function import_tools($fn, &$data){
	if (!access('add', 'doc:product')) return;
	$data['Импорт товаров']		= getURL('import');
	$data['Создать YandexXML']	= getURL('yandex-export');
}

class importBulk
{
	function importBulk(){
	}
	function db(){
		return new dbRow('import_tbl', 'import_id');
	}
	/////////////////
	function addItem(&$synch, $type, $article, $name, $fields)
	{
		$db			= $this->db();
		$key		= $db->key;
		$statistic	= $synch->getValue('statistic');

		$name		= trim($name);
		if (!$name){
			$statistic[$type]['error']++;
			$synch->log("No $type name: $article");
			$synch->setValue('statistic', $statistic);
			return;
		}
		$article= trim($article);
		if (!$article){
			$statistic[$type]['error']++;
			$synch->log("No $type article: $name");
			$synch->setValue('statistic', $statistic);
			return;
		}
		
		$d	= array(
			'article'	=> $article,
			'doc_type'	=> $type,
			'name'		=> $name,
			'fields'	=> $fields,
			'date'		=> time()
		);
		
		$a		= dbEncString($db, $article);
		$db->open("`article`=$a AND `doc_type`='$type'");
		$data	= $db->next();
		if ($data)
		{
			dataMerge($fields, $data['fields']);
			$data['fields']	= $fields;
			dataMerge($data, $d);
			unset($data[$key]);
			$data['id']		= $db->id();
			$data['updated']= 0;
			$id	= $db->update($data);
			if ($id){
				$statistic[$type]['update']++;
			}else{
				$statistic[$type]['error']++;
				$error	= $db->error();
				$synch->log("Update error: $error");
			}
		}else{
			$id	= $db->update($d);
			if ($id){
				$statistic[$type]['add']++;
			}else{
				$statistic[$type]['error']++;
				$error	= $db->error();
				$synch->log("Add error: $error");
			}
		}
		$synch->setValue('statistic', $statistic);
	}
};
?>
