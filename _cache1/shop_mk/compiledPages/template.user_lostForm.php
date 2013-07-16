<?
function user_lostForm(){
	if (testValue('lost')){
		if (module('user:lost', getValue('lost'))) return user_lostSend();
	}
	module('script:ajaxForm');
?>
    <? module("page:style", 'baseStyle.css') ?>
    <form method="post" action="<? module("getURL:user_lost"); ?>" class="form login ajaxForm">
        <table border="0" cellspacing="0" cellpadding="2" width="300">
        <tr>
          <th nowrap="nowrap">Адрес e-mail:</th>
          <td width="100%"><input name="lost[login]" value="<? if(isset($_POST["login"]["login"])) echo htmlspecialchars($_POST["login"]["login"]) ?>" type="text" class="input w100" /></td>
        </tr>
        <tr>
          <td colspan="2"><p><input type="submit" value="Восстановить" class="button w100" /></p></td>
        </tr>
        </table>
    </form>
<? } ?>
<? function user_lostSend(){ ?>
<? $module_data = array(); $module_data[] = "Инстукция по восстановлению пароля выслана."; moduleEx("message", $module_data); ?>
<? } ?>