<? function mail_templates($db, $val, $data){ ?>
<link rel="stylesheet" type="text/css" href="../../_module.admin/admin.css">
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css">
{{page:title=Почтовые шаблоны}}
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
    <th width="100%">Шаблон</th>
  </tr>
  <?
$files		= array();
$adminFiles	= getFiles(localCacheFolder."/siteFiles/mailTemplates");
$userFiles	= getFiles(images."/mailTemplates");

foreach($adminFiles as $name => $path){
	$name = preg_replace('#\..*#', '', $name);
	$files[$name] = $path;
}
foreach($userFiles as $name => $path){
	$name = preg_replace('#\..*#', '', $name);
	$files[$name] = $path;
}

module('script:ajaxLink');
foreach($files as $name => $path){
	$url = getURL("admin_mailTemplates_$name");
?>
  <tr>
    <td><a href="{!$url}" id="ajax">{$name}</a></td>
  </tr>
  <? } ?>
</table>

<? } ?>