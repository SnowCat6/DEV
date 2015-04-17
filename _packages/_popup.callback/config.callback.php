<?
addEvent('site.admin',	'callbackAdv');
addUrl('callbackAdv',	'callbackAdvForm');

$def	= array(
	'timeout1' => 15,
	'timeout2' => 50,
	'timeout3' => 60
);
setCacheValue(":callbackAdv", $def);
?>