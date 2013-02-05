<? function doc_property_publish_update($data){
}?>
<? function doc_property_publish($data){?>
<?
	$db		= module('doc', $data);
	$id		= $db->id();
	$type	= $data['doc_type'];
	module('script:calendar');
	
	if (!$id){
		if ($type == 'article') $data['datePublish'] = date('d.m.Y');
	}else{
		$date = makeDate($data['datePublish']);
		if ($date) $data['datePublish'] = date('d.m.Y H:i', $date);
	}
	$folder	= $db->folder();
	$folder	= "$folder/Gallery";
?>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
<tr>
    <td width="33%" valign="top">
Дата публикации
<div><input name="doc[datePublish]" type="text" value="{$data[datePublish]}" class="input w100" id="calendarPublish" /></div>
    </td>
    <td width="33%" valign="top">
<div><? module('gallery:upload:document', $data) ?></div>
    </td>
    <td width="33%" valign="top">&nbsp;</td>
</tr>
</table>

<? return '10-Публикация'; } ?>