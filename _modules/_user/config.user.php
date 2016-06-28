<?
include_once ("_class/class.user.php");

addEvent('site.enter',	'user:enterSite');

addUrl('login',			'user:loginPage');
addUrl('user_login',	'user:loginPage');
addUrl('user_register',	'user:registerPage');
addUrl('user_lost',		'user:lostPage');
addUrl('user', 			'user:settingsPage');
addUrl('user([\d]+)',	'user:editPage');

addAccess('user:(\d+)',	'user_adminAccess');
addAccess('.*',			'user_access');
//	Права доступа к файлам
addAccess('file:.+/user/(\d+|new\d+)/(File|Gallery|Image|Title).*',	'user_file_access');
//	Сохранение настроек
addEvent('storage.get',	'user:storage:get');
addEvent('storage.set',	'user:storage:set');
//	Административные ссылки
addUrl('user_all',			'user:all');
addUrl('user_add',			'user:add');
addUrl('user_edit_(\d+)',	'user:edit');

//	Инстументы для административной панели
addEvent('admin.tools.settings2',	'user:tools2');
addUrl('admin_edit_mode',			'user:tools2:set');

//	Администратор сайта, божественная сущность
addRole('Администратор сайта',	'admin');
//	Судья и надзиратель, но сам практически бесправен
addRole('SEO',					'SEO');
//	Судья и надзиратель, но сам практически бесправен
addRole('Администратор пользователей',		'accountManager');
//	Разработчик, тоже божество, даже более сильное
addRole('Программист',			'developer');
//	Редактор сайта, великий, но не всемогущий
addRole('Редактор сайта',		'writer');
//	Достаточно крут, в своем пруду
addRole('Контент менеджер',		'manager');
//	Зарегистрированный пользователь
addRole('Пользователь',			'user');

addEvent('config.end',	'user_config');
function module_user_config($val, $data)
{
	$users_tbl = array();
	$users_tbl['user_id']= array('Type'=>'int(6) unsigned', 'Null'=>'NO', 'Key'=>'PRI', 'Default'=>'', 'Extra'=>'auto_increment');
	$users_tbl['name']= array('Type'=>'varchar(255)', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$users_tbl['login']= array('Type'=>'varchar(255)', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$users_tbl['md5']= array('Type'=>'char(32)', 'Null'=>'YES', 'Key'=>'UNI', 'Default'=>'', 'Extra'=>'');
	$users_tbl['access']= array('Type'=>'varchar(255)', 'Null'=>'NO', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$users_tbl['fields']= array('Type'=>'array', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
//	$users_tbl['openIDidentity']= array('Type'=>'text', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$users_tbl['dateCreate']= array('Type'=>'datetime', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$users_tbl['lastUpdate']= array('Type'=>'datetime', 'Null'=>'YES', 'Key'=>'', 'Default'=>'', 'Extra'=>'');
	$users_tbl['deleted']= array('Type'=>'tinyint(8) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'0', 'Extra'=>'');
	$users_tbl['visible']= array('Type'=>'tinyint(8) unsigned', 'Null'=>'NO', 'Key'=>'', 'Default'=>'1', 'Extra'=>'');
	dbAlter::alterTable('users_tbl', $users_tbl);
	
	if (defined('restoreProcess') || defined('STDIN')) return;

	$db		= user::find(array('login' => 'admin'));
	if ($db->next()) return;

	$data			= array();
	$data['login']	='admin';
	$data['md5']	= user::loginKey('admin', '');
	$data['access']	= 'admin,developer,backup';
	$db->update($data);
}
?>