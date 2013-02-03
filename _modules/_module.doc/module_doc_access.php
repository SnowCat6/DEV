<?
function module_doc_access($mode, $data)
{
	@$id	= (int)$data[1];
	switch($mode){
		case 'read': 
			return true;
		case 'write':
			return hasAccessRole('admin,developer,writer,manager');
		case 'delete':
			return hasAccessRole('admin,developer,writer');
	}
}

function module_doc_add_access($mode, $data)
{
	@$baseType	= $data[1];
	@$newType	= $data[2];

	switch("$baseType:$newType")
	{
		case 'page:':
		case 'page:page':
		case 'page:catalog':
		case 'page:article':
		
		case 'article:';
		case 'article:comment':
		
		case 'catalog:';
		case 'catalog:catalog';
		case 'catalog:product';
		
		case 'product:';
		case 'product:comment';
		break;
		default: return false;
	}
	return hasAccessRole('admin,developer,writer');
}

?>