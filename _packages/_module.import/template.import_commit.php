<? function import_commit(&$val){
?>
{{ajax:template=ajaxResult}}
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="table">
<tr>
  <th>Тип</th>
  <th>Наименование</th>
  <th>Дата</th>
  </tr>
<?
$import	= new importBulk();
$db		= $import->db();
$db->open();
while($data = $db->next()){
?>
<tr>
    <td>{$data[doc_type]}</td>
    <td title="{$data[article]}">{$data[name]}</td>
    <td nowrap="nowrap"><?= date('d.m.Y H:i', $data['date'])?></td>
</tr>
<? } ?>
</table>
<? } ?>
