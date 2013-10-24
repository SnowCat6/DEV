<?
function doc_all(&$db, $val, &$data)
{
	@$type	= $data[1];
	module('script:ajaxLink');
	module('script:ajaxForm');
	module('script:jq_ui');
	
	$documentDelete = getValue('documentDelete');
	if (is_array($documentDelete)){
		foreach($documentDelete as $iid){
			module("doc:update:$iid:delete");
		}
	}

	if (testValue('doSorting'))
		$db->sortByKey('sort', getValue('documentOrder'), getValue('page')*15);

	$db2	= module('doc');
	$sql	= array();
	
	$search	= getValue('search');
	if (!is_array($search)) $search = array();
	$search['type'] = $type?$type:'page,catalog';
	if ($template = getValue('template')) $search['template'] = $template;
	
	doc_sql($sql, $search);
	
	if (getValue('documentDeleteAll') == 'yes'){
		$db->open($sql);
		while($db->next()){
			$id = $db->id();
			m("doc:update:$id:delete");
		}
		m('page:display:!message', '');
	}
	
	$db->order = '`sort`';
	$db->open($sql);

	$rows	= $db->rows();
	if ($rows == 0){
		module('message:error', 'Нет документов');
		module('display:message');
	}
	$urlType= $type?"_$type":'';
	$page	= getValue('page');
	$docType= docTypeEx($type, $template, 1);
?><? $module_data = array(); $module_data[] = "Список $docType"; moduleEx("page:title", $module_data); ?>
<form action="<? $module_data = array(); $module_data["template"] = "$template"; moduleEx("getURL:page_all$urlType", $module_data); ?>" method="post" class="form ajaxForm ajaxReload">
<input type="hidden" name="page" value="<? if(isset($page)) echo htmlspecialchars($page) ?>" />
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><input type="submit" class="button" value="Сохранить" /></td>
    <td width="100%">Все выделенные документы будут удалены</td>
    <td align="right" nowrap="nowrap">Удалить все видимые документы </td>
    <td><input name="documentDeleteAll" type="checkbox" value="yes" /></td>
  </tr>
</table>
<?
$p = dbSeek($db, 15, array('search' => $search));
echo $p;
?>
<table class="table all" cellpadding="0" cellspacing="0" width="100%">
<tr class="search">
    <td colspan="3">Поиск</td>
    <td><input type="text" name="search[title]" value="<? if(isset($search["title"])) echo htmlspecialchars($search["title"]) ?>" class="input w100" /></td>
</tr>
<tbody id="sortable">
<?	
	while($data = $db->next()){
		$id		= $db->id();
		$url	= getURL($db->url());
		$drag	= docDraggableID($id, $data);
?>
<tr>
  <td><div  class="ui-icon ui-icon-arrowthick-2-n-s"></div></td>
    <td>
<input type="hidden" name="documentOrder[]" value= "<? if(isset($id)) echo htmlspecialchars($id) ?>" />
<input type="checkbox" name="documentDelete[]" value="<? if(isset($id)) echo htmlspecialchars($id) ?>" />
    </td>
    <td><a href="<? module("getURL:page_edit_$id"); ?>" id="ajax_edit"><b><? if(isset($id)) echo htmlspecialchars($id) ?></b></a></td>
    <td width="100%">
    <a href="<? if(isset($url)) echo $url ?>" id="ajax"<? if(isset($drag)) echo $drag ?>><? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?></a>
    <div><small><?
$split	= '';
$parents = getPageParents($id);
foreach($parents as $iid){
	$d		= $db2->openID($iid);
	$url	= $db2->url($iid);
	$drag	= docDraggableID($iid, $d);
?><? if(isset($split)) echo $split ?><a href="<? module("getURL:$url"); ?>" id="ajax"<? if(isset($drag)) echo htmlspecialchars($drag) ?>><? if(isset($d["title"])) echo htmlspecialchars($d["title"]) ?></a>
<? $split = ' &gt; '; } ?></small></div>
    </td>
</tr>
<?	} ?>
</tbody>
</table>
<? if(isset($p)) echo $p ?>
</form>
<script language="javascript" type="text/javascript">
$(function(){
	$( "#sortable" ).sortable({
		axis: 'y',
		update: function(e, ui){
			var form = $(this).parents("form");
			if (form.find("input[name=doSorting]").length) return;
			$('<input name="doSorting" type="hidden" />').appendTo(form);
		}
	}).disableSelection();
});
</script>
<? } ?>