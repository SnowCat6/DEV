<? function module_callback($val, $data)
{
	$callBackPhone = getValue('callBackPhone');
	if ($callBackPhone){
		$data = array();
		$data['phone']	= $callBackPhone;
		$data['html']	= htmlspecialchars($callBackPhone);
		$data['plain']	= $callBackPhone;
		$template		= module('mail:template', 'callbackPhone');
		m("mail:send:::$template:Заказ обратного звонка", $data);
		m('message', "Ваш номер отправлен");
	}
	m('script:maskInput');
	m('script:ajaxForm');
?>
<? module("page:style", 'callback.css') ?>
<form action="<? module("url:#"); ?>" class="callback ajaxFormNow ajaxReload" method="post">
<a href="#">Заказать звонок</a>
<? module("display:message"); ?>
<? if (!$callBackPhone){ ?>
<div class="callbackHolder">
<div class="callbackBackground">
<div id="formReadMessage"></div>
<p>Оставте номер вашего телефона</p>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td width="100%"><input name="callBackPhone" type="text" class="input w100 phone" value="<? if(isset($callBackPhone)) echo htmlspecialchars($callBackPhone) ?>"></td>
    <td><input name="" type="submit" class="callbackButton" value=" "></td>
</tr>
</table>
</div>
</div>
<? } ?>
<div class="iconCallback"></div>
</form>
<? } ?>