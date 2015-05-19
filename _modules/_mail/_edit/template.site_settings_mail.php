<? function site_settings_mail($ini){ ?>

{{script:splitInput}}
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
    <td width="100%"><input type="text" name="settings[:mail][mailAdmin]" class="input w100 splitInput" value="{$ini[:mail][mailAdmin]}" /></td>
  </tr>
  <tr>
    <th nowrap="nowrap">Эл.адрес обратной связи</th>
    <td><input type="text" name="settings[:mail][mailFeedback]" class="input w100 splitInput" value="{$ini[:mail][mailFeedback]}" /></td>
  </tr>
  <tr>
    <th nowrap="nowrap">Эл.адрес заказов</th>
    <td><input type="text" name="settings[:mail][mailOrder]" class="input w100 splitInput" value="{$ini[:mail][mailOrder]}" /></td>
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
            <td nowrap="nowrap">Эл. почта СМС оператора</td>
            <td width="100%"><input type="text" name="settings[:mail][SMS_MAIL]" class="input w100 splitInput" value="{$ini[:mail][SMS_MAIL]}" /></td>
          </tr>
        </tbody>
      </table></td>
    </tr>
  </tbody>
</table>


<? return 'Уведомления'; } ?>