<? function module_master($val, &$evData)
{
	switch($val){
	case 'edit';
		$data		= $evData[2];
		if ($data['doc_type'] != 'article') return;

		$evData[0]	= 'doc_property_document_master';
		$evData[1]	= __FILE__;
		break;
	case 'update':
		$d		= &$evData[0];
		$data	= &$evData[1];
	
		if (isset($data['masterDate']))
		{
			if ($data['masterDate']){
				$d['masterDate'] = makeSQLDate(makeDateStamp($data['masterDate']));
			}else{
				$d['masterDate'] = NULL;
			}
		}
		break;
	}
}
?><?
//	Редактирование документа
function doc_property_document_master($data)
{
$db		= module('doc', $data);
$id		= $db->id();
$type	= $data['doc_type'];
@$fields= $data['fields'];
$date	= makeDate($data['masterDate']);
if ($date) $date = dateStamp($date);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td nowrap="nowrap">Дата проведения</td>
    <td width="100%" nowrap="nowrap">Название мастер-класса</td>
    <td align="right" nowrap="nowrap" style="padding-right:50px">Место прохождения</td>
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
    <td><input name="doc[masterDate]" id="calendarEvent" type="text" value="<? if(isset($date)) echo htmlspecialchars($date) ?>" class="input w100" /></td>
    <td><input name="doc[title]" type="text" value="<? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?>" class="input w100" /></td>
    <td colspan="3" align="right"><input name="doc[fields][any][place]" type="text" value="<? if(isset($fields["any"]["place"])) echo htmlspecialchars($fields["any"]["place"]) ?>" class="input w100" /></td>
  </tr>
</table>
Аннотация мастер-класса, краткое описание
<div><textarea name="doc[fields][note]" cols="" rows="5" class="input w100"><? if(isset($fields["note"])) echo htmlspecialchars($fields["note"]) ?></textarea></div>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="100%">Описание мастер-класса</td>
    <td><? module("snippets:tools:doc[originalDocument]"); ?></td>
  </tr>
</table>
<div><textarea name="doc[originalDocument]" cols="" rows="25" class="input w100 editor"><? if(isset($data["originalDocument"])) echo htmlspecialchars($data["originalDocument"]) ?></textarea></div>
<? return '1-Документ'; } ?>
