<?
//	+function docConfig_SEO_update
function docConfig_SEO_update(&$data)
{
	if (!hasAccessRole('developer')) return;
	list($typeName, $templateName)	= explode(':', $data['id']);
	if (!$typeName) return;

	$nameSEO= trim($typeName."_$templateName", '_');
//	$iniType= getStorage("SEO_$nameSEO", 'ini');
	$SEO	= getValue("SEO_$nameSEO");
	if (!is_array($SEO)) $SEO	= getValue("SEO_");
	if (!is_array($SEO)) return;

	setStorage("SEO_$nameSEO", $SEO, 'ini');
}
?>
<?
//	+function docConfig_SEO
function docConfig_SEO($data)
{
	if (!hasAccessRole('developer')) return;
	list($typeName, $templateName)	= explode(':', $data['id']);

	$nameSEO= trim($typeName."_$templateName", '_');
	$iniType= getStorage("SEO_$nameSEO", 'ini');
?>

Заголовок (title), перезаписывает автоматически сгенерированный
<div><input name="SEO_{$nameSEO}[title]" type="text" value="{$iniType[title]}" class="input w100" /></div>
Ключевые слова (keywords metatag)
<div><input name="SEO_{$nameSEO}[keywords]" type="text" value="{$iniType[keywords]}" class="input w100" /></div>
Описание (description metatag)
<div><textarea name="SEO_{$nameSEO}[description]" cols="" rows="5" class="input w100">{$iniType[description]}</textarea></div>

<? return 'SEO'; } ?>