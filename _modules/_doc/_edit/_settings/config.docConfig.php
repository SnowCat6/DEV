<?
addEvent('admin.tools.settings','doc:toolsConfig');
addUrl('admin_docconfig',		'doc:docConfig');
addUrl('admin_doctype', 		'doc:docTypeEdit');
?>

<?
addEvent('config.end',	'doc_setConfig');
////////////////////
//	Типы документов
////////////////////
function module_doc_setConfig($val, $data)
{
	$docTypes 				= array();
	$docTypes['page']		= 'Раздел:Разделы';
	$docTypes['article']	= 'Статью:Статьи';
	m('doc:config:type', $docTypes);
}

function doc_config($db, $val, $docTypesInt)
{
	foreach($docTypesInt as $type => $data)
	{
		if (!$type) continue;
		if (docConfig::getTemplate($type)) continue;
		
		if (!is_array($data))
		{
			$name1 = $name2 = $contentFn = $pageTemplate	= '';
			list($name1, $name2, $contentFn, $pageTemplate)	= explode(':', $data);
			$data	= array(
				'type'		=> 'internal',
				'mode'		=> 'active',
				'NameOne'	=> $name1,
				'NameOther'	=> $name2,
				'contentFn'	=> $contentFn,
				'note'		=> "Тип документов $name1",
				'pageTemplate'	=> $pageTemplate
			);
		}
		docConfig::setTemplate($type, $data);
	}
}
?>
