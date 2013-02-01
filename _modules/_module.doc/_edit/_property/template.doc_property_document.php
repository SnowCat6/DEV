<? function doc_property_document($data){ ?>
<? $db = module('doc', $data) ?>
Заголовок
<div><input name="doc[title]" type="text" value="{$data[title]}" class="input w100" /></div>
Текст документа
<div><textarea name="doc[originalDocument]" cols="" rows="35" class="input w100 editor">{$data[originalDocument]}</textarea></div>
<? return '1-Документ'; } ?>