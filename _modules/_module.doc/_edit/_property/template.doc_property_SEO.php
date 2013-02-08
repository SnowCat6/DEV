<? function doc_property_SEO_update(&$data)
{
	if (!hasAccessRole('admin,developer,SEO')) return;
	
	$SEO	= getValue('SEO');
	$newSEO	= getValue('nameSEO');
	$newSEOv= getValue('valueSEO');
	if (is_array($newSEO)){
		foreach($newSEO as $ndx => $name){
			$name = trim($name);
			if (!$name) continue;
			@$SEO[$name] = trim($newSEOv[$ndx]);
		}
	}
	
	if (!is_array($SEO)) return;
	$data['fields']['SEO'] = $SEO;
} ?>
<? function doc_property_SEO($data){?>
<?
	if (!hasAccessRole('admin,developer,SEO')) return;

	$db		= module('doc', $data);
	$id		= $db->id();
	$type	= $data['doc_type'];
	@$fields= $data['fields'];
	@$SEO	= $fields['SEO'];
	if (!is_array($SEO)) $SEO = array();
?>
Заголовок (title), перезаписывает автоматически сгенерированный
<div><input name="SEO[title]" type="text" value="{$SEO[title]}" class="input w100" /></div>
Ключевые слова (keywords metatag)
<div><input name="SEO[keywords]" type="text" value="{$SEO[keywords]}" class="input w100" /></div>
Описание (description metatag)
<div><textarea name="SEO[description]" cols="" rows="5" class="input w100">{$SEO[description]}</textarea></div>
Собственные метатеги
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
</table>
<p><input type="button" class="button adminReplicateButton" id="addMeta" value="Добавть метатег"></p>
<? return '10-SEO'; } ?>