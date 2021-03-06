<? function import_txtSettings(&$val)
{
	$ini	= getCacheValue('ini');
	$prices	= getCacheValue(':price');

	$fields	= array();
	$fields['article']	= 'Артикул';
	$fields['name']		= 'Наименование';

	$fields['parent1']	= 'Базовый каталог';
	$fields['parent2']	= 'Каталог 2 уровня';
	$fields['parent3']	= 'Каталог 3 уровня';
	
	foreach($prices as $name => $field){
		$fields[$field[0]]	= 'Цена ' . $field[1];
	}
	$fields['ed']		= 'Ед. измерения';
	$fields['delivery']	= 'Условия доставки';
	
	////////////////////////////////////////
	$updateFields	= array();
	if (testValue('txtSettingsOther'))
	{
		$values	= getValue('txtSettingsOther');
		$values	= explode("\r\n", $values);
		foreach($values as $row)
		{
			list($name, $val)	= explode('=', $row);
			$name	= trim($name);
			$val	= trim($val);
			if ($name && $val){
				$updateFields[$name] = $val;
			}
		}
	}
	
	$values	= getValue('txtSettingsFields');
	if (is_array($values))
	{
		foreach($values as $name=>$val){
			if ($val) $updateFields[$name] = $val;
		}
	}

	if ($updateFields){
		$ini[':txtImportFields']	= $updateFields;
		setIniValues($ini);
	}
	///////////////////////////////////////
	$updateFields	= array();
	
	if ($val = getValue('txtEncode')){
		$updateFields['encode']	= $val;
	}
	if ($val = getValue('txtHasRoot')){
		$updateFields['txtHasRoot']	= $val;
	}
	
	if ($updateFields){
		$ini[':txtSettings']	= $updateFields;
		setIniValues($ini);
	}
	
	
	$encodes	= explode(',', 'windows-1251,utf-8');
	$encode		= $ini[':txtSettings']['encode'];
	if (!$encode) $encode = $encodes[0];
	
?>
{{page:title=Настройки импорта}}
{{ajax:template=ajax_edit}}
{{script:adminTabs}}
<form action="{{url:#}}" method="post" class="ajaxForm ajaxReload">

<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#txtImportMain">Основные</a></li>
    <li class="ui-corner-top"><a href="#txtImportOther">Дополнительные</a></li>
    <li class="ui-corner-top"><a href="#txtImportHelp">Формат данных</a></li>
    <li style="float:right"><input name="docSave" type="submit" value="Сохранить" class="ui-button ui-widget ui-state-default ui-corner-all" /></li>
</ul>

<div id="txtImportMain">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="50%" valign="top">
<table border="0" cellspacing="0" cellpadding="2" class="table" width="100%">
<tr>
  <th nowrap="nowrap">Данные товара</th>
  <th width="100%">Названия колонок через ";"</th>
</tr>
<? $txtFields	= $ini[':txtImportFields']; ?>
<? foreach($fields as $n=>$name){ ?>
<tr>
    <td nowrap="nowrap">{$name}</td>
    <td><input type="text" name="txtSettingsFields[{$n}]" value="{$txtFields[$n]}" class="input w100" /></td>
</tr>
<? } ?>
</table>
    </td>
    <td width="50%" valign="top"><table border="0" cellspacing="0" cellpadding="2" class="table" width="100%">
      <? $txtFields	= $ini[':txtImportFields']; ?>
      <? foreach($fields as $n=>$name){ ?>
      <? } ?>
      <tr>
        <th nowrap="nowrap">Свойство</th>
        <th width="100%">Значение</th>
      </tr>
      <tr>
        <td nowrap="nowrap">Кодировка</td>
        <td><select name="txtEncode" class="input w100">
          <? foreach($encodes as $name){ ?>
          <option value="{$name}"{selected:$encode==$name}>{$name}</option>
          <? } ?>
        </select></td>
      </tr>
      <tr>
        <td nowrap="nowrap">Имеется родительский каталог</td>
        <td>
        <input type="hidden" name="txtHasRoot" value="0" />
        <input type="checkbox" name="txtHasRoot" value="1" {checked:$ini[:txtSettings][txtHasRoot]} />
        </td>
      </tr>
    </table>
    </tr>
</table>
</div>

<div id="txtImportOther">
Дополнительная обработка колонок, формат: <strong>название поля</strong>=<strong>названия колонок через ";"</strong><br>
Если название поля разделено "." значения будут записываться в массив, к примеру: <strong>property.Тип</strong>=<strong>Вид отдыха;Длительность</strong>.
<div>
  <textarea class="input w100" name="txtSettingsOther" rows="15"><?
	$txtFields	= $ini[':txtImportFields'];
	$text		= '';
	foreach($txtFields as $name=>$val){
		$text .= "$name=$val\r\n";
	};
	echo $text;
	?></textarea>
</div>
</div>

<div id="txtImportHelp">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
    <th colspan="3">Формат данных для импорта <i>.txt .,csv</i> файлов</th>
    </tr>
  <tr>
    <td><b>Родительский каталог</b></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    </tr>
  <tr>
    <td><b>Название колнки</b></td>
    <td><b>Название колнки</b></td>
    <td><b>Название колнки</b></td>
    </tr>
  <tr>
    <td>Данные</td>
    <td>Данные</td>
    <td>Данные</td>
    </tr>
  <tr>
    <td><b>Родительский каталог</b></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>Данные</td>
    <td>Данные</td>
    <td>Данные</td>
  </tr>
  <tr>
    <td>Данные</td>
    <td>Данные</td>
    <td>Данные</td>
  </tr>
    </table>
    <p><em>В TXT файлах разделяются знаком табуляции в кодировке <strong>{$encode}</strong></em></p>
    <p><em>В CSV файлах разделяются знаком ; (точка с запятой) в кодировке <strong>{$encode}</strong>, значения экранируються знаком "\" (обратный слеш)</em></p>
    <p>Родительские каталоги так-же могут быть заданы в колонках указанных в настройках, они имеют больший приоритет перед указанными в строках.</p>
</div>

</div>

<?
	$sources	= array();
	moduleEx('import:txtSource', $sources);
	$name		= getValue('source');
	$synch		= $sources[$name];
	if ($synch){

	$source	= $synch->getValue('source');
?>
<div class="ajaxDocument">
<h2><a href="{$source}" target="_blank">{$source}</a></h2>
<style>
.txtRowRootCatalog{
	background:#F90;
	color:white;
}
.txtRowCatalog{
	background:green;
	color:white;
}
.txtRowFormat{
	background:yellow;
	color:red;
}
.txtRowProduct{
	color:blue;
}
.txtRowReset{
	background:red;
	color:white;
}
</style>
<input type="hidden" name="source" value="{$name}" />
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<?
	$rows	= array();
	$cols	= 0;
	$f		= fopen($source, 'r');

	rowResetScan($synch, true);
	while(!feof($f))
	{
		$row	= fgets($f);
		$row	= rowParse($synch, $row);

		$rows[]	= $row;
		$cols	= max($cols, count($row));
	}
?>
<?
$line	= 0;
foreach($rows as $ix=>$row)
{
	$class	= '';
	if ($r = rowIsRootCatalog($synch, $row)){
		$line	= 0;
		$class	= 'txtRowRootCatalog';
	}else
	if ($r = rowIsCatalog($synch, $row)){
		$line	= 0;
		$class	= 'txtRowCatalog';
	}else
	if ($r = rowIsFormat($synch, $row)){
		$line	= 0;
		$class	= 'txtRowFormat';
		foreach($r as $ix=>$name){
			$row[$ix]	.= " ($name)";
		}
	}else
	if ($r = rowIsProduct($synch, $row)){
		$class	= 'txtRowProduct';
		if (++$line > 2) continue;
		if ($line == 2){
			foreach($row as &$val) $val = '...';
		}
	}else{
		foreach($row as &$val){
			if ($val) break;
		};
		if (!$val && $false){
			$line	= 0;
			$class	= 'txtRowReset';
			rowResetScan($synch);
			$row[0]	= 'Format reset line...';
		}
	}
?>
<tr class="{$class}">
<? for($col = 0; $col < $cols; ++$col){?>
    <td>{$row[$col]}</td>
<? } ?>
</tr>
<? } ?>
</table>
</div>
<? } ?>
</form>
<? } ?>
