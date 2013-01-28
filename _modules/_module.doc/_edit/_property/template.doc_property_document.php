<? function doc_property_document($data){ ?>
<? $db = module('doc', $data) ?>
<div><input name="doc[title]" type="text" value="{$data[title]}" class="input w100" /></div>
<div><textarea name="doc[originalDocument]" id="document2" cols="" rows="35" class="input w100">{$data[originalDocument]}</textarea></div>
<? return 'Документ'; } ?>