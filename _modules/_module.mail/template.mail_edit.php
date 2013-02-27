<?
function mail_edit($db, $val, $data)
{
	if (!hasAccessRole('admin,developer,writer,manager')) return;
	module('script:ajaxLink');

	@$id = $data[1];
	$data = $db->openID($id);
	if (!$data) return;
	
	if (is_array($d = getValue('mailData')))
	{
		$d['id']		= $id;
		$d['mailStatus']= 'sendWait';
		$db->update($d, false);
		$d	= $db->openID($id);
		
		$a			= array();
		$mailFrom	= $d['from'];
		$mailTo		= $d['to'];
		$title		= $d['subject'];
		$mail		= $d['document'];
		$error		= mailAttachment($mailFrom, $mailTo, $title, $mail, '', $a);

		if ($error){
			$d = array();
			$d['mailStatus']	= 'sendFalse';
			$d['mailError']		= $error;
			$db->setValues($id, $d, false);
		}else{
			$d = array();
			$d['mailStatus']	= 'sendOK';
			$d['mailError']		= '';
			$db->setValues($id, $d, false);
			module('message', "Сообщение успешно отправлено");
		}
		$d	= $db->openID($id);
	}
	
	module('script:ajaxForm');
	module('message:error', $data['mailError']);
?>
<form action="{{getURL:admin_mail$id}}" method="post" class="admin ajaxFormNow ajaxReload">
{{page:title=Просмотр письма}}
{{display:message}}
<table width="100%" border="0" cellspacing="0" cellpadding="2">
<tr>
    <th align="left" nowrap>Адрес отправителя</th>
    <td width="100%"><input name="mailData[from]" type="text" class="input w100" value="{$data[from]}" /></td>
</tr>
<tr>
  <th align="left" nowrap>Адрес получателя</th>
  <td><input name="mailData[to]" type="text" class="input w100" value="{$data[to]}" /></td>
</tr>
<tr>
  <th align="left" nowrap>Тема письма</th>
  <td>{$data[subject]}</td>
</tr>
</table>

<p><input type="submit" class="button" name="resendMail" value="Отправить повторно" /></p>

<? if (is_array($data['document'])){ ?>
<h3>Сообщение в тексте:</h3>
<pre>{$data[document][plain]}</pre>

<h3>Сообщение в HTML:</h3>
{!$data[document][html]}
<? }else{ ?>
<h3>Сообщение:</h3>
<pre>{$data[document]}</pre>
<? } ?>
</form>
<? } ?>