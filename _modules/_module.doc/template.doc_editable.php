<?
function doc_editable($db, $id, &$name)
{
	if ($id == 'edit') return  doc_editableEdit($db, $name);

	$name	= implode('', $name);
	$data	= $db->openID($id);

	$menu	= array();
	if (access('write', "doc:$id")){
		$menu['Изменить#ajax']	= getURL("page_edit_$id"."_$name");
	}
	
	beginAdmin();
	if (beginCompile($data, "editable_$name"))
	{
		$doc	= $data['fields'];
		$doc	= $doc['any'];
		echo $doc["editable_$name"];
		endCompile($data);
	}
	endAdmin($menu);
}?>
<? function doc_editableEdit($db, &$data)
{
	$id		= $data[1];
	$name	= $data[2];
	if (!access('write', "doc:$id")) return;
	
	$doc	= getValue('doc');
	if (is_array($doc))
	{
		mEx('prepare:2local', $doc);
		$d		= array();
		$d['fields']['any']["editable_$name"]	= $doc["editable_$name"];
		$iid	= moduleEx("doc:update:$id:edit", $d);
		if ($iid) redirect(getURL($db->url($id)));
	}
	
	$data	= $db->openID($id);
	$folder	= $db->folder();
	$url	= "page_edit_$id"."_$name";
	
	mEx('prepare:2public', $data);
	module("editor:$folder");
?>
<form method="post" action="{{url:$url}}" class="admin ajaxForm ajaxReload">
{{display:message}}
<div><textarea name="doc[editable_{$name}]" cols="" rows="35" class="input w100 editor">{$data[fields][any][editable_$name]}</textarea></div>
</form>
<? } ?>

