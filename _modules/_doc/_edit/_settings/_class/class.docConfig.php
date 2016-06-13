<?
class docConfig
{
	//	Get all pages templates by type or template
	static function getTemplates($filter = '')
	{
		$docTypesCache	= getCache('docTypesCache', 'ini');
		if (!is_array($docTypesCache))
		{
			$docTypesCache	= array();

			//	Кастомизированные стили документов
			//	Custom user rules
			$docTypesUser	= getStorage(':docRules', 'ini');
			if (!$docTypesUser)
			{
				$docTypesUser	= getIniValue(':docRules') or array();
				foreach($docTypesUser as $type => $data)
				{
					$type	= self::makeTypeName($type);

					$name1	= $name2 = $contentFn = $pageTemplate	= '';
					list($name1, $name2, $contentFn, $pageTemplate)	= explode(':', $data);
					$data	= array(
						'type'		=> 'user',
						'mode'		=> 'active',
						'NameOne'	=> $name1,
						'NameOther'	=> $name2,
						'contentFn'	=> $contentFn,
						'note'		=> "Тип документов $name1",
						'pageTemplate'	=> $pageTemplate
					);
					$docTypesUser[$type] 	= $data;
				}
				setStorage(':docRules', $docTypesUser, 'ini');
			}
			foreach($docTypesUser as $type => $data)
			{
				//	Combine with internal
				$data2	= $docTypesCache[$type];
				if ($data2) dataMerge($data, $data2);
				$docTypesCache[$type]	= $data;
			}
			setCache('docTypesCache', $docTypesCache, 'ini');
		}
		
		if (!$filter) return $docTypesCache;

		$result	= array();
		foreach($docTypesCache as $type => $data)
		{
			if (!preg_match("#^$filter#", $type)) continue;
			$result[$type] 	= $data;
		}
		return $result;
	}
	//
	static function deleteTemplate($type)
	{
		$type	= self::makeTypeName($type);

		$undo	= self::getTemplate($type);
		if (!$undo) return;
		
		undo::add("Удаление шаблона документа $type", "docConfig:$type",
			array('action' => "doc:docConfigUndo:$type", 'data' => $undo)
		);
		
		$docTypesUser			= getStorage(':docRules', 'ini');
		$docTypesUser[$type] 	= NULL; unset($docTypesUser[$type]);
		setStorage(':docRules', $docTypesUser, 'ini');
		setCache('docTypesCache', NULL, 'ini');
	}
	//	Store user template
	static function setTemplate($type, $data)
	{
		$type	= self::makeTypeName($type);
		
		$undo	= self::getTemplate($type);
		if ($undo)
		{
			undo::add("Шаблон документа $type изменен", "docConfig:$type",
				array('action' => "doc:docConfigUndo:$type", 'data' => $undo)
			);
		}else{
			undo::add("Шаблон документа $type добавлен", "docConfig:$type",
				array('action' => "doc:docConfigUndo:$type", 'data' => $undo)
			);
		}

		if (!$data['NameOne'])		$data['NameOne']	= $type;
		if (!$data['NameOther'])	$data['NameOther']	= $data['NameOne'];
		
		$docTypesUser			= getStorage(':docRules', 'ini');
		$docTypesUser[$type] 	= $data;
		setStorage(':docRules', $docTypesUser, 'ini');
		setCache('docTypesCache', NULL, 'ini');
	}
	//	Get user template
	static function getTemplate($type)
	{
		$type	= self::makeTypeName($type);
		$result	= self::getTemplates();
		return $result[$type];
	}
	static function getTypes()
	{
		$types	= self::getTemplates();
		$result	= array();		
		foreach($types as $type => $data){
			list($type) 	= explode(':', $type);;
			$result[$type] 	= $type;
		};
		
		return $result;
	}
	static function getContentFns()
	{
		$fnTemplates	= module("findTemplates:^(doc_page_.*)");
		return array_keys($fnTemplates);
	}
	static function getPageTemplates()
	{
		$namesPage	= array();
		$pages		= getCacheValue('pages') or array();
		foreach($pages as $name => $val){
			if (!preg_match('#^page\.(.*)#', $name, $v)) continue;
			$namesPage[$v[1]]	= $v[1];
		}
		return $namesPage;
	}
	static function makeTypeName($type)
	{
		$type	= trim($type, ':');
		list($typeName, $typeTemplate)	= explode(':', $type, 2);
		if (!$typeName) return;
		$type	= "$typeName:$typeTemplate";
		return $type;
	}
};
?>
