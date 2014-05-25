<?
//	Задать папку для импорта файлов
define('importFolder', localRootPath.'/_exchange');

function module_import($fn, &$data)
{
	if (!access('write', 'doc:')) return;
	
	$db	= new importBulk();
	$db->addItem('catalog', 'main', 'Главный каталог', array(
		'price'=>10,
		'parent'=>''
	));

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
	function addItem($type, $article, $name, $fields)
	{
		if (!$article) $article = $name;

		$db		= $this->db();
		$a		= dbEncString($db, $article);
		$db->open("`article`=$a AND `doc_type`='$type'");
		$data	= $db->next();
		if ($data)
		{
			dataMerge($fields, $data['fields']);
			$data['fields']	= $fields;
			$d	= array(
				'id'		=> $db->id(),
				'article'	=> $article,
				'name'		=> $name,
				'fields'	=> $fields
			);
			$db->update($d);
		}else{
			$d	= array(
				'article'	=> $article,
				'doc_type'	=> $type,
				'name'		=> $name,
				'fields'	=> $fields
			);
			$db->update($d);
		}
	}
};
?>
