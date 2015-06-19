<?
addEvent('site.admin',	'callbackAdv');
addUrl('callbackAdv',	'callbackAdvForm');

$def	= array(
	'timeout1' => 45,
	'timeout2' => 60*2,
	'timeout3' => 60*2,
	
	'bkColor'	=> '#001B47',
	'txColor'	=> '#fff'
);
setCacheValue(":callbackAdv", $def);
?>