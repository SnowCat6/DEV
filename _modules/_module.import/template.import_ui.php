<?
//	Пользовательский интерфейс импорта
function import_ui($val, &$data)
{
	//	Если запущенно как AJAX то вернем просто сводку импорта
	if (testValue('ajax')){
		setTemplate('');
		module('import:doImport');
		return importUI($val, $data);
	}
	//	Иначе выведем весь код
	m('page:title', 'Импорт данных');
	m('script:jq');

	//	Получим переменные с указанием действий
	$files = getValue('import');
	if (is_array($files)){
		//	Отмененныйе импорты
		$doCancel = @array_keys(@$files['cancel']);
		module('import:doImport:delete', $doCancel);
		//	Запустить импорты
		$doImport = @array_keys(@$files['import']);
		module('import:doImport:create', $doImport);
	}
	//	Вывести данные
?>
<div id="importProcess"><? importUI($val, $data) ?></div>
<script>
//	Счетчик секунд до обновления
var lastImportUpdate = 0;
$(function(){
	updateImportData();
});
//	Загрузить через AJAX обновленные данные
function updateImportData()
{
	if (lastImportUpdate++ >= 5){
		$("#reloadImportButton").val("Обновляется");
		$("#importProcess").load("import.htm?ajax", function(){
			lastImportUpdate = 0;
			updateImportData();
		});
	}else{
		$("#reloadImportButton").val("Обновить (" + (6 - lastImportUpdate) + ")");
		setTimeout(updateImportData, 1000);
	}
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
<? foreach(getFiles(importFolder, 'xml$') as $file => $path){
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
    <td align="right" valign="top" nowrap="nowrap">
<? switch($process['status']){ ?>
<? case 'working': $hasWorking = true; ?>
<input type="submit" name="import[import][{$file}]" class="button" value="Продолжить" />
<input type="submit" name="import[cancel][{$file}]" class="button" value="X" />
<? break ?>
<? case 'complete': ?>
<input type="submit" name="import[import][{$file}]" class="button" value="Завершено, повторить" />
<? break ?>
<? default: ?>
<input type="submit" name="import[import][{$file}]" class="button" value="Импортировать" />
<? } ?>
    </td>
</tr>
<? } ?>
<tr>
    <td colspan="3" align="right" valign="top"><input type="submit" id="reloadImportButton" class="button" value="Обновить" /></td>
</tr>
</table>
</form>
<script>
var hasWorking = <?= $hasWorking?'true':'false'?>;
</script>
<? return $hasWorking; } ?>

