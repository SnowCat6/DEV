<?
addEvent('admin.tools.settings','doc:toolsConfig');
addUrl('admin_docconfig',		'doc:docConfig');

////////////////////
//	Типы документов
////////////////////
addUrl('admin_doctype', 'doc:docTypeEdit');

$docTypes 				= array();
$docTypes['page']		= 'Раздел:Разделы';
$docTypes['article']	= 'Статью:Статьи';
//	$docTypes['comment']	= 'Комментарий:Комментарии';
doc_config('', '', $docTypes);
?>

<?
function doc_config($db, $val, $data)
{
	$docTypes = getCacheValue(':docTypes') or array();
	foreach($data as $name => $val)
	{
		$docType = $docTemplate		= '';
		list($docType, $docTemplate)= explode(':', $name);
		$docTypes["$docType:$docTemplate"]	= $val;
	}
	setCacheValue(':docTypes', $docTypes);
}
?>
