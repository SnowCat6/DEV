<?
function import_synch(&$val)
{
	$import	= new importBulk();
	$db		= $import->db();
	$ddb	= module('doc');
	
	$ini	= getCacheValue('ini');
	
	$import	= getValue('importSynch');
	if (is_array($import)){
		$ini[':import']	= $import;
		setIniValues($ini);
	}
	
	$import	= $ini[':import'];
	if (getValue('doImportSynch')){
		doImportSynch($db, $ddb, $import);
	}
	
	$updates= array();
	$table	= $db->table();
	$db->exec("SELECT count(*) AS cnt, `doc_type`, `doc_id` = 0 AS isAdd FROM $table WHERE `ignore`=0 AND `updated`=0 GROUP BY `doc_type`, `isAdd`");
	while($data =  $db->next()){
		$updates[$data['doc_type']][$data['isAdd']]	= $data['cnt'];
	}
?>
{{ajax:template=ajaxResult}}
<form action="{{url:#}}" method="post" class="ajaxForm ajaxReload">
<table border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td>Новых каталогов</td>
    <td align="right"><?= (int)$updates['catalog'][1]?></td>
    <td>
        <label>
            <input type="hidden" name="importSynch[noAddCatalog]" value="">
            <input type="checkbox" name="importSynch[noAddCatalog]" {checked:$import[noAddCatalog]}> не добавлять
        </label>
    </td>
    </tr>
  <tr>
    <td>Обновленных каталогов</td>
    <td align="right"><?= (int)$updates['catalog'][0]?></td>
    <td><label>
        <input type="hidden" name="importSynch[noUpdateCatalog]" value="">
        <input type="checkbox" name="importSynch[noUpdateCatalog]" {checked:$import[noUpdateCatalog]}> не обновлять
    </label>
    </td>
    </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td>Новых товаров</td>
    <td align="right"><?= (int)$updates['product'][1]?></td>
    <td><label>
        <input type="hidden" name="importSynch[noAddProduct]" value="">
        <input type="checkbox" name="importSynch[noAddProduct]"  {checked:$import[noAddProduct]}> не добавлять </label>
      </td>
    </tr>
  <tr>
    <td>Обновленных товаров</td>
    <td align="right"><?= (int)$updates['product'][0]?></td>
    <td><label>
        <input type="hidden" name="importSynch[noUpdateProduct]" value="">
        <input type="checkbox" name="importSynch[noUpdateProduct]" {checked:$import[noUpdateProduct]}>
         не  обновлять
    </label>
    </td>
    </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    </tr>
</table>
<p>
<input type="submit" value="Обновить сайт" name="doImportSynch" class="button" />
</p>
</form>
<? } ?>

<? function doImportSynch(&$db, &$ddb, $import)
{
	$sql	= array();
	$sql[]	= '`ignore`=0 AND `updated`=0';
	

	if ($import['noAddCatalog'])	$sql[]	= "(`doc_id`<>0 OR `doc_type`<>'catalog')";
	if ($import['noUpdateCatalog'])	$sql[]	= "(`doc_id`=0 OR `doc_type`<>'catalog')";

	if ($import['noAddProduct'])	$sql[]	= "(`doc_id`<>0 OR `doc_type`<>'product')";
	if ($import['noUpdateProduct'])	$sql[]	= "(`doc_id`=0 OR `doc_type`<>'product')";

	$db->open($sql);
	while($data = $db->next())
	{
		$d			= array();
		$fields		= $data['fields'];
		
		$d['title']	= $data['name'];
		$d['price']	= parseInt($fields['price']);
		$d[':property']	= $fields[':property'];
		dataMerge($d, $d[':data']);
		if ($data['doc_id'])
		{
			if (moduleEx("doc:update:$data[doc_id]:edit", $d))
			{
				$id	= $db->id();
				$db->setValue($id, 'updated', 1);
			}
		}else{
			$d['fields']['any']['import'][':importArticle']	= $data['article'];
			if ($iid = moduleEx("doc:update:$data[parent_doc_id]:add:$data[doc_type]", $d))
			{
				$id	= $db->id();
				$db->setValues($id, array('updated' => 1, 'doc_id'=>$iid));
			}
		}
	}
}?>
