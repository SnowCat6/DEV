<?
function doc_all(&$db, $val, &$data){
	@$type	= $data[1];
	module('script:ajaxLink');
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
<div><a href="{{getURL:page_edit_$id}}" id="ajax_edit">{$data[doc_type]}<b>{$id}</b></a> - <a href="{!$url}">{$data[title]}</a></div>
<?
	}
}
?>