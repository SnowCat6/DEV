<? function admin_expires(&$val, &$menu)
{
	$gini	= getGlobalCacheValue('ini');
?>
<div>
<label>
	<input type="hidden" name="globalSettings[:][useExpires]" value="no" />
	<input type="checkbox" name="globalSettings[:][useExpires]" value="yes" {checked:$gini[:][useExpires]=='yes'}  />
   Использовать время кеширования контента
</label>
</div>
<? } ?>