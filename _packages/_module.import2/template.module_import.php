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
}
function importMakeArticle($data)
{
	$a	= $data['fields']['any'];
	$a	= $a['import'][':importArticle'];
	if ($a) return importArticle($a);
	
	$db	= module("doc");
	$db->setData($data);
	$id	= $db->id();
	
	return "page$id";
}
function importArticle($article){
	$article	= str_replace(',', '', $article);
	$article	= preg_replace('#\s+#', ' ', $article);
	$article	= trim($article);
	return $article;
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
		if ($synch)	$statistic	= $synch->getValue('statistic');
		else $statistic = array();

		$name		= trim($name);
		if (!$name){
			$statistic[$type]['error']++;
			if ($synch){
				$synch->log("No $type name: $article");
				$synch->setValue('statistic', $statistic);
			}
			return;
		}
		$fields['name']	= $name;
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
		
		$a		= dbEncString($db, $article);
		$db->open("`article`=$a AND `doc_type`='$type'");
		$data	= $db->next();
		if ($data)
		{
			dataMerge($fields, $data['fields']);
			$data['fields']	= $fields;
			dataMerge($data, $d);
			unset($data[$key]);
			removeEmpty($data);
			$data['id']		= $db->id();
			$data['updated']= 0;
			$data['pass']	= 0;
			
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
//			removeEmpty($data);
			$id	= $db->update($d);
			if ($id){
				$statistic[$type]['add']++;
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
