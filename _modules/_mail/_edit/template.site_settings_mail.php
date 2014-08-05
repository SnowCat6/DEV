<? function site_settings_mail($ini){ ?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <th nowrap="nowrap">Эл. адрес адмнинистратора</th>
    <td width="100%"><input type="text" name="settings[:mail][mailAdmin]" class="input w100" value="{$ini[:mail][mailAdmin]}" /></td>
  </tr>
  <tr>
    <th nowrap="nowrap">Эл.адрес обратной связи</th>
    <td><input type="text" name="settings[:mail][mailFeedback]" class="input w100" value="{$ini[:mail][mailFeedback]}" /></td>
  </tr>
  <tr>
    <th nowrap="nowrap">Эл.адрес заказов</th>
    <td><input type="text" name="settings[:mail][mailOrder]" class="input w100" value="{$ini[:mail][mailOrder]}" /></td>
  </tr>
  <tr>
    <th nowrap="nowrap">&nbsp;</th>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <th nowrap="nowrap">Обратный эл. адрес сообщений сайта</th>
    <td><input type="text" name="settings[:mail][mailFrom]" class="input w100" value="{$ini[:mail][mailFrom]}" /></td>
  </tr>
  <tr>
    <th nowrap="nowrap">Сервер SMTP</th>
    <td><input type="text" name="settings[:mail][SMTP]" class="input w100" value="{$ini[:mail][SMTP]}" /></td>
  </tr>
</table>
<? return 'Электронная почта'; } ?>