<? function import_log($val, &$data)
{
	$file		= getValue('file');
	$process	= getImportProcess($file);
	if (!$process) return;
	m('page:title', "Лог: $file");
?>
<pre><div>
<?= implode('</div><div>', $process['log'])?>
</div></pre>
<? } ?>