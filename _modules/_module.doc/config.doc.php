<?
addUrl('page(\d+)', 				'doc:page:url');
addUrl('page_edit_(\d+)', 			'doc:edit');
addUrl('page_edit_(\d+)_([a-z\d]+)','doc:editable:edit');

addUrl('page_add_(\d+)', 	'doc:add');
addUrl('page_add', 			'doc:add');

addUrl('page_all_([a-z]+)',	'doc:all');
addUrl('page_all',			'doc:all');

addUrl('page_map',			'doc:map');

addUrl('search',			'doc:searchPage');
addUrl('search_([a-z]+)',	'doc:searchPage');
addUrl('search_([a-z]+)_(\w+)',	'doc:searchPage');

addEvent('document.compile','doc_compile');
addEvent('site.exit',		'doc:cacheFlush');
addEvent('admin.tools.add',	'doc:tools');
//	addEvent('site.getPageCacheName',	'doc:getPageCacheName');

addAccess('doc:(\d*)',				'doc_access');
addAccess('doc:(\d+):([a-z]+)',		'doc_access');
addAccess('doc:([a-z]+)',			'doc_add_access');
addAccess('doc:([a-z]+):([a-z]+)',	'doc_add_access');
//	Права доступа к файлам документов
addAccess('file:.+/doc/(\d+|new\d+)/(File|Gallery|Image|Title).*',	'doc_file_access');

addSnippet('map', 		'{{doc:map}}');
addSnippet('title', 	'{{page:title}}');

$docTypes = array();
$docTypes['page']		= 'Раздел:разделов';
$docTypes['article']	= 'Статью:статей';
$docTypes['comment']	= 'Комментарий:комментариев';
doc_config($docTypes, $docTypes, $docTypes);

$docSort	= array();
$docSort['default']	= '`sort`, `datePublish` DESC';
$docSort['name']	= '`title` ASC';
$docSort['-name']	= '`title` DESC';
$docSort['date']	= '`datePublish` ASC';
$docSort['-date']	= '`datePublish` DESC';
$docSort['sort']	= '`sort` ASC';
$docSort['-sort']	= '`sort` DESC';
$docSort['price']	= '`price` ASC';
$docSort['-price']	= '`price` DESC';
setCacheValue('docSort', $docSort);

$docPages	= array();
$docPages['25']		= 25;
$docPages['50']		= 50;
$docPages['100']	= 100;
$docPages['все']		= 10000;
setCacheValue('docPages', $docPages);

addEvent('file.upload',	'doc_file_update');
addEvent('file.delete',	'doc_file_update');

addEvent('config.end',	'doc_config');
function module_doc_config($val, $data)
{
	$documents_tbl = array();
	$documents_tbl['doc_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$documents_tbl['user_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Default'=>'0', 'Extra'=>'');
	$documents_tbl['doc_type']= array('Type'=>'enum(\'page\',\'article\',\'catalog\',\'product\',\'comment\')', 'Null'=>'NO', 'Key'=>'', 'Default'=>'page', 'Extra'=>'');
	$documents_tbl['title']= array('Type'=>'text', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['searchTitle']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['cache']= array('Type'=>'longtext', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['document']= array('Type'=>'longtext', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['searchDocument']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['fields']= array('Type'=>'longtext', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['fieldsThis']= array('Type'=>'longtext', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['datePublish']= array('Type'=>'datetime', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['template']= array('Type'=>'varchar(255)', 'Null'=>'YES', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['lastUpdate']= array('Type'=>'datetime', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0000-00-00 00:00:00', 'Extra'=>'');
//	$documents_tbl['deleted']= array('Type'=>'tinyint(8) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'0', 'Extra'=>'');
	$documents_tbl['visible']= array('Type'=>'tinyint(8) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'1', 'Extra'=>'');
	$documents_tbl['sort']= array('Type'=>'int(10) unsigned', 'Null'=>'YES', 'Key'=>'', 'Default'=>'9999', 'Extra'=>'');
	$fields	= dbAlterTable('documents_tbl', $documents_tbl);

	if ($fields['deleted']){
		dbDeleteField('documents_tbl', 'deleted');
	}
	if ($fields['originalDocument']){
		$db		= new dbRow('documents_tbl');
		$table	= $db->table();
		$db->exec("UPDATE $table SET `document` = `originalDocument`");
		dbDeleteField('documents_tbl', 'originalDocument');
	}
}

function doc_config($db, &$val, &$data)
{
	$docTypes = getCacheValue('docTypes');
	if (!is_array($docTypes)) $docTypes = array();
	foreach($data as $name => &$val){
		$type = $template = '';
		list($type, $template) = explode(':', $name);
		$docTypes["$type:$template"]	= $val;
	}
	setCacheValue('docTypes', $docTypes);
}

addEvent('page.compile',	'doc_page_compile');
function module_doc_page_compile($val, &$thisPage)
{
	//	{beginCompile:compileName}  {endCompile:compileName}
	$thisPage	= preg_replace('#{beginCompile:([^}]+)}#',	'<? if (beginCompile(\$data, "\\1")){ ?>', $thisPage);
	$thisPage	= preg_replace('#{endCompile:([^}]+)}#', 	'<? endCompile(\$data); } ?>',	$thisPage);
	$thisPage	= str_replace('{endCompile}', 				'<? endCompile($data); } ?>', 	$thisPage);
	$thisPage	= str_replace('{document}',					'<? document($data) ?>',		$thisPage);
	
	$thisPage	= preg_replace('#{beginCache:(\$[\d\w_]+):([^}]+)}#',	'<? if(beginCompile(\\1, "\\2")){ ?>', $thisPage);
	$thisPage	= preg_replace('#{endCache:(\$[\d\w_]+)}#', 			'<? endCompile(\\1); } ?>', $thisPage);
}
?>