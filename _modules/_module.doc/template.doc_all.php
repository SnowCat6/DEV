<?
function doc_all(&$db, $val, &$data){
	@$type	= $data[1];

	$sql	= array();
	$search	= array('type'=>$type);
	doc_sql($sql, $search);
	$db->open($sql);
	
	if ($db->rows() == 0) return module('message:error', 'Нет документов');
	while($data = $db->next()){
		$id		= $db->id();
		$url	= getURL($db->url());
?>
<div><a href="{!$url}">{$data[doc_type]} <b>{$id}</b></a> {$data[title]}</div>
<?
	}
}
?>