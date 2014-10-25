<? function admin_global_htaccess_update(&$gini)
{
	if (!access('write', 'admin:global')) return;

	$htaccess	= getValue('globalSettingsHtaccess');
	if ($htaccess && testValue('htaccessOverride')){
		file_put_contents_safe('.htaccess', $htaccess);
	}
}?>

<? function admin_global_htaccess(&$gini)
{
?>

<div align="right"><label><input type="checkbox" name="htaccessOverride" value="yes" />Перезаписать .htaccess</label></div>
<div><textarea name="globalSettingsHtaccess" rows="15" readonly class="input w100" id="globalSettingsHtaccess"><?= htmlspecialchars(file_get_contents('.htaccess'))?></textarea></div>

<script language="javascript" type="text/javascript">
$(function(){
	$("input[name=htaccessOverride]").change(function(){
		$("#globalSettingsHtaccess").prop("readonly", $(this).attr("checked")?false:true);
	});
});
</script>

<? return '30-.htaccess'; } ?>