<? function doc_editable(&$db, &$val, &$data)
{
	if (!is_array($data)) $data = array();
	if ($val == 'edit') return  doc_editableEdit($db, $data);

	list($id, $name) = explode(':', $val, 2);
	if (!$name) return;
	
	$id		= alias2doc($id);
	$d		= $db->openID($id);
	if (!$d) return;
	$menu	= array();
	if (access('write', "doc:$id")){
		$menu['Изменить#ajax_edit']	= getURL("page_edit_$id"."_$name");
		$menu	= doc_menu_inlineEx($menu, $d, "fields.any.editable_$name");
	}

	beginAdmin($menu);
	if (beginCompile($d, "editable_$name"))
	{
		$ctx	= $d['fields'];
		$ctx	= $ctx['any'];
		$ctx	= $ctx["editable_$name"];
		if (!$ctx) $ctx	= $data['default'];
		
		$fn		= $data['fn'];
		if (function_exists($fn)) $fn($ctx);

		echo $ctx;

		endCompile();
	}
	endAdmin();
}

function doc_editableEdit($db, &$data)
{
	$id		= $data[1];
	$name	= $data[2];
	if (!$name) return;
	if (!access('write', "doc:$id")) return;
	
	$doc	= getValue('doc');
	if (is_array($doc))
	{
		mEx('prepare:2local', $doc);
		$d		= array();
		$d['fields']['any']["editable_$name"]	= $doc["editable_$name"];
		$iid	= moduleEx("doc:update:$id:edit", $d);
		if ($iid){
			m("doc:clear:$id");
			module("redirect", getURL($db->url($id)));
		}
	}
	
	$data	= $db->openID($id);
	$folder	= $db->folder();
	$url	= "page_edit_$id"."_$name";
	
	m('page:title', "Изменить $name");
	mEx('prepare:2public', $data);
	module("editor", $folder);
	
	$docName	= "doc[editable_$name]";
?>
<form method="post" action="{{url:$url}}" class="admin ajaxForm ajaxReload pageEdit">
{{display:message}}
<div class="adminEditTools">
    <table>
    <tr>
        <td>{{editor:images:document=$folder/Image;$folder/Gallery}}</td>
        <td>{{snippets:tools:$docName}}</td>
    </tr>
    </table>
</div>
<div><textarea name="{$docName}" {{editor:data:$folder}} cols="" rows="35" class="input w100 editor">{$data[fields][any][editable_$name]}</textarea></div>
</form>
<? } ?>