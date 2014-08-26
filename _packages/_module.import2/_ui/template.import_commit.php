<?
function import_commit(&$val)
{
	$import	= new importBulk();
	$db		= $import->db();
	$ddb	= module('doc');
	
	if ($val == 'get'){
		$data = $db->openID(getValue('id'));
		if ($data) importCommitRowData($ddb, $data);
		return setTemplate('');
	}
	if ($val == 'set'){
		$data = $db->openID(getValue('id'));
		if ($data){
			$d	= getValue('importData');
			dataMerge($d, $data);
			unset($d[$db->key]);
			$db->setValues($db->id(), $d);
		}
		return setTemplate('');
	}
	
	$bDelete	= getValue('deleteValue')?1:0;
	$bIgnore	= getValue('ignoreValue')?1:0;
	$i			= getValue('import');
	if (is_array($i)){
		if (getValue('importDoDelete')){
			$db->delete($i);
		}else
		if (getValue('importDoIgnore')){
			$db->setValue($i, 'ignore', $bIgnore);
		}
		if (getValue('importDoMarkDelete')){
			$db->setValue($i, 'delete', $bDelete);
		}
	}
	if (getValue('importDoSynch')){
		importDoSynch();
	}
?>
{{script:importCommit}}
{{ajax:template=ajaxResult}}
{{script:ajaxForm}}
{{script:ajaxLink}}
{{script:preview}}
<form action="{{url:#}}" method="post">
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="table">
  <tr>
    <th nowrap="nowrap">Выполнить со всеми</th>
    <th nowrap="nowrap"></th>
    <th colspan="3" nowrap="nowrap">Только с отмеченными</th>
    </tr>
  <tr>
    <td><input type="submit" name="importDoSynch" value="Обработать данные" class="button"></td>
    <td width="100%"></td>
    <td nowrap="nowrap">
        <input type="checkbox" name="deleteValue" {checked:$bDelete} />
        <input type="submit"  class="button" name="importDoMarkDelete" value="Пометить для удаления" />
    </td>
    <td nowrap="nowrap">
        <input type="checkbox" name="ignoreValue" {checked:$bIgnore} />
        <input type="submit"  class="button" name="importDoIgnore" value="Игнорировать">
    </td>
    <td><input type="submit" name="importDoDelete" value="Удалить" class="button"></td>
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
		$msg		= $data['updated']?'OK':'update';
		$url		= getURL($ddb->url($data['doc_id']));
		$document	= "<a href=\"$url\" id=\"ajax\" class=\"preview\">$msg</a>";
	}else{
		$document	= '<span class="new">new</span>';
	}
	$ignore	= $data['ignore']?'<div>ignore</div>':'';
	$delete	= $data['delete']?'<div>delete</div>':'';
	$rel	= json_encode($data['fields']);
?>
<tr class="import_{$data[doc_type]}">
    <td>
        <input type="checkbox" name="import[{$id}]" id="ch{$id}" value="{$id}" />
        <label for="ch{$id}">
            {$data[doc_type]}
        </label>
    </td>
    <td>
    {!$document}
    {!$ignore}
    {!$delete}
    </td>
    <td class="name">{$data[name]}</td>
</tr>
<tr class="importData" rel="{$id}"><td colspan="3"></td></tr>
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
.importCommit .importData{
	display:none;
}
.importCommit table{
	border-spacing:0;
}
.importCommit table td{
	padding:0;
}
.importCommit .importInfo{
	padding-left:10px;
}
.importCommit table *{
	padding:0;
	border:none;
}
.importCommit .importRowInfo div{
	position:relative;
}
.importCommit .importRowInfo .input{
	margin:1px 0;
	padding:0px 2px;
	position:absolute;
	left:0; top:0;
	min-width: 100%;
	z-index:9999;
}
.ui-tabs-panel .importCommit .importRowInfo{
	background:#222;
	padding:5px 10px;
	border-top:solid 1px #888;
	border-bottom:solid 1px #888;
}
.ui-tabs-panel .importCommit .import_catalog{
	background:#333;
}
.importCommit input:checked + label{
	background:#F00;
}
</style>
<? } ?>



<? function script_importCommit($val){ ?>
<script>
var bImportMouseDown = false;
var bImportFirstSelector = false;
$(function()
{
	$(".importSelectAll").click(function(){
		doChangeCheckValue = true;
		var bCheck = $(this).prop('checked')?true:false;
		$(".importCommit td input").prop("checked", bCheck);
		doChangeCheckValue = false;
	});
	
	$(".importCommit tr").mousedown(function(){
		bImportMouseDown = true;
		bImportFirstSelector = $(this).find("td input").prop('checked')?true:false;
	}).mouseup(function(){
		bImportMouseDown = false;
	}).mouseenter(function(){
		if (bImportMouseDown == false) return;
		$(this).find("td input").prop('checked', !bImportFirstSelector);
	}).mouseleave(function(){
		if (bImportMouseDown == false) return;
		$(this).find("td input").prop('checked', !bImportFirstSelector);
	});

	$(".importCommit .name").click(function()
	{
		var ctx = $(this).parent().next("tr");
		ctx.toggleClass("importData");
		
		var id = ctx.attr("rel");
		ctx = ctx.find("td");
		if (ctx.html()) return false;
		ctx.html('---- loading ----');
		
		ctx.load("{{getURL:import_commit_get=ajax}}" + "&id=" + id, function(data)
		{
			$(this)
				.addClass("importRowInfo")
				.html(data)
				.find("td[rel*=importData]").each(function()
				{
					var thisCell = $(this);
					$(this).parent("tr").find("td").click(function()
					{
						if (thisCell.find("input").length == 0)
						{
							var html = '<input type="text" size="50" class="input" name="' + thisCell.attr("rel") + '" value="' + thisCell.text() +'" />';
							html = "<div>" + thisCell.html() + html + "</div>";
							thisCell.html(html);
						}
						thisCell.find("input").show().focus()
							.blur(function()
							{
								$(this).hide();
								thisCell.find("strong").text($(this).val());
								$.ajax("{{url:import_commit_set=ajax}}&id=" + id + "&" + $(this).serialize());
							});
					});
			});
			
			$(document).trigger("jqReady");
		});
	});
});
</script>
<? } ?>

<? function importCommitRowData(&$db, &$data)
{
	$url	= $data['parent_doc_id']?getURL($db->url($data['parent_doc_id'])):'';
?>
<table><tr>
	<td>
    
<table>
<tr>
    <td>Документ:</td>
    <td><strong>
<? if ($data['doc_id']){ ?>
    {$data[doc_id]}
<? }else{ ?>
	-
<? } ?>
    </strong></td>
</tr>
<tr>
    <td>Родитель:</td>
    <td><strong>
<? if ($url){ ?>
    <a href="{!$url}" id="ajax" class="preview">{$data[parent_doc_id]}</a>
<? }else{ ?>
	-
<? } ?>
    </strong></td>
</tr>
<tr>
    <td>Артикул:</td>
    <td rel="importData[article]"><strong>{$data[article]}</strong></td>
</tr>
<tr>
    <td>Цена:</td>
    <td rel="importData[fields][price]"><strong>{$data[fields][price]}</strong></td>
</tr>
<tr>
    <td>Ед. изм:</td>
    <td><strong>{$data[fields][ed]}</strong></td>
</tr>
<tr>
  <td>Доставка:</td>
  <td><strong>{$data[fields][delivery]}</strong></td>
</tr>
</table>

    </td>
	<td class="importInfo">
<? foreach($data['fields'] as $name=>$val){
	if (is_array($val)) continue;
?><div>{$name}: <b>{$val}</b></div>
<? } ?>
    </td>
<? foreach($data['fields'] as $name=>$a){
	if (!is_array($a)) continue;
?>
    <td class="importInfo">
<b>{$name}</b>
<? foreach($a as $name=>$val){ ?>
<div>{$name}: <b>{$val}</b></div>
<? } ?>
    </td>
<? } ?>
</tr>
</table>
<? } ?>

<? function importDoSynch()
{
	$import	= new importBulk();
	$db		= $import->db();
	$docs	= array();
	$ddb	= module('doc:find', array('type'=>'catalog,product'));
	while($data = $ddb->next())
	{
		if ($data['doc_type'] == 'catalog'){
			$docs[$data['doc_type']][$data['title']]	= $ddb->id();
		}
		
		$fields	= $data['fields'];
		$any	= $fields['any'];
		$im		= $any['import'];
		if (!$import) $import = array();

		//	Получить артикул товара
		$article= $im[':importArticle'];
		if (!$article) continue;
		
		//	Запомнить артикул
		$article	= explode(', ', $article);
		foreach($article as $v){
			$v = trim($v);
			if ($v) $docs[$data['doc_type']][":$v"]	= $ddb->id();
		}
	}
	
	//	Родители
	$parents= array();
	
	$table	= $db->table();
	$db->exec("UPDATE $table SET `doc_id`=0, SET `parent_doc_id`=0");

	$catalogs	= array();
	$db->open("`doc_type`='catalog'");
	while($data = $db->next()){
		$catalogs[":$data[article]"]	= $db->id();
	}

	$db->open();
	while($data = $db->next())
	{
		$fields	= $data['fields'];
		$article= $data['article'];
		//	Найти по артикулу код товара
		$article= explode(', ', $article);
		foreach($article as $v){
			$v		= trim($v);
			$docID	= $docs[$data['doc_type']][":$v"];
			if ($docID) break;

			if ($data['doc_type'] == 'catalog'){
				$docID	= $docs[$data['doc_type']][$v];
				if ($docID) break;
			}
		}

		$d	= array();
		//	Если элемент с артикулом есть, присвоить
		if ($docID != $data['doc_id']) $d['doc_id']	= $docID;
		
		$parent		= $fields['parent'];
		$parentID	= importDoSynchCatalog($import, $docs, $catalogs, $parent, $parent, "");
		if ($parent && $parentID != $data['parent_doc_id']) $d['parent_doc_id']	= $parentID;
/*		
		$parent		= $fields['parent2'];
		$parentID	= importDoSynchCatalog($import, $docs, $catalogs, $parent, $parent, $fields['parent']);
		if ($parent && $parentID != $data['parent_doc_id']) $d['parent_doc_id']	= $parentID;
		
		$parent		= $fields['parent3'];
		$parentID	= importDoSynchCatalog($import, $docs, $catalogs, $parent, $parent, $fields['parent2']);
		if ($parent && $parentID != $data['parent_doc_id']) $d['parent_doc_id']	= $parentID;
*/		
		if ($d)	$db->setValues($db->id(), $d);
	}
}
function importDoSynchCatalog(&$import, &$docs, &$catalogs, $name, $article, $parent)
{
	$article	= importArticle($article);
	$parent		= importArticle($parent);
	if (!$name || !$article) return;
	
	$docID	= $docs['catalog'][":$article"];
	if (!$docID) $docID	= $docs['catalog'][$article];
/*	
	$synch		= NULL;
	$f			= array();
	$f['parent']= $parent;
	$iid	= $import->addItem($synch, 'catalog', $article,  $name, $f);
	if ($iid){
		$catalogs[":$article"]	= $iid;
		$import->db()->setValue($iid, 'doc_id', $docID);
	}
*/	
	return $docID;
}
?>
