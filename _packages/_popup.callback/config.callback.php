<?
addEvent('site.admin',	'callbackAdv');
addUrl('callbackAdv',	'callbackAdvForm');

$def	= array(
	'timeout1' => 15,
	'timeout2' => 50,
	'timeout3' => 60,
	
	'bkColor'	=> '#001B47',
	'txColor'	=> '#fff'
);
setCacheValue(":callbackAdv", $def);
?>