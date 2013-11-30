<? function doc_tools($db, $val, &$data){
	if (!access('write', 'doc:')) return;
?>
<style>
.adminTools .adminDocTools td{
	padding:0;
}
.adminDocTools a{
	margin-bottom:10px;
	margin-right:20px;
}
</style>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="adminDocTools">
  <tr>
    <td width="50%" valign="top">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?
$types = getCacheValue('docTypes');
foreach($types as $docType => $names){
	list($type, $template) = explode(':', $docType);
	if ($template) continue;
	if (!access('add', "doc:$type")) continue;
	$name = docType($type, 1);
?>
  <tr>
    <td nowrap="nowrap"><a href="<?= getURL("page_all_$type")?>" id="ajax">Список {$name}</a></td>
    <td><a href="<?= getURL('page_add', "type=$type")?>" id="ajax_edit">новый</a></td>
  </tr>
<? } ?>
</table>
    </td>
    <td width="50%" valign="top">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
 <?
foreach($types as $docType => $name){
	list($type, $template) = explode(':', $docType);
	if (!$template) continue;
	if (!access('add', "doc:$type")) continue;
	$name = docTypeEx($type, $template, 0);
?>
  <tr>
    <td nowrap="nowrap"><a href="<?= getURL("page_all_$type", "template=$template")?>" id="ajax">{$name}</a></td>
    <td><a href="<?= getURL('page_add', "type=$type&template=$template")?>" id="ajax_edit">новый</a></td>
  </tr>
<? } ?>
</table>
    </td>
  </tr>
</table>
<p><a href="{{url:page_all}}" id="ajax">Список разделов и каталогов</a></p>
<? } ?>