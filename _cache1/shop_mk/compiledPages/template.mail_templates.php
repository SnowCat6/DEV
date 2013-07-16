<? function mail_templates($db, $val, $data){
	if (!hasAccessRole('admin,developer,writer')) return;
?>
<? module("page:style", 'admin.css') ?>
<? module("page:style", 'baseStyle.css') ?>
<? $module_data = array(); $module_data[] = "Почтовые шаблоны"; moduleEx("page:title", $module_data); ?>
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
    <td><a href="/<? if(isset($url)) echo $url ?>" id="ajax"><? if(isset($name)) echo htmlspecialchars($name) ?></a></td>
  </tr>
  <? } ?>
</table>

<? } ?>