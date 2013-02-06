<?
function module_test()
{
	if (!testValue('test')) return;
	
	$prop	= array('Процессор', 'Тип экрана', 'Операционная система', 'Ьатарея');
	$val	= array('Android', 'IPS', 'HDMI', 'GSM');
	
	for($ix = 0; $ix < 200; ++$ix)
	{
		$p = array();
		$p[$prop[rand(0, count($prop))]] = $val[rand(0, count($val))];
		$p[$prop[rand(0, count($prop))]] = $val[rand(0, count($val))];
		$p[$prop[rand(0, count($prop))]] = $val[rand(0, count($val))];
		$p[$prop[rand(0, count($prop))]] = $val[rand(0, count($val))];
		$p[$prop[rand(0, count($prop))]] = $val[rand(0, count($val))];
		$p[$prop[rand(0, count($prop))]] = $val[rand(0, count($val))];
		
		$d = array();
		$d['title']		= generateRandomString(rand(5, 15));
		$d[':property']	= $p;
		if (module("doc:update:43:add:product", $d)) continue;
//		module('display:message');
	}
}

function generateRandomString($length = 10) {
    $characters = 'abcde';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}
?>