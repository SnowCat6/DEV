<? function user_loginPage()
{
	m('page:display:!message', '');
	m('script:ajaxLink');
	m('user:enter', getValue('login'));
	
	m('page:title', 'Вход на сайт');
	
	if ($id = userID()) return module("user:page:$id");
?>
<link rel="stylesheet" type="text/css" href="css/userLogin.css">

{push}
{{user:loginForm:page}}
{pop:loginForm}

<div class="loginPage">
    <h2>Введите логин и пароль</h2>
    {{display:message}}
    {{display:loginForm}}
</div>
<? } ?>
