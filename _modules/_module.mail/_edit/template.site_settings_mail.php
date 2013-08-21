<? function site_settings_mail($ini){ ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td nowrap="nowrap">Эл. адрес адмнинистратора</td>
    <td width="100%"><input type="text" name="settings[:mail][mailAdmin]" class="input w100" value="{$ini[:mail][mailAdmin]}" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Эл.адрес обратной связи</td>
    <td><input type="text" name="settings[:mail][mailFeedback]" class="input w100" value="{$ini[:mail][mailFeedback]}" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Эл.адрес заказов</td>
    <td><input type="text" name="settings[:mail][mailOrder]" class="input w100" value="{$ini[:mail][mailOrder]}" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td nowrap="nowrap">Обратный эл. адрес сообщений сайта</td>
    <td><input type="text" name="settings[:mail][mailFrom]" class="input w100" value="{$ini[:mail][mailFrom]}" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Сервер SMTP</td>
    <td><input type="text" name="settings[:mail][SMTP]" class="input w100" value="{$ini[:mail][SMTP]}" /></td>
  </tr>
</table>
<? return 'Электронная почта'; } ?>