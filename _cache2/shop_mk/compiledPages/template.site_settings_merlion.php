<? function site_settings_merlion($ini){ ?>
<table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td nowrap="nowrap">Логин</td>
    <td width="100%"><input type="text" name="settings[:merlion][login]" class="input" value="<? if(isset($ini[":merlion"]["login"])) echo htmlspecialchars($ini[":merlion"]["login"]) ?>" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Код</td>
    <td width="100%"><input type="text" name="settings[:merlion][code]" class="input" value="<? if(isset($ini[":merlion"]["code"])) echo htmlspecialchars($ini[":merlion"]["code"]) ?>" /></td>
  </tr>
  <tr>
    <td nowrap="nowrap">Пароль</td>
    <td width="100%"><input type="text" name="settings[:merlion][passw]" class="input" value="<? if(isset($ini[":merlion"]["passw"])) echo htmlspecialchars($ini[":merlion"]["passw"]) ?>" /></td>
  </tr>
</table>
<? return 'Merlion'; } ?>