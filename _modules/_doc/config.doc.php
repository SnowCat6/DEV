<?
/*
Конфигурационный файл для модуля документов
Файл запускается один раз и не используется при основной работе сайта
Тут собраны все статичные настройки и методы обработки необходимые на стадии конфигурирования
*/

////////////////////
//	Ссылки на сайте
////////////////////

//	Стандартные ссылки страниц
addUrl('page(\d+)', 				'doc:page_url');
//	Редактирование страниц
addUrl('page_edit_(\d+)', 			'doc:edit');
//	Редактирование отдельных частей документа
addUrl('page_edit_(\d+)_(.+)','doc:editable:edit');
//	Редактирование страниц, добавление
addUrl('page_add_(\d+)', 	'doc:add');
addUrl('page_add', 			'doc:add');
//	Менеджер документов
addUrl('page_all_([a-z]+)',	'doc:all');
addUrl('page_all',			'doc:all');
//	Каталог документов
addUrl('page_map',			'doc:map');
//	Страницы поииска
addUrl('search',				'doc:searchPage');
addUrl('search_([a-z]+)',		'doc:searchPage');
addUrl('search_([a-z]+)_(\w+)',	'doc:searchPage');

////////////////////
//	События
////////////////////

//	Компиляция документов из динамического вида в статический для хранения
addEvent('document.compile','doc_compile');
//	addEvent('site.renderEnd',	'doc:cacheFlush');
//	Сбросить кеш в базу данных
addEvent('site.exit',		'doc:cacheFlush');
//	Инстументы для административной панели
addEvent('admin.tools.add',	'doc:tools');

addEvent('file.upload',	'doc_file_update');
addEvent('file.delete',	'doc_file_update');

//	Сохранение настроек
addEvent('storage.get',	'doc:storage:get');
addEvent('storage.set',	'doc:storage:set');

addEvent('cache.set',	'doc:cache:set');
addEvent('cache.get',	'doc:cache:get');
addEvent('cache.clear',	'doc:cache:clear');

////////////////////
//	Права доступа к документам
////////////////////

addAccess('doc:(\d*)',				'doc_access');
addAccess('doc:(\d+):([a-z]+)',		'doc_access');
addAccess('doc:([a-z]+)',			'doc_add_access');
addAccess('doc:([a-z]+):([a-z]+)',	'doc_add_access');
//	Права доступа к файлам документов
addAccess('file:.+/doc/(\d+|new\d+)/(File|Gallery|Image|Title).*',	'doc_file_access');

////////////////////
//	Сниппеты
////////////////////

addSnippet('map', 		'{{doc:map}}');
addSnippet('title', 	'{{page:title}}');

////////////////////
//	Типы документов
////////////////////

$docTypes 				= array();
$docTypes['page']		= 'Раздел:Разделы';
$docTypes['article']	= 'Статью:Статьи';
$docTypes['comment']	= 'Комментарий:Комментарии';
doc_config('', '', $docTypes);

////////////////////
//	Возможные сортировки
////////////////////

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

////////////////////
//	Количества документов на страницу
////////////////////

$docPages	= array();
$docPages['25']		= 25;
$docPages['50']		= 50;
$docPages['100']	= 100;
$docPages['все']		= 10000;
setCacheValue('docPages', $docPages);

////////////////////
//	Дополнительные настройки на этапе подготовки к запуску
////////////////////

addEvent('config.end',	'doc_config');
function module_doc_config($val, $data)
{
	//	Основная таблица документов
	$documents_tbl = array();
	$documents_tbl['doc_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$documents_tbl['user_id']= array('Type'=>'int(10) unsigned', 'Null'=>'NO', 'Default'=>'0', 'Extra'=>'');
	$documents_tbl['doc_type']= array('Type'=>'enum(\'page\',\'article\',\'catalog\',\'product\',\'comment\')', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'page', 'Extra'=>'');
	$documents_tbl['title']= array('Type'=>'text', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['searchTitle']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['cache']= array('Type'=>'array', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['document']= array('Type'=>'longtext', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['searchDocument']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['fields']= array('Type'=>'array', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['datePublish']= array('Type'=>'datetime', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['template']= array('Type'=>'varchar(255)', 'Null'=>'YES', 'Key'=>'MUL', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['lastUpdate']= array('Type'=>'datetime', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$documents_tbl['visible']= array('Type'=>'tinyint(8) unsigned', 'Null'=>'NO', 'Key'=>'MUL', 'Default'=>'1', 'Extra'=>'');
	$documents_tbl['sort']= array('Type'=>'int(10) unsigned', 'Null'=>'YES', 'Key'=>'', 'Default'=>'0', 'Extra'=>'');
	$fields	= dbAlterTable('documents_tbl', $documents_tbl);
}

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

	$rules		= getIniValue(':docRules') or array();
	foreach($rules as $rule => $val)
	{
		$docType = $docTemplate = '';
		list($docType, $docTemplate) = explode(':', $rule);
		$docTypes["$docType:$docTemplate"]	= $val;
	}

	setCacheValue('docTypes', $docTypes);
}

////////////////////
//	Обработка документов
////////////////////

//	Статичная компиляция исполняемого кода на этапе конфигурирования сайта
addEvent('page.compile',	'doc_page_compile');
function module_doc_page_compile($val, &$ev)
{
	$thisPage	= &$ev['content'];
	//	{beginCompile:compileName} 
	//	{endCompile}
	$thisPage	= preg_replace('#{beginCompile:([^}]+)}#',	'<? if (beginCompile(\$data, "\\1")){ ?>', $thisPage);
	$thisPage	= preg_replace('#{endCompile([^}]+)}#', 	'<? endCompile(\$data); } ?>',	$thisPage);
	$thisPage	= str_replace('{endCompile}', 				'<? endCompile($data); } ?>', 	$thisPage);
	//	{document}
	$thisPage	= str_replace('{document}',	'<? document($data) ?>',		$thisPage);
	$thisPage	= preg_replace_callback('#{document:(\$[\w\d_]+)([^}]*)}#',	'fnDocumentCache',	$thisPage);
	$thisPage	= preg_replace('#{document\|([^}]*)}#',	'<? document($data, "\\1") ?>',	$thisPage);

	//	{beginCache:$data:cacheName}
	$thisPage	= preg_replace('#{beginCache:(\$[\d\w_]+):([^}]+)}#',	'<? if(beginCompile(\\1, "\\2")){ ?>', $thisPage);
	//	{endCache:$documentData}
	$thisPage	= preg_replace('#{endCache:(\$[\d\w_]+)}#', 			'<? endCompile(\\1); } ?>', $thisPage);
}
function fnDocumentCache(&$ctx)
{
	$varName	= $ctx[1].$ctx[2];
	$varName	= preg_replace('#\[([^]]+)\]#',  '[\'\\1\']', $varName);
	return "<? if (beginCompile($ctx[1], '$ctx[2]')){ echo $varName; endCompile(); }; ?>";
}
?>