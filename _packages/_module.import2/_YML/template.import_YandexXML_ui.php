<? function import_YandexXML_ui($val, $data)
{
	m("import:YandexXML", $data);
	
	m('page:title', 'Файл yandex.xml создан');
	$url	= ('yandex.xml');
	echo "<a href=\"$url\">$url</a>";
}?>

<?
//	+function import_YandexXML_direct
function import_YandexXML_direct($val, $data)
{
	setTemplate('');
	header('Content-Type: text/xml; charset=utf-8');
	echo moduleEx("import:YandexXML", $data);
}?>
