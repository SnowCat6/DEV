<?
function doc_edit(&$db, $val, $data)
{
	$id		= (int)$data[1];
	$data	= $db->openID($id);
	if (!$data) return;
	
	$bAjax = testValue('ajax');
	$doc	= getValue('doc');
	if (is_array($doc))
	{
		dataMerge($doc, $data);
		module('prepare:2local', &$doc);
		module('admin:tabUpdate:doc_property', &$doc);
		$iid = module("doc:update:$id:edit", &$doc);
		//	document added
		if ($iid){
			if ($bAjax){
				echo 'Документ записан';
				die;
			}
			redirect(getURL($db->url($iid)));
		}
	}
	
	$folder = $db->folder();
	module('prepare:2public', &$data);
	module("editor:$folder");
	$class	= $bAjax?' class="admin ajaxForm"':'class="admin"';
?>
<form action="<?= getURL("page_edit_$id", $bAjax?'ajax':'')?>" method="post"{!$class}>
<? module('admin:tab:doc_property', &$data)?>
</form>
<? } ?>