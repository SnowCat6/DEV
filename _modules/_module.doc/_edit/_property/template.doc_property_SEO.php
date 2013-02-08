<? function doc_property_SEO($data){?>
<?
	$db		= module('doc', $data);
	$id		= $db->id();
	$type	= $data['doc_type'];
	@$fields= $data['fields'];
?>
Заголовок (title), перезаписывает автоматически сгенерированный
<div><input name="doc[fields][title]" type="text" value="{$fields[title]}" class="input w100" /></div>
Ключевые слова (keywords metatag)
<div><input name="doc[fields][keywords]" type="text" value="{$fields[keywords]}" class="input w100" /></div>
Описание (description metatag)
<div><textarea name="doc[fields][description]" cols="" rows="5" class="input w100">{$fields[description]}</textarea></div>
<? return '10-SEO'; } ?>