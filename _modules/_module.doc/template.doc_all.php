<?
function doc_all(&$db, $val, &$data){
	@$type	= $data[1];
?>
<h1>Список документов</h1>
<?
	$sql	= array();
	$search	= array('type'=>$type);
	doc_sql($sql, $search);
	$db->open($sql);
	
	if ($db->rows() == 0){
		module('message:error', 'Нет документов');
		module('display:message');
	}
	
	while($data = $db->next()){
		$id		= $db->id();
		$url	= getURL($db->url());
?>
<div><a href="{!$url}">{$data[doc_type]} <b>{$id}</b></a> {$data[title]}</div>
<?
	}
}
?>