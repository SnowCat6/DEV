<? function module_address($val, &$evData)
{
	switch($val){
	case 'edit';
		$data		= $evData[2];
		if ($data['doc_type'] != 'article') return;

		$evData[0]	= 'doc_property_document_address';
		$evData[1]	= __FILE__;
		break;
	}
}
?><?
//	Редактирование документа
function doc_property_document_address($data)
{
$db		= module('doc', $data);
$id		= $db->id();
$type	= $data['doc_type'];
$fields	= $data['fields'];
$date	= makeDate($data['masterDate']);
if ($date) $date = dateStamp($date);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%" nowrap="nowrap">Название ресторана-партнера</td>
    <td align="right" nowrap="nowrap">
  <? if ($id){?>
  <label for="saveAsCopy">Сохранить как копию</label>
  <input type="checkbox" name="saveAsCopy" id="saveAsCopy" value="doCopy" />
  <? } ?>
</td>
<td align="right" nowrap="nowrap">
<label for="copyExternal">Копировать файлы из интернета</label>
<input type="checkbox" name="copyExternal" id="copyExternal" value="doCopy" />
</td>
  </tr>
  <tr>
    <td colspan="3"><input name="doc[title]" type="text" value="<? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?>" class="input w100" /></td>
  </tr>
</table>
Адрес WEB сайта, к примеру: <strong>http://foodsmile.ru</strong>
<div><input type="text" name="doc[fields][any][url]" value="<? if(isset($fields["any"]["url"])) echo htmlspecialchars($fields["any"]["url"]) ?>" class="input w100" /></div>
Адреса ресторанов, в каждой строчке новый адрес, к примеру: <strong>г.Екатеринбург, Гагарина 38</strong>
<div><textarea name="doc[fields][any][places]" cols="" rows="5" class="input w100"><? if(isset($fields["any"]["places"])) echo htmlspecialchars($fields["any"]["places"]) ?></textarea></div>
Краткое описание
<div><textarea name="doc[fields][any][note]" cols="" rows="5" class="input w100"><? if(isset($fields["any"]["note"])) echo htmlspecialchars($fields["any"]["note"]) ?></textarea></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%">Страничка партера</td>
    <td><? module("snippets:tools:doc[originalDocument]"); ?></td>
  </tr>
</table>
<div><textarea name="doc[originalDocument]" cols="" rows="25" class="input w100 editor"><? if(isset($data["originalDocument"])) echo htmlspecialchars($data["originalDocument"]) ?></textarea></div>
<? return '1-Документ'; } ?>
