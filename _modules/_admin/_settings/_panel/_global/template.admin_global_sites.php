<? function admin_global_sites_update(&$gini)
{
	$gini[':globalSiteRedirect'] = array();

	$redirect	= explode("\r\n", getValue('globalSiteRedirect'));
	foreach($redirect as $row)
	{
		$row	= explode('=', $row);
		@$host	= trim($row[0]);
		@$path	= trim($row[1]);
		if (!$host || !$path) continue;
		$gini[':globalSiteRedirect'][$host] = $path;
	}
}?>

<? function admin_global_sites(&$gini)
{
	$redirect		= '';
	@$stieRedirect	= $gini[':globalSiteRedirect'];
	if (!is_array($stieRedirect)) $stieRedirect = array();
	foreach($stieRedirect as $host => $path){
		$redirect .= "$host=$path\r\n";
	}
?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td valign="top">
<div>Адреса и хосты: вы сейчас на <b><?= htmlspecialchars($_SERVER['HTTP_HOST'])?></b>, правило обработки<strong> HOST_NAME=локальное имя сайта</strong>. <br />
Если<strong>локальное имя сайта</strong> начинается с <strong>http://</strong>, то выполнится редирект по указанному адресу. <br />
К примеру: .<strong>*={{urlEx:#}}</strong></div>
<textarea name="globalSiteRedirect" cols="" class="input w100" rows="15">{$redirect}</textarea>
    </td>
    <td valign="top" style="padding-left:20px"><?
$files	= getDirs(sitesBase);
foreach($files as $name){
	$name	= basename($name);
?>
<div>{$name}</div>
<? } ?>
	</td>
  </tr>
</table>

<? return '20-Сайты и редиректы'; } ?>