<?
function module_doc_access($mode, &$data)
{
	$id	= (int)$data[1];
	switch($mode){
		case 'read': 
			return true;
		case 'add':
			return module_doc_add_access($mode, $data);
		case 'write':
			return hasAccessRole('admin,developer,writer,manager,SEO');
		case 'delete':
			if ($id){
				$db = module('doc');
				$d	= $db->openID($id);
				return $d['fields']['denyDelete'] != 1 && hasAccessRole('admin,developer,writer');
			}
			return hasAccessRole('admin,developer,writer');
	}
}

function module_doc_add_access($mode, &$data)
{
	if ($mode != 'add') return false;

	$baseType	= $data[1];
	if ((int)$baseType){
		$db = module('doc');
		$d	= $db->openID($baseType);
		$baseType = $d['doc_type'];
	}else
	if (!$baseType) $baseType = '';
	
	$newType	= $data[2];
	
	switch("$baseType:$newType")
	{
		case 'page:':
		case ':page':
//		case 'page:catalog':
		case 'catalog:page':
		case 'catalog:':
		case ':catalog':
			if ($d && $newType){
				$access	= $d['fields']['access'];
				return $access['page'] && hasAccessRole('admin,developer,writer');
			}
			return hasAccessRole('admin,developer,writer');
		case 'page:page':
		case 'page:article':
			if ($d){
				$access	= $d['fields']['access'];
				return $access[$newType] && hasAccessRole('admin,developer,writer');
			}
			return hasAccessRole('admin,developer,writer');

		case 'article:':
		case ':article':
		case 'product:':
		case ':product':
		case 'catalog:product':
		case 'catalog:catalog':
			return hasAccessRole('admin,developer,writer,manager');

		case 'page:comment':
		case 'article:comment':
			if ($d){
				$access	= $d['fields']['access'];
				return $access[$newType];
			}
			return false;
		case 'product:comment':
			return true;
	}
	return false;
}
function module_doc_file_access(&$mode, &$data)
{
	if (preg_match('#new(\d+)#', $data[1], $var)){
		if (userID() == $var[1]) return true;
	}

	$id	= (int)$data[1];
	return access($mode, "doc:$id");
}
?>