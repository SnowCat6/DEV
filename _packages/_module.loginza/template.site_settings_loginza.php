<? function site_settings_loginza($ini)
{
	module('loginza:check');
	if (!loginza_check(false)){
		echo 'Не найден модуль CURL или он работает некорректно!';
		return 'OpenID Loginza';
	}
	
	if (!@$ini[':loginza']['URL']){
		$ini[':loginza']['URL'] = getURLEx('');
	}
?>
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
  <td nowrap="nowrap">URL сайта</td>
  <td><input type="text" name="settings[:loginza][URL]" class="input w100" value="{$ini[:loginza][URL]}" /></td>
</tr>
<tr>
  <td nowrap="nowrap">Код подтверждения прав</td>
  <td><input type="text" name="settings[:SEO][loginza-verification]" class="input w100" value="{$ini[:SEO][loginza-verification]}" /></td>
</tr>
<tr>
    <td nowrap="nowrap">Код сайта <strong>(ID)</strong></td>
    <td width="100%"><input type="text" name="settings[:loginza][id]" class="input w100" value="{$ini[:loginza][id]}" /></td>
</tr>
<tr>
  <td nowrap="nowrap">Секретный ключ</td>
  <td><input type="text" name="settings[:loginza][key]" class="input w100" value="{$ini[:loginza][key]}" /></td>
</tr>
</table>
<p>Для работы этого сервиса, вы должны зарегистрироваться на сайте <a href="http://loginza.ru/" target="_blank">http://loginza.ru/</a></p>
<p>В настройках добавть ваш сайт, и ввести полученый код и секретный ключ в настройки.</p>
<p>Для подтверждения прав на владение сайтом, введите код подтверждения.</p>
<? return 'OpenID Loginza'; } ?>