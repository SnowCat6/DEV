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
function parseInt($val){
	$v = preg_replace('#[^\d.,]#', '', $val);
	$v = (float)str_replace(',',  '.', $v);
	return round($v, 2);
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
?>
