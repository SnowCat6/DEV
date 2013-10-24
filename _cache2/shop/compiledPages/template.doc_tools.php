<? function doc_tools($db, $val, &$data){
	if (!access('write', 'doc:')) return;
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="50%" valign="top">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?
$types = getCacheValue('docTypes');
foreach($types as $docType => $names){
	if (!access('add', "doc:$docType")) continue;
	$name = docType($docType, 1);
?>
  <tr>
    <td nowrap="nowrap"><a href="<?= getURL("page_all_$docType")?>" id="ajax">Список <? if(isset($name)) echo htmlspecialchars($name) ?></a></td>
    <td><a href="<?= getURL('page_add', "type=$docType")?>" id="ajax_edit">новый</a></td>
  </tr>
<? } ?>
</table>
    </td>
    <td width="50%" valign="top">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
 <?
$types = getCacheValue('docTemplates');
foreach($types as $docType => $name){
	list($docType, $template) = explode(':', $docType);
	if (!access('add', "doc:$docType")) continue;
?>
  <tr>
    <td nowrap="nowrap"><a href="<?= getURL("page_all_$docType", "template=$template")?>" id="ajax"><? if(isset($name)) echo htmlspecialchars($name) ?></a></td>
    <td><a href="<?= getURL('page_add', "type=$docType&template=$template")?>" id="ajax_edit">новый</a></td>
  </tr>
<? } ?>
</table>
    </td>
  </tr>
</table>
<p><a href="<? module("url:page_all"); ?>" id="ajax">Список разделов и каталогов</a></p>
<? } ?>