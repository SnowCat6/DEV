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
?><? module("page:style", 'admin.css') ?><? module("page:style", 'baseStyle.css') ?>
<form action="<? module("getURL:admin_mail$id"); ?>" method="post" class="admin ajaxFormNow ajaxReload">
<? $module_data = array(); $module_data[] = "Просмотр письма"; moduleEx("page:title", $module_data); ?><? module("display:message"); ?>
<table width="100%" border="0" cellspacing="0" cellpadding="2">
<tr>
    <th align="left" nowrap>Адрес отправителя</th>
    <td width="100%"><input name="mailData[from]" type="text" class="input w100" value="<? if(isset($data["from"])) echo htmlspecialchars($data["from"]) ?>" /></td>
</tr>
<tr>
  <th align="left" nowrap>Адрес получателя</th>
  <td><input name="mailData[to]" type="text" class="input w100" value="<? if(isset($data["to"])) echo htmlspecialchars($data["to"]) ?>" /></td>
</tr>
<tr>
  <th align="left" nowrap>Тема письма</th>
  <td><? if(isset($data["subject"])) echo htmlspecialchars($data["subject"]) ?></td>
</tr>
</table>

<p><input type="submit" class="button" name="resendMail" value="Отправить повторно" /></p>

<? if (is_array($data['document'])){ ?>
<div id="mailTabs" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
<ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
    <li class="ui-corner-top"><a href="#mailHtml">HTML</a></li>
    <li class="ui-corner-top"><a href="#mailText">Текст</a></li>
    <li class="ui-corner-top"><a href="#mailSMS">СМС</a></li>
</ul>

<div id="mailText" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<pre class="ui-state-highlight" style="padding:10px"><? if(isset($data["document"]["plain"])) echo htmlspecialchars($data["document"]["plain"]) ?></pre>
</div>

<div id="mailHtml" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<div class="ui-state-highlight" style="padding:10px"><? if(isset($data["document"]["html"])) echo $data["document"]["html"] ?></div>
</div>

<div id="mailSMS" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
<pre class="ui-state-highlight" style="padding:10px"><? if(isset($data["document"]["SMS"])) echo $data["document"]["SMS"] ?></pre>
</div>
</div>

<script>
$(function() {
	$("#mailTabs").tabs();
});
</script>
<? }else{ ?>
<h3>Сообщение:</h3>
<pre><? if(isset($data["document"])) echo htmlspecialchars($data["document"]) ?></pre>
<? } ?>
</form>
<? } ?>