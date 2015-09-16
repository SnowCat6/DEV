<?
function user_name($db, $val, $data)
{
	if (!$data) return;
	$person	= userPerson($data);
	@$name	= $person['name'];
	@$name	= $name['last_name'];
	if (!$name){
		$data	= userData($data);
		@$name	= $data['login'];
		$name	= "<$name>";
	}else{
		if ($val == 'full'){
			@$sName = $person['name'];
			@$sName = $sName['first_name'];
			if ($sName) $name = "$name $sName";
		}
	}
	echo htmlspecialchars($name);
}

function userData($data = NULL)
{
	if ($data) return $data;
	
	$user	= config::get(':USER');
	return $user['data'];
}
function userFields($data = NULL){
	$data	= userData($data);
	@$data	= $data['fields'];
	return $data;
}
function userPerson($data = NULL){
	$data	= userFields($data);
	@$data	= $data['person'];
	return $data;
}
function userLang($data = NULL){
	$data	= userPerson($data);
	@$data	= $data['language'];
	if (!$data) $data = 'ru';
	return $data;
}
?>