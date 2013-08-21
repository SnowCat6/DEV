<? function module_loginza($val){
	$fn			= getFn("loginza_$val");
	if ($fn) return $fn();

	if (!loginza_check(true)) return;
	
	$ini		= getCacheValue('ini');
	$thisURL	= trim($ini[':loginza']['URL'], '/').getURL('loginza_login');
?>
<script src="http://loginza.ru/js/widget.js" type="text/javascript"></script>
<a href="http://loginza.ru/api/widget?token_url=<?= urlencode($thisURL)?>" class="loginza">
<img src="http://loginza.ru/img/sign_in_button_gray.gif" alt="Войти через loginza"/>
</a>
<? } ?>

<? function loginza_page()
{
	if (!loginza_check(true)) return;
	$ini		= getCacheValue('ini');
	$thisURL	= trim($ini[':loginza']['URL'], '/').getURL('loginza_login');
?>
<script src="http://loginza.ru/js/widget.js" type="text/javascript"></script>
<iframe src="http://loginza.ru/api/widget?overlay=loginza&token_url=<?= urlencode($thisURL)?>" 
style="width:359px;height:300px;" scrolling="no" frameborder="no"></iframe>
<? } ?>

<? function loginza_check($fullCheck = true){
	if (!function_exists('curl_init') || curl_init() == NULL) return false;
	if (!$fullCheck) return true;

	$ini	= getCacheValue('ini');
	@$val	= $ini[':loginza']['URL'];
	if (!$val) return false;
	@$val	= $ini[':loginza']['id'];
	if (!$val) return false;
	@$val	= $ini[':loginza']['key'];
	if (!$val) return false;

	return true;
}?>

<? function loginza_login()
{
	if (userID()) redirect(getURL('login'));
	if (!loginza_check(true))
		return module('message:error', 'Не найден модуль CURL или он работает некорректно!');
	
	$curl	= curl_init();
	$ini	= getCacheValue('ini');
	
	@$id	= $ini[':loginza']['id']; 	// здесь пишем ID сайта, выданный Loginza
	@$key	= $ini[':loginza']['key'];	// здесь пишем ключ, выданный Loginza
	$token	= getValue('token');	// получаем Token из запроса
	$sig	= md5($token.$key);		// цифровая подпись запроса

	curl_setopt($curl, CURLOPT_URL, "http://loginza.ru/api/authinfo?token=$token&id=$id&sig=$sig");
	curl_setopt($curl, CURLOPT_RETURNTRANSFER,true);
	curl_setopt($curl, CURLOPT_POST, false);
	$json_data	= curl_exec($curl);
	curl_close($curl);
	$user_data	= json_decode($json_data,true);
//	echo '<pre>'; print_r($user_data); echo '</pre>'; die;

	if (!$user_data || isset($user_data['error_type'])) {
		@$error = $user_data['error_type'];
		if ($error) module('message:error', $error);
		else module('message:error', 'Ошибка авторизации');
		return module('display:message');
	}
	
	$openIDidentity = $user_data['identity'];
	$dbUser	= module('user');
	
	$login	= array();
	$rnd	=	md5($openIDidentity);
	$login['login']	= "loginza_$rnd";
	$login['passw']	= $openIDidentity;
	
	$id	= module('user:enter', $login);
	if (!$id){
		module('display:!error', '');
		$id		= module('user:update::register', $login);
		if (!$id) return module('display:message');
		module('user:enter', $login);
	}
	
/*
    [identity] => https://www.google.com/accounts/o8/id?id=AItOawlCt7q4y0fLXvx7HDV_a52nqbUzN1X-pqU
    [provider] => https://www.google.com/accounts/o8/ud
    [name] => Array
        (
            [first_name] => xxxx
            [last_name] => xxxx
        )

    [language] => ru
    [address] => Array
        (
            [home] => Array
                (
                    [country] => RU
                )

        )

    [email] => xxxx
*/
	$d		= array();
	$fields	= array();
	$person	= array();
	
	//	Loginza data
	$fields['loginType']	= 'loginza';
	$fields['loginza']		= $user_data;
	
	//	Person data
	$person['name']			= $user_data['name'];
	$person['address']		= $user_data['address'];
	$person['email']		= $user_data['email'];
	$person['language']		= $user_data['language'];
	
	//	Commit data
	$fields['person']		= $person;
	//	Commit data
	$d['fields']	= $fields;
	module("user:update:$id:edit", $d);
//	module('display:message'); die;
	
	redirect(getURL('login'));
} ?>