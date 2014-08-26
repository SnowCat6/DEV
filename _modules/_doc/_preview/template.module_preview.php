<?
function module_preview(&$fn, &$data){
	list($fn, $val) = explode(':', $fn, 2);
	$fn	= getFn("preview_$fn");
	if ($fn) return $fn($val, $data);
}
function preview_page(&$val, &$data)
{
	$id	= alias2doc("/$data[1].htm");
	if (!$id) retrun;
	
	$db		= module("doc");
	$data	= $db->openID($id);
	if (!$data) return;
	
	setTemplate('');
	$fn	= getFn("doc_preview_$data[doc_type]");
	if ($fn) return $fn($db);
?>
<div class="previewImage">{{doc:titleImage:$id:size=400x300}}</div>
<h2 class="previewTitle">{$data[title]}</h2>
<div class="previewProperty">{{prop:read:plain=id:$id}}</div>
<? } ?>