<?
function user_register(&$db, $val, &$data)
{
	if (!access('register', '')) return;
	if (!is_array($data)) return module('message:error', 'Не верный формат данных');

	$iid = moduleEx("user:update::register", $data);
	if ($iid) module('user:enter', $data);
	return $iid;
}
?>