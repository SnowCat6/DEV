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
//	echo "$baseType:$newType", ' ';
	switch("$baseType:$newType"){
		case 'page:':
		case 'page:article':
		case 'article:';
		break;
		default: return false;
	}
	return hasAccessRole('admin,developer,writer');
}

?>