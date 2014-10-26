<? function admin_global_sites_update(&$gini)
{
	$gini[':globalSiteRedirect'] = array();
	$rules	= getValue('globalSiteRules');
	if (!is_array($rules)) return;
	
	foreach($rules as $host => $rule)
	{
		$rule	= trim($rule);
		$host	= trim($host);
		if (!$rule) continue;
		$gini[':globalSiteRedirect'][$rule] = $host;
	}

}?>

<? function admin_global_sites(&$gini)
{
	$siteRules	= $gini[':globalSiteRedirect'];
	$siteRules	= array_flip($siteRules);
	
	$files		= getDirs(sitesBase);
	foreach($files as $name=>$path){
		if (isset($siteRules[$name])) continue;
		$siteRules[$name] = '';
	}
?>
{{script:jq_ui}}
<script>
$(function(){
	$(".globalSiteRules tbody.sortRules").sortable({
		axis: "y"
	});
	$(".copy2rule").click(function(){
		var v = $(this).parent().parent().find("td");
		var val = $(v.get(1)).text();
		$(v.get(3)).find("input").val(val);
		return false;
	});
});
</script>
<style>
.copy2rule{
	text-decoration:none;
}
</style>
<link rel="stylesheet" type="text/css" href="../../../../../_templates/baseStyle.css">
<div style="max-height:600px; overflow:auto">

<div>Адреса и хосты: вы сейчас на <b>{$_SERVER[HTTP_HOST]}</b>, правило обработки<strong> HTTP_HOST=локальное имя сайта</strong>. <br />
Если<strong>локальное имя сайта</strong> начинается с <strong>http://</strong>, то выполнится редирект по указанному адресу. <br />
К примеру: .<strong>*={$_SERVER[HTTP_HOST]}</strong></div>
<p>При отсутсвии правил, сайты распознаются по названию папки с сайтом.</p>

<table width="95%" border="0" cellspacing="0" cellpadding="0" class="table globalSiteRules">
<tr>
  <th nowrap="nowrap">&nbsp;</th>
  <th nowrap="nowrap">Путь к сайту</th>
  <th>&nbsp;</th>
  <th>Регулярное выражение для идентификации сайта HTTP_HOST (сейчас {$_SERVER[HTTP_HOST]})</th>
</tr>
<tbody class="sortRules">
<? foreach($siteRules as $name => $rule){
?>
<tr>
  <td nowrap="nowrap"><span class="ui-icon ui-icon-arrowthick-2-n-s"></span></td>
    <td nowrap="nowrap">{$name}</td>
    <td nowrap="nowrap"><a href="#" class="copy2rule">copy =&gt;</a></td>
    <td width="100%"><input type="text" class="input w100" name="globalSiteRules[{$name}]" value="{$rule}" /></td>
</tr>
<? } ?>
</tbody>
</table>
</div>

<? return '20-Сайты и редиректы'; } ?>