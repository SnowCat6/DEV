<?
function mail_edit($db, $val, $data)
{
	if (!hasAccessRole('admin,developer,writer,manager')) return;
	module('script:ajaxLink');

	@$id = $data[1];
	$data = $db->openID($id);
	if (!$data) return;
	
	module('message:error', $data['mailError']);
?>
{{page:title=Просмотр письма}}
{{display:message}}
<table width="100%" border="0" cellspacing="0" cellpadding="2">
<tr>
    <th align="left" nowrap>Адрес отправителя</th>
    <td width="100%">{$data[from]}</td>
</tr>
<tr>
  <th align="left" nowrap>Адрес получателя</th>
  <td>{$data[to]}</td>
</tr>
<tr>
  <th align="left" nowrap>Тема письма</th>
  <td>{$data[subject]}</td>
</tr>
</table>
<br>
<? if (is_array($data['document'])){ ?>
<h3>Сообщение в тексте:</h3>
<pre>{$data[document][plain]}</pre>

<h3>Сообщение в HTML:</h3>
{!$data[document][html]}
<? }else{ ?>
<h3>Сообщение:</h3>
<pre>{$data[document]}</pre>
<? } ?>
<? } ?>