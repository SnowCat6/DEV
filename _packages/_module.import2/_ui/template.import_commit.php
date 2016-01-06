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

	if (getValue('importDoSClear'))
	{
		$table	= $db->table();
		$db->exec("DELETE FROM $table");
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
		import_commitSynch($val);
	}
?>
{{script:importCommit}}
{{ajax:template=ajaxResult}}
{{script:ajaxForm}}
{{script:ajaxLink}}
{{script:preview}}
<form action="{{url:#}}" method="post" class="commitForm">
<table width="100%" border="0" cellspacing="0" cellpadding="2" class="table">
  <tr>
    <th nowrap="nowrap">Выполнить со всеми</th>
    <th nowrap="nowrap">&nbsp;</th>
    <th nowrap="nowrap"></th>
    <th colspan="3" nowrap="nowrap">Только с отмеченными</th>
    </tr>
  <tr>
    <td><input type="submit" name="importDoSynch" value="Обработать данные" class="button"></td>
    <td><input type="submit" name="importDoSClear" value="Очистить все" class="button"></td>
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

<?
	$key	= $db->key;
	$db->order	= "date ASC, $key ASC";
	$db->open();
	
	$p	= dbSeek($db, 100);
?>

{!$p}

<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table importCommit">
<tr>
    <th colspan="2"><label><input type="checkbox" class="importSelectAll" /> Тип / отметка</label></th>
    <th>Наименование</th>
</tr>
<?
while($data = $db->next())
{
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

{!$p}

</form>
<? } ?>


<? function script_importCommit($val){ ?>
{{script:jq}}
<script src="script/jqImportCommit.js"></script>
<link rel="stylesheet" type="text/css" href="css/jqImportCommit.css">
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
<table cellpadding="0" cellspacing="0">
<?
$f = $data['fields'] or array();
ksort($f);
foreach($f as $name=>$val){
	if (is_array($val)) continue;
?>
<tr>
    <td>{$name}: </td>
    <td rel="importData[fields][{$name}]">
    	<strong>{$val}</strong>
    </td>
</tr>
<? } ?>
</table>
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

<?
//	+function import_commitSynch
function import_commitSynch(&$val)
{
	set_time_limit(5*60);


	$import	= new importBulk();
	$db		= $import->db();
	$docs	= array();
	
	$db->open('`pass`=0');
	if ($db->rows() == 0) return;
	
	$ddb	= module('doc:find', array('type'=>'catalog,page,product'));
	while($data = $ddb->next())
	{
		$type	= $data['doc_type'];
		switch($data['doc_type'])
		{
			case 'catalog':
			case 'page':
				$type	= 'catalog';
//				$docs[$type][$data['title']]	= $ddb->id();
				
				$path	= getPageParents($ddb->id());
				$article= array();
				foreach($path as $iid)
				{
					$d	= module("doc:data:$iid");
					$article[]	= $d['title'];
				}
				$article[]	= $data['title'];
				$article	= implode('/', $article);
				$article	= importArticle($article);
				$docs[$type][$article]	= $ddb->id();
		}
		
		$fields	= $data['fields'];
		$any	= $fields['any'];
		$im		= $any['import'];
		if (!$import) $import = array();

		//	Получить артикул товара
		$article= $im[':importArticle'];
		if (!$article) continue;
		
		//	Запомнить артикул
		$article	= explode(',', $article);
		foreach($article as $v){
			$v = trim($v);
			if ($v) $docs[$type][":$v"]	= $ddb->id();
		}
	}
	
	//	Родители
	$parents= array();
	
	$table	= $db->table();
	$db->exec("UPDATE $table SET `doc_id`=0, `parent_doc_id`=0 WHERE `pass`=0");

	$catalogs	= array();
	$db->open("`doc_type` IN ('catalog')");
	while($data = $db->next()){
		$catalogs[":$data[article]"]	= $db->id();
	}

	$db->open('`pass`=0');
	while($data = $db->next())
	{
		$fields	= $data['fields'];
		$article= $data['article'];
		//	Найти по артикулу код товара
		$article= explode(', ', $article);
		foreach($article as $v)
		{
			$v		= trim($v);
			$docID	= $docs[$data['doc_type']][":$v"];
			if ($docID) break;

			switch($data['doc_type'])
			{
				case 'catalog':
				case 'page':
					$docID	= $docs['catalog'][$v];
			}
			if ($docID) break;
		}

		$d	= array();
		$d['pass']	= 1;
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
