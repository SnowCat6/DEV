<? function import_commit(&$val){
?>
{{ajax:template=ajaxResult}}
Синхронизация товаров с баздой данных
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table">
<tr>
  <th>Цена</th>
  <th>Артикул</th>
  <th>Наименование</th>
  <th>Документ</th>
  <th>Тип</th>
  </tr>
<?
$import	= new importBulk();
$db		= $import->db();
$db->open();
while($data = $db->next()){
?>
<tr>
  <td>&nbsp;</td>
	<td>{$data[article]}</td>
	<td>{$data[name]}</td>
	<td>&nbsp;</td>
	<td>{$data[doc_type]}</td>
  </tr>
<? } ?>
</table>
<? } ?>
