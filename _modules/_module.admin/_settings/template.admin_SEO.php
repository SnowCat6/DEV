<?
function admin_SEO(&$data)
{
	if (!hasAccessRole('admin,developer,SEO')) return;
	
	$SEO	= getValue('SEO');
	if (is_array($SEO))
	{
		$newSEO	= getValue('nameSEO');
		$newSEOv= getValue('valueSEO');
		if (is_array($newSEO)){
			foreach($newSEO as $ndx => $name){
				$name = trim($name);
				if (!$name) continue;
				@$SEO[$name] = trim($newSEOv[$ndx]);
			}
		}
		
		$ini	= getCacheValue('ini');
		$ini[':SEO']	= $SEO;
		setIniValues($ini);
		module('message', 'Конфигурация сохранена');
	}

	$ini	= getCacheValue('ini');
	@$SEO	= $ini[':SEO'];
	if (!is_array($SEO)) $SEO = array();
	
	module('script:ajaxForm');
	module('script:clone');
?>
{{page:title=Настройки SEO}}
<form action="{{getURL:admin_SEO}}" method="post" class="admin ajaxForm">
Заголовок (title) для всех страниц сайта, знак % заменяется на заголовок документов
<div><input name="SEO[title]" type="text" value="{$SEO[title]}" class="input w100" /></div>
Ключевые слова (keywords metatag) для всех старниц сайта
<div><input name="SEO[keywords]" type="text" value="{$SEO[keywords]}" class="input w100" /></div>
Описание (description metatag) для всех старниц сайта
<div><textarea name="SEO[description]" cols="" rows="5" class="input w100">{$SEO[description]}</textarea></div>
Собственные метатеги для всех старниц сайта
<table width="100%" border="0" cellspacing="0" cellpadding="2">
<tr>
    <th></th>
    <th nowrap="nowrap">Название метатега (name)</th>
    <th nowrap="nowrap">Значение метатега (content)</th>
</tr>
<?
foreach($SEO as $name => $val){
	if ($name == 'keywords' || $name == 'description' || $name == 'title')
		continue;
?>
<tr>
    <td><a class="delete" href="">X</a></td>
    <td>{$name}</td>
    <td width="100%"><input name="SEO[{$name}]" type="text" value="{$val}" class="input w100" /></td>
</tr>
<? } ?>
<tr class="adminReplicate" id="addMeta">
    <td><a class="delete" href="">X</a></td>
    <td><input name="nameSEO[]" type="text" value="" class="input w100" /></td>
    <td width="100%"><input name="valueSEO[]" type="text" value="" class="input w100" /></td>
</tr>
</table><br />

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><input type="button" class="button adminReplicateButton" id="addMeta" value="Добавть метатег" /></td>
    <td align="right"><input name="Submit" type="submit" class="ui-button ui-widget ui-state-default ui-corner-all" value="Записать" /></td>
  </tr>
</table>

</form>
<? return '5-SEO'; } ?>