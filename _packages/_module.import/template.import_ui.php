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
	m('script:ajaxLink');

	@$file = $_FILES['importFile'];
	if (is_array($file)){
		$dst = importFolder."/$file[name]";
		module('import:doImport:delete', array($dst));
		copy($file['tmp_name'], $dst);
		module('import:doImport:create', array($dst));
	}
	//	Получим переменные с указанием действий
	$files = getValue('import');
	if (is_array($files)){
		//	Отмененныйе импорты
		$doDelete = @array_keys(@$files['delete']);
		module('import:doImport:delete', $doDelete);
		//	Отмененныйе импорты
		$doCancel = @array_keys(@$files['cancel']);
		module('import:doImport:cancel', $doCancel);
		//	Запустить импорты
		$doImport = @array_keys(@$files['import']);
		module('import:doImport:create', $doImport);
		
		if (isset($files['continue']))
			module('import:doImport');
	}
	//	Вывести данные
?>
<form action="{{getURL:import}}"  method="post" enctype="multipart/form-data">
  <table width="100%" border="0" cellspacing="0" cellpadding="2">
    <tr>
      <td width="100%"><input type="file" name="importFile" class="fileupload w100" /></td>
      <td><input type="submit"class="button" value="Импортировать" /></td>
    </tr>
  </table>
</form>
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
		$("#importProcess").load("import.htm?ajax&"+Math.random(), function(){
			$(document).trigger("jqReady");
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
  <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table import">
<tr>
    <th>&nbsp;</th>
    <th nowrap>Файл</th>
    <th width="100%" nowrap>Процесс</th>
    <th>&nbsp;</th>
</tr>
<? foreach(getFiles(importFolder, '(xml|txt)$') as $file => $path){
	$process	= getImportProcess($path);
	
	$processDate= $process['endTime'];
	if ($processDate){
		$workTime	= round($processDate - $process['startTime']);
		$processDate= date('<b>d.m.Y</b> H:i:s', $processDate);
	}else{
		$processDate= '-';
		$workTime	= '';
	}
	
?>
<tr>
  <td valign="top">
  <input type="submit" name="import[delete][{$file}]" class="button" value="X" />
  </td>
    <td valign="top" nowrap="nowrap">
<div title="{$path}">{$file}</div>
<div><?= date('<b>d.m.Y</b> H:i:s', filemtime($path))?></div>
    </td>
    <td valign="top" nowrap="nowrap">
  <div><b>{$process[percent]}%</b> <?= round($process['offset']/1024, 2)?> кб. / <?= round($process['size']/1024, 2)?> кб.</div>
  <div><a href="{{getURL:import_log=file:$file}}" id="ajax">{!$processDate} <b>{$workTime} сек.</b></a></div>
    </td>
    <td align="right" valign="top" nowrap="nowrap">
<? switch($process['status']){ ?>
<? case 'working': $hasWorking = true; ?>
<input type="submit" name="import[continue][{$file}]" class="button" value="Продолжить" />
<input type="submit" name="import[cancel][{$file}]" class="button" value="X" />
<? break ?>
<? case 'complete': ?>
<input type="submit" name="import[import][{$file}]" class="button" value="Повторить" />
<? break ?>
<? default: ?>
<input type="submit" name="import[import][{$file}]" class="button" value="Импортировать" />
<? } ?>
    </td>
</tr>
<? } ?>
<tr class="noBorder">
    <td colspan="4" align="right" valign="top"><input type="submit" id="reloadImportButton" class="button" value="Обновить" /></td>
</tr>
</table>
</form>
<script>
var hasWorking = <?= $hasWorking?'true':'false'?>;
</script>
<? return $hasWorking; } ?>

