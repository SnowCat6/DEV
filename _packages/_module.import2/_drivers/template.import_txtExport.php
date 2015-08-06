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

	makeDir(dirname($file));

	$f	= fopen($file, 'w');

	$row= array();
	foreach($fieldsNames as $field => $name)
	{
		$n		= explode(';', $$name);
		if ($n) $n = $n[0];
		$row[]	= $n?$n:$name;
	}

	importExportWriteLn($f, $row);

	$db	= module('doc:find', array(
		'type' => 'product'
	));
	while($data = $db->next())
	{
		$id		= $db->id();
		$row	= array();
		foreach($fieldsNames as $field => $name)
		{
			if (strncmp($field, ':property.', 10) == 0)
			{
				$id			= $db->id();
				$field		= substr($field, 10);
				$property	= module("prop:get:$id");
				$row[$field]= $property[$field];
			}else{
				$fn		= getFn("txtExportField_$field");
				if ($fn) $val	= $fn($db, $field, $data);
				else $val	= importExportGetField($data, $field);

				$val		= preg_replace('/[\x00-\x1F\x80-\x9F]/u', '', $val);
				$val		= trim($val);
				$row[$name]	= $val;
			}
		}
		importExportWriteLn($f, $row);
	}
	fclose($f);
}
/***********************************/
function importExportWriteLn($fp, $val)
{
	if (is_array($val)) $val	= implode("\t", $val);

	$enc	= getiniValue(':txtSettings');
	$enc	= $enc['encode'];
	if (!$enc) $enc	= 'windows-1251';
	$val	= iconv('utf-8', $enc, $val);
	
	fwrite($fp, $val);
	fwrite($fp, "\r\n");
}
function importExportGetField($data, $path)
{
	$path	= explode('.', $path);
	foreach($path as $name){
		if (!is_array($data)) return NULL;
		$data	= $data[$name];
	}
	return $data;
}
/***********************************/
function txtExportField_name($db, $field, $data)
{
	return $data['title'];
}
function txtExportField_article($db, $field, $data)
{
	$val	= importExportGetField($data, 'fields.any.import.:importArticle');
	if ($val) return $val;
	
	$val	= importMakeArticle($data);
	$d		= array();
	$d['fields']['any']['import'][':importArticle']	= $val;
	m("doc:update:$id:edit", $d);

	return $val;
}
function txtExportField_parent1($db, $field, $data)
{
	$parents	= getPageParents($db->id());
	$data		= module("doc:data:$parents[0]");
	return $data['title'];	
}
function txtExportField_parent2($db, $field, $data)
{
	$parents	= getPageParents($db->id());
	$data		= module("doc:data:$parents[1]");
	return $data['title'];	
}
function txtExportField_parent3($db, $field, $data)
{
	$parents	= getPageParents($db->id());
	$data		= module("doc:data:$parents[2]");
	return $data['title'];	
}
?>