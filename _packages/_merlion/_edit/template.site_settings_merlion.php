<? function site_settings_merlion($ini){ ?>
<table border="0" cellspacing="0" cellpadding="2">
  <tr>
    <td nowrap="nowrap">Логин</td>
    <td><input type="text" name="settings[:merlion][login]" class="input" value="{$ini[:merlion][login]}" /></td>
    <td nowrap="nowrap">Обновлять каждые ... часов</td>
    <td><input type="text" name="settings[:merlion][updateEveryHour]" class="input" value="{$ini[:merlion][updateEveryHour]}" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Код</td>
    <td><input type="text" name="settings[:merlion][code]" class="input" value="{$ini[:merlion][code]}" /></td>
    <td nowrap="nowrap">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td nowrap="nowrap">Пароль</td>
    <td><input type="text" name="settings[:merlion][passw]" class="input" value="{$ini[:merlion][passw]}" /></td>
    <td nowrap="nowrap">&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>
<? return 'Merlion'; } ?>