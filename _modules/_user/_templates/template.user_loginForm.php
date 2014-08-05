<?
function user_loginForm($db, $val, $data){
	$login = getValue('login');
?>
{{script:ajaxLink}}
<? if (!defined('userID')){ ?>
<form method="post" action="{{getURL:user_login}}" class="form login">
<div style="width:230px">
<table border="0" cellspacing="0" cellpadding="2" width="100%" class="loginInput">
    <tr>
        <th nowrap="nowrap">Логин:</th>
        <td width="100%"><input name="login[login]" value="{$login[login]}" type="text" class="input w100" /></td>
    </tr>
    <tr>
        <th nowrap="nowrap">Пароль:</th>
        <td width="100%"><input name="login[passw]" type="password" value="{$login[passw]}" class="input password w100" /></td>
    </tr>
</table>
<table width="100%" border="0" cellpadding="0" cellspacing="0" class="loginOptions">
<? if ($val){?>
<tr>
    <td valign="top" nowrap="nowrap"><label for="loginRemember">Помнить меня</label></td>
    <td align="right" valign="top"><input type="checkbox" name="login[remember]" class="checkbox" id="loginRemember" value="1"<?= @$login['remember']?' checked="checked"':''?> /></td>
</tr>
<? } ?>
  <tr>
    <td valign="top" nowrap="nowrap">
<? if (access('register', '')){ ?>
<div><a href="{{getURL:user_register}}" id="ajax">Регистрация</a><br /></div>
<? } ?>
<? if (!$val){ ?><div>{{loginza:enter}}</div><? } ?>
<? if ($val){ ?><div><a href="{{getURL:user_lost}}" id="ajax">Напомнить пароль?</a></div><? } ?>
  	</td>
    <td align="right" valign="top"><input type="submit" value="OK" class="button" /></td>
  </tr>
</table>
</div>
</form>
<? }else{ ?>
<div class="form">
<a href="{{getURL=logout}}">Выход</a>
</div>
<? } ?>
<? } ?>