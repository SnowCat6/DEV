<? function user_loginPage()
{
	m('page:display:!message', '');
	m('script:ajaxLink');
	m('page:title', 'Вход на сайт');

	if ($id = userID()) return module("user:page:$id");
	
	$login = getValue('login');
	m('user:enter', $login);
?>
<link rel="stylesheet" type="text/css" href="css/userLogin.css">
<div class="loginPage">
    {{display:message}}
<form method="post" action="{{getURL:user_login}}" class="userLoginForm shadow">

<div class="loginHolder">

    <h2>Введите логин</h2>
    <div><input name="login[login]" type="text" class="input w100" placeholder="Логин" title="Логин" value="{$login[login]}" /></div>

    <h2>Введите пароль</h2>
    <div><input name="login[passw]" type="password" class="input password w100" placeholder="Пароль" title="Пароль" value="{$login[passw]}" /></div>

	<div class="loginButton"> <input type="submit" value="OK"/></div>

    <div class="loginOptions">
    <div>
        <label>
            <input type="checkbox" name="login[remember]" class="checkbox" id="loginRemember" value="1" {checked:$login[remember]} />
            Запомнить меня
        </label>
    </div>
    <div>
        <a href="{{getURL:user_lost}}" id="ajax">Напомнить пароль</a>
    </div>
    <? if (access('register', '')){ ?>
        <div><a href="{{getURL:user_register}}" id="ajax">Регистрация</a></div>
    <? } ?>
    </div>
</div>

</form>
</div>
<? } ?>
