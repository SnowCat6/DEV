<? function module_tools(){ ?>
{{script:jq}}
<script src="script/jq.mailTools.js"></script>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>Адреса электронной почты</td>
    <td>Лог ошибок</td>
  </tr>
  <tr>
    <td width="50%"><textarea id="mailInput" name="mailInput" class="input w100" rows="20"></textarea></td>
    <td width="50%"><textarea id="mailError" name="mailError" class="input w100" rows="20"></textarea></td>
  </tr>
</table>
<p><table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><input type="button" id="doReformat" value="Обработать" /></td>
    <td>Разделять по</td>
    <td>
    <input name="splitMails" type="text" id="splitMails" value="20" size="5"></td>
    <td>писем</td>
  </tr>
</table>
</p>
<? } ?>
