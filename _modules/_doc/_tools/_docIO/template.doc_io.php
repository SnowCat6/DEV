<?
//	+function doc_io_set
function doc_io_set($db, $val, $data)
{
	$key	= $data['key'];
	if (!preg_match('#^doc(\d+)\.(.*)#', $key, $val)) return;
	
	$id		= $val[1];
	$path	= $val[2];
	
	$d	= array();
	systemIO::set_data($d, $path, $data['value']);
	m("doc:update:$id:edit", $d);
}
//	+function doc_io_get
function doc_io_get($db, $val, $data)
{
	$key	= $data['key'];
	if (!preg_match('#^doc(\d+)\.(.*)#', $key, $val)) return;
	
	$id		= $val[1];
	$path	= $val[2];
	
	$d		= $db->openID($id);
	$data['value']	= systemIO::get_data($d, $path);
}
?>
