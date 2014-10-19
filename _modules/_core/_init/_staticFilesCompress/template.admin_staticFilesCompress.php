<? function admin_staticFilesCompress(&$val, &$menu)
{
	$gini	= getGlobalCacheValue('ini');
?>
<div>
<label>
	<input type="hidden" name="globalSettings[:][staticCompress]" value="no" />
	<input type="checkbox" name="globalSettings[:][staticCompress]" value="yes" {checked:$gini[:][staticCompress]=='yes'}  />
    Статическое сжатие файлов
</label>
</div>
<? } ?>