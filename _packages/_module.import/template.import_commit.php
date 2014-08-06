<? function import_commit(&$val)
{
	$import	= new importBulk();
	$db		= $import->db();
	$ddb	= module('doc');
	
	$bIgnore= getValue('ignoreValue')?1:0;
	
	$i		= getValue('import');
	if (is_array($i)){
		if (getValue('importDoDelete')){
			$db->delete($i);
		}else
		if (getValue('importDoIgnore')){
			$db->setValue($i, 'ignore', $bIgnore);
		}
	}
	if (getValue('importDoSynch')){
		importDoSynch($db);
	}
?>
{{script:importCommit}}
{{ajax:template=ajaxResult}}
{{script:ajaxForm}}
{{script:ajaxLink}}
<form action="{{url:#}}" method="post" class="ajaxForm ajaxReload">
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="table">
  <tr>
    <th colspan="2">Выполнить со всеми</th>
    <th></th>
    <th colspan="2" nowrap="nowrap">Только с отмеченными</th>
    </tr>
  <tr>
    <td><input type="submit" name="importDoSynch" value="Обработать"></td>
    <td><input type="submit" name="importDoImport" value="Импортировать на сайт"></td>
    <td width="100%"></td>
    <td nowrap="nowrap"><input type="checkbox" name="ignoreValue" value="1" {checked:$bIgnore} /> <input type="submit" name="importDoIgnore" value="Игнорировать"></td>
    <td><input type="submit" name="importDoDelete" value="Удалить"></td>
  </tr>
</table>

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table importCommit">
<tr>
    <th colspan="2"><label><input type="checkbox" class="importSelectAll" /> Тип / отметка</label></th>
    <th>Наименование</th>
</tr>
<?
$key	= $db->key;
$db->order	= "date ASC, $key ASC";
$db->open();
while($data = $db->next()){
	$id	= $db->id();
	if ($data['doc_id']){
		$url		= getURL($ddb->url($data['doc_id']));
		$document	= "<a href=\"$url\" id=\"ajax\">update</a>";
	}else{
		$document	= '<span class="new">new</span>';
	}
	$ignore	= $data['ignore']?'<div>ignore</div>':'';
	$rel	= json_encode($data['fields']);
?>
<tr class="import_{$data[doc_type]}">
    <td>
    <label>
    	<input type="checkbox" name="import[{$id}]" value="{$id}" />
        {$data[doc_type]}
	</label>
    </td>
    <td>
    {!$document}
    {!$ignore}
    </td>
    <td class="name">
    	{$data[name]}
        <div rel="{$rel}"></div>
    </td>
  </tr>
<? } ?>
</table>
</form>
<? } ?>


<? function style_importCommit($val){ ?>
<style>
.importCommit th{
	white-space:nowrap;
}
.importCommit td{
	vertical-align:top;
}
.importCommit .checkCommit{
	padding:0;
}
.importCommit .new{
	color:red;
}
.importCommit .name{
	cursor:pointer;
}
.importCommit .name div div{
	padding:2px 0 2px 50px;
}
.importCommit .import_catalog{
	background:#eee;
}
.ui-tabs-panel .importCommit .import_catalog{
	background:#333;
}
</style>
<? } ?>



<? function script_importCommit($val){ ?>
<script>
$(function(){
	$(".importSelectAll").click(function(){
		doChangeCheckValue = true;
		var bCheck = $(this).prop('checked')?true:false;
		$(".importCommit td input").prop("checked", bCheck);
		doChangeCheckValue = false;
	});
	$(".importCommit .name").click(function()
	{
		var ctx = $(this).find("div");
		if (ctx.html()){
			ctx.html("");
			return;
		}
		var data = $.parseJSON(ctx.attr("rel"));
		var html = '';
		for (v in data){
			html += "<div>" + v + ": " + data[v] + "</div>";
		}
		ctx.html(html);
	});
});
</script>
<? } ?>

<? function importDoSynch(&$db)
{
	$docs	= array();
	$ddb	= module('doc:find', array('type'=>'catalog,product'));
	while($data = $ddb->next())
	{
		$fields	= $data['fields'];
		$any	= $fields['any'];
		$import	= $any['import'];
		if (!$import) $import = array();
		$article= $import[':importArticle'];
		if (!$article) continue;
		
		$docs[$data['doc_type']][$article]	= $ddb->id();
	}
	
	$table	= $db->table();
	$db->exec("UPDATE $table SET `doc_id`=0");
	$db->open();
	while($data = $db->next())
	{
		$fields	= $data['fields'];
		$article= $fields['article'];
		$docID	= $docs[$data['doc_type']][$article];

		if (!$docID) continue;
		if ($docID == $data['doc_id']) continue;
		
		$db->setValue($db->id(), 'doc_id', $docID);
	}
}?>