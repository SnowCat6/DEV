<?
function import_synch(&$val)
{
	$import	= new importBulk();
	$db		= $import->db();
	$ddb	= module('doc');
	
	$ini	= getCacheValue('ini');
	$import	= $ini[':import'];
	
	$updates= array();
	$table	= $db->table();
	$db->exec("SELECT count(*) AS cnt, `doc_type`, `doc_id` = 0 AS isAdd FROM $table WHERE `ignore`=0 GROUP BY `doc_type`, `isAdd`");
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
            <input type="checkbox" name="importSynch[noAddCatalog]" {checked:$import[noAddCatalog]}> не добавлять
        </label>
    </td>
    </tr>
  <tr>
    <td>Обновленных каталогов</td>
    <td align="right"><?= (int)$updates['catalog'][0]?></td>
    <td><label>
      <input type="checkbox" name="importSynch[noUpdateCatalog]" {checked:$import[noUpdateCatalog]}>
      не обновлять
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
      <input type="checkbox" name="importSynch[noAddProduct]"  {checked:$import[noAddProduct]}>
      не добавлять </label>
      </td>
    </tr>
  <tr>
    <td>Обновленных товаров</td>
    <td align="right"><?= (int)$updates['product'][0]?></td>
    <td><label>
      <input type="checkbox" name="importSynch[noUpdateProduct]" {checked:$import[noUpdateProduct]}>
      не не обновлять
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
<input type="submit" value="Обновить сайт" class="button" />
</p>
</form>
<? } ?>
