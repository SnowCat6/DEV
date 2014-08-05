<?
function user_lostForm(){
	if (testValue('lost')){
		if (module('user:lost', getValue('lost'))) return user_lostSend();
	}
	module('script:ajaxForm');
?>
    <link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css">
    <form method="post" action="{{getURL:user_lost}}" class="form login ajaxForm">
        <table border="0" cellspacing="0" cellpadding="2" width="300">
        <tr>
          <th nowrap="nowrap">Адрес e-mail:</th>
          <td width="100%"><input name="lost[login]" value="{$_POST[login][login]}" type="text" class="input w100" /></td>
        </tr>
        <tr>
          <td colspan="2"><p><input type="submit" value="Восстановить" class="button w100" /></p></td>
        </tr>
        </table>
    </form>
<? } ?>
<? function user_lostSend(){ ?>
{{message=Инстукция по восстановлению пароля выслана.}}
<? } ?>