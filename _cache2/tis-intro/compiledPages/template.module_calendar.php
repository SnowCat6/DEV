<? function module_calendar($val, &$evData)
{
	switch($val){
	case 'edit';
		$data		= $evData[2];
		if ($data['doc_type'] != 'article') return;
		
		$evData[0]	= 'doc_property_document_calendar';
		$evData[1]	= __FILE__;
		break;
	case 'update':
		$d		= &$evData[0];
		$data	= &$evData[1];
	
		if (isset($data['eventDate']))
		{
			if ($data['eventDate']){
				$d['eventDate'] = makeSQLDate(makeDateStamp($data['eventDate']));
			}else{
				$d['eventDate'] = NULL;
			}
		}
		break;
	}
}
?>

<? function doc_property_document_calendar($data){ ?>
<?
module('script:calendar');
$db		= module('doc', $data);
$type	= $data['doc_type'];
$price	= docPrice($data);
$date	= makeDate($data['eventDate']);
if ($date) $date = dateStamp($date);
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td nowrap="nowrap">Дата события</td>
    <td> Заголовок </td>
  </tr>
  <tr>
    <td><input name="doc[eventDate]" type="text" class="input" value="<? if(isset($date)) echo htmlspecialchars($date) ?>" size="14" id="calendarEvent" /></td>
    <td width="100%"><input name="doc[title]" type="text" value="<? if(isset($data["title"])) echo htmlspecialchars($data["title"]) ?>" class="input w100" /></td>
  </tr>
</table>
Текст документа
<div><textarea name="doc[originalDocument]" cols="" rows="35" class="input w100 editor"><? if(isset($data["originalDocument"])) echo htmlspecialchars($data["originalDocument"]) ?></textarea></div>
<? return '1-Документ'; } ?>