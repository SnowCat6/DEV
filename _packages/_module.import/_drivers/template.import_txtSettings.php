<? function import_txtSettings(&$val)
{
	$ini	= getCacheValue('ini');

	$fields	= array();
	$fields['article']	= 'Артикул';
	$fields['name']		= 'Наименование';
	$fields['price']	= 'Цена';
	$fields['ed']		= 'Ед. измерения';
	$fields['delivery']	= 'Условия доставки';
	
	$values	= getValue('txtSettingsFields');
	if ($values && is_array($values)){
		foreach($values as $name=>$val){
			$ini[':txtImportFields'][$name] = $val;
		}
		setIniValues($ini);
	}
	if ($val = getValue('txtEncode')){
		$ini[':txtSettings']['encode']	= $val;
		setIniValues($ini);
	}
	
	$encodes	= explode(',', 'windows-1251,utf-8');
	$encode		= $ini[':txtSettings']['encode'];
	if (!$encode) $encode = $encodes[0];
	
?>
{{ajax:template=ajaxResult}}
<form action="{{url:#}}" method="post" class="ajaxForm ajaxReload">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="50%" valign="top">
<table border="0" cellspacing="0" cellpadding="2" class="table" width="100%">
<tr>
  <th>Данные товара</th>
  <th>Названия колонок через ";"</th>
</tr>
<?
$txtFields	= $ini[':txtImportFields'];
?>
<? foreach($fields as $n=>$name){ ?>
<tr>
    <td>{$name}</td>
    <td><input type="text" name="txtSettingsFields[{$n}]" value="{$txtFields[$n]}" class="input w100" /></td>
</tr>
<? } ?>
<tr>
  <td>Кодировка</td>
  <td>
<select name="txtEncode" class="input w100">
<? foreach($encodes as $name){ ?>
	<option value="{$name}"{selected:$encode==$name}>{$name}</option>
<? } ?>
</select>
  </td>
</tr>
</table>
    </td>
    <td width="50%" valign="top">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
  <tr>
    <th colspan="3">Формат данных для импорта <i>.txt</i> файлов</th>
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
    <p><em>Колнки разделяются знаком табуляции в кодировке <strong>{$encode}</strong></em></p>
    </tr>
</table>

<p>
	<input type="submit" value="Сохранить" class="button" />
</p>

<?
	$sources	= array();
	moduleEx('import:txtSource', $sources);
	$name		= getValue('source');
	$synch		= $sources[$name];
	if ($synch){

	$source	= $synch->getValue('source');
?>
<h2><a href="{$source}" target="_blank">{$source}</a></h2>
<style>
.txtRowRootCatalog{
	background:red;
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
</style>
<input type="hidden" name="source" value="{$name}" />
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="table">
<?
	$rows	= array();
	$cols	= 0;
	$f		= fopen($source, 'r');

	rowResetScan($synch);
	while(!feof($f))
	{
		$row	= fgets($f);
		if ($encode != 'utf-8'){
			$row	= iconv($encode, 'utf-8', $row);
		}
		
		$row	= explode("\t", $row);
		foreach($row as &$val) $val	= trim($val);
		$rows[]	= $row;
		$cols	= max($cols, count($row));
	}
?>
<?
$line	= 0;
foreach($rows as $row)
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
		$line++;
		if ($line > 2) continue;
		if ($line == 2){
			foreach($row as &$val) $val = '...';
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
<? } ?>
</form>
<? } ?>
