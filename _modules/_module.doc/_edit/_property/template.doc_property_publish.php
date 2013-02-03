<? function doc_property_publish($data){?>
<?
	$db = module('doc', $data);
	module('script:calendar');
?>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
<tr>
    <td width="33%" valign="top">
Дата публикации
<div><input name="doc[datePublish]" type="text" value="{$data[datePublish]}" class="input w100" id="calendarPublish" /></div>
    </td>
    <td width="33%" valign="top">&nbsp;</td>
    <td width="33%" valign="top">&nbsp;</td>
</tr>
</table>

<? return '10-Публикация'; } ?>