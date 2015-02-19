<? function site_settings_mail($ini){ ?>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tbody>
    <tr>
      <td>
      <h2 class="ui-state-default">Электронная почта</h2>
		</td>
      <td>
<h2 class="ui-state-default">SMS уведомления</h2>
      </td>
    </tr>
    <tr>
      <td width="50%" valign="top">
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
    <th nowrap="nowrap">Обратный эл. адрес</th>
    <td><input type="text" name="settings[:mail][mailFrom]" class="input w100" value="{$ini[:mail][mailFrom]}" /></td>
  </tr>
  <tr>
    <th nowrap="nowrap">Сервер SMTP</th>
    <td><input type="text" name="settings[:mail][SMTP]" class="input w100" value="{$ini[:mail][SMTP]}" /></td>
  </tr>
</table>
      </td>
      <td width="50%" valign="top"><table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tbody>
          <tr>
            <td nowrap="nowrap">Номера телефонов Мегафон</td>
            <td><input type="text" name="settings[:mail][SMS_MEGAPHONE]" class="input w100" value="{$ini[:mail][SMS_MEGAPHONE]}" /></td>
          </tr>
          <tr>
            <td nowrap="nowrap">Номера телефонво МТС</td>
            <td><input type="text" name="settings[:mail][SMS_MTS]" class="input w100" value="{$ini[:mail][SMS_MTS]}" /></td>
          </tr>
          <tr>
            <td nowrap="nowrap">Номера телефонов Билайн</td>
            <td><input type="text" name="settings[:mail][SMS_BEELINE]" class="input w100" value="{$ini[:mail][SMS_BEELINE]}" /></td>
          </tr>
          <tr>
            <td nowrap="nowrap">&nbsp;</td>
            <td>&nbsp;</td>
          </tr>
          <tr>
            <td nowrap="nowrap">Сервер шлюза SMTP</td>
            <td width="100%"><input type="text" name="settings[:mail][SMS_SMTP]" class="input w100" value="{$ini[:mail][SMS_SMTP]}" /></td>
          </tr>
        </tbody>
      </table></td>
    </tr>
  </tbody>
</table>


<? return 'Уведомления'; } ?>