<?
function doc_add(&$db, $val, $data)
{
	@$id	= (int)$data[1];
	
	$type	= getValue('type');
	$doc	= getValue('doc');
	if (is_array($doc) && $type)
	{
		module('prepare:2local', &$doc);
		module('admin:tabUpdate:doc_property', &$doc);
		$iid = module("doc:update:$id:add:$type", $doc);
		//	document added
		if ($iid) redirect(getURL($db->url($iid)));
	}else{
		$doc = array();
	}
	
	$data	= $doc;
	$folder	= $db->folder();
	module('prepare:2public', &$data);
	module("editor:$folder");
	$class	= testValue('ajax')?' class="admin ajaxForm"':' class="admin"';
?>
<form action="<?= getURL("page_add_$id", "type=$type")?>" method="post"{!$class}>
<? module('admin:tab:doc_property', &$data)?>
</form>
<? } ?>