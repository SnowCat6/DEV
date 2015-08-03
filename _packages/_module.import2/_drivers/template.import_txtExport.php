<? function import_txtExport(&$val, &$ev)
{
	$folder	= $ev['folder'];
	$file	= "$folder/txtExport.txt";
	if (getValue('doTextExport')) doTextExport($file);
?>
<h3>Выгрузка товаров как .txt файл</h3>
<? if (file_exists($file)){?>
<p><a href="{$file}">{$file}</a></p>
<? } ?>
<form action="{{url:import_export}}" method="post">
    <input type="submit" name="doTextExport" class="button" value="Начать экспорт" />
</form>
<? } ?>

<? function doTextExport($file)
{
	$fieldsNames	= getIniValue(':txtImportFields');
	if (!is_array($fieldsNames))
	{
		$fieldsNames	= array(
			'article'	=> 'Артикул',
			'name'		=> 'Наименование',
			'price'		=> 'Цена'
		);
	}

	$fields	= array(
		'article'	=> 'fields.any.import.:importArticle',
		'name'		=> 'title',
		'price'		=> 'price'
	);

	foreach($fieldsNames as $fieldName => $colName)
	{
		if (isset($fields[$colName]) || isset($fields[$fieldName])) continue;
		$fields[$colName]	= $fieldName;
	}

	makeDir(dirname($file));
	
	$db	= module('doc:find', array(
		'type' => 'product'
	));

	foreach($fields as &$documentField){
		$documentField	= explode('.', $documentField);
	}
	
	$f	= fopen($file, 'w');

	$row= array();
	foreach($fields as $name => $field)
	{
		$n		= explode(';', $fieldsNames[$name]);
		if ($n) $n = $n[0];
		$row[]	= $n?$n:$name;
	}

	$row	= implode("\t", $row);
	$row	= iconv('utf-8', 'windows-1251', $row);
	fwrite($f, $row);
	fwrite($f, "\r\n");

	while($data = $db->next())
	{
		$id		= $db->id();
		$row	= array();
		foreach($fields as $name => $field)
		{
			if (strncmp($name, ':property.', 10) == 0)
			{
				$id			= $db->id();
				$name		= substr($name, 10);
				$property	= module("prop:get:$id");
				
				$field		= $field[0];
				$row[$field]= $property[$name];
			}else{
				$val	= importExportGetField($data, $field);
				$val	= preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', $val);
				$val	= trim($val);
				if ($name == 'article' && !$val){
					$val	= importMakeArticle($data);
					$d		= array();
					$d['fields']['any']['import'][':importArticle']	= $val;
					m("doc:update:$id:edit", $d);
				}
				$row[$name]	= $val;
			}
		}
		$row	= implode("\t", $row);
		$row	= iconv('utf-8', 'windows-1251', $row);
		fwrite($f, $row);
		fwrite($f, "\r\n");
	}
	fclose($f);
}
function importExportGetField($data, $path)
{
	foreach($path as $name){
		if (!is_array($data)) return NULL;
		$data	= $data[$name];
	}
	return $data;
}?>