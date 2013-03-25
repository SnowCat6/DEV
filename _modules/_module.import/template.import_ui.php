<? function import_ui($val, &$data)
{
	if (testValue('ajax')){
		setTemplate('');
		module('import:doImport');
		return importUI($val, $data);
	}
	m('page:title', 'Импорт данных');
	m('script:jq');

	$files = getValue('import');
	if ($files) module('import:doImport:create', @array_keys(@$files['import']));
?>
<div id="importProcess"><? importUI($val, $data); ?></div>
<script>
$(function(){
	updateImportData();
});
function updateImportData(){
	if (!hasWorking) return;
	$("#importProcess").load("import.htm?ajax", function(){
		setTimeout(updateImportData, 5*1000);
	});
}
</script>
<? } ?>
<? function importUI($val, $data)
{
	$hasWorking = false;
?>
{{page:title=Импорт файлов}}
<form action="{{getURL:import}}" method="post">
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<tr>
    <th nowrap>Файл</th>
    <th nowrap>Процесс</th>
    <th nowrap>&nbsp;</th>
</tr>
<? foreach(getFiles(localHostPath.'/_exchange', 'xml$') as $file => $path){
	$process	= getImportProcess($path);
	
	$processDate= $process['processDate'];
	if ($processDate) $processDate = date('<b>d.m.Y</b> H:i:s');
	else $processDate = '-';
	
	$workTime = round(mktime() - $process['startTime']);
?>
<tr>
    <td valign="top">
<div title="{$path}">{$file}</div>
<div><?= date('<b>d.m.Y</b> H:i:s', filemtime($path))?></div>
    </td>
    <td valign="top" nowrap="nowrap">
  <div><b>{$process[percent]}%</b> <?= round($process['offset']/1024, 2)?> кб. / <?= round($process['size']/1024, 2)?> кб.</div>
  <div>{!$processDate} <b>{$workTime} сек.</b></div>
    </td>
    <td align="right" valign="top">
<? switch($process['status']){ ?>
<? case 'working': $hasWorking = true; ?>
<input type="submit" name="import[cancel][{$path}]" class="button" value="Отменить" />
<? break ?>
<? case 'complete': ?>
<input type="submit" name="import[import][{$path}]" class="button" value="Завершено, повторить" />
<? break ?>
<? default: ?>
<input type="submit" name="import[import][{$path}]" class="button" value="Импортировать" />
<? } ?>
    </td>
</tr>
<tr>
    <td colspan="3" valign="top">
    <div><?= implode('</div> <div>', $process['log'])?></div>
    </td>
</tr>
<? } ?>
</table>
</form>
<script>
var hasWorking = <?= $hasWorking?'true':'false'?>;
</script>
<? return $hasWorking; } ?>

