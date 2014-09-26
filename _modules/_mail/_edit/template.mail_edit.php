<?
function mail_edit($db, $val, $data)
{
	$id		= $data[1];
	if (!access('write', "mail:$id")) return;
//	if (!hasAccessRole('admin,developer,writer,manager')) return;

	module('script:ajaxLink');
	module('script:jq_ui');

	$data	= $db->openID($id);
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
		$data	= $db->openID($id);
	}
	
	module('script:ajaxForm');
	module('message:error', $data['mailError']);
?>
<link rel="stylesheet" type="text/css" href="../../admin/css/admin.css">
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css">
<form action="{{getURL:admin_mail$id}}" method="post" class="admin ajaxFormNow ajaxReload">
{{page:title=$data[subject]}}
{{display:message}}
{{ajax:template=ajax_edit}}

<? if (is_array($data['document'])){ ?>
<div class="adminTabs ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#mailHtml">HTML</a></li>
    <li class="ui-corner-top"><a href="#mailText">Текст</a></li>
    <li class="ui-corner-top"><a href="#mailSMS">СМС</a></li>
    <li class="ui-corner-top"><a href="#mailInfo">Информация</a></li>
</ul>

<div id="mailInfo" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
    <table width="100%" border="0" cellspacing="0" cellpadding="2">
    <tr>
      <th align="left" nowrap>Тема письма</th>
      <td>{$data[subject]}</td>
    </tr>
    <tr>
        <th align="left" nowrap>Адрес отправителя</th>
        <td width="100%"><input name="mailData[from]" type="text" class="input w100" value="{$data[from]}" /></td>
    </tr>
    <tr>
      <th align="left" nowrap>Адрес получателя</th>
      <td><input name="mailData[to]" type="text" class="input w100" value="{$data[to]}" /></td>
    </tr>
    </table>
    <p><input type="submit" class="button" name="resendMail" value="Отправить повторно" /></p>
</div>

<div id="mailText" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<pre class="ui-state-highlight" style="padding:10px">{$data[document][plain]}</pre>
</div>

<div id="mailHtml" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<div class="ui-state-highlight" style="padding:10px">{!$data[document][html]}</div>
</div>

<div id="mailSMS" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<pre class="ui-state-highlight" style="padding:10px">{!$data[document][SMS]}</pre>
</div>
</div>

{{script:adminTabs}}
<? }else{ ?>
{{ajax:template=ajax}}
<h3>Сообщение:</h3>
<pre>{$data[document]}</pre>
<? } ?>
</form>
<? } ?>