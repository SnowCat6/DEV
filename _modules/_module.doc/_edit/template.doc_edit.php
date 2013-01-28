<?
function doc_edit(&$db, $val, $data)
{
	$id		= (int)$data[1];
	$data	= $db->openID($id);
	if (!$data) return;
	
	$doc	= getValue('doc');
	if (is_array($doc))
	{
		dataMerge($doc, $data);
		module('prepare:2local', &$doc);
		module('admin:tabUpdate:doc_property', &$doc);
		$iid = module("doc:update:$id:edit", &$doc);
		//	document added
		if ($iid){
//			module("links:add:page$id", "/linkedURL.htm");
			redirect(getURL($db->url($iid)));
		}
	}
	
	$folder = $db->folder();
	module('prepare:2public', &$data);
	module("editor:$folder");
	$class	= testValue('ajax')?' class="admin ajaxForm"':'class="admin"';
?>
<form action="<?= getURL("page_edit_$id")?>" method="post"{!$class}>
<? module('admin:tab:doc_property', &$data)?>
</form>
<? } ?>