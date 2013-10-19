<?
function import_cron($val, &$data)
{
	$locks	= array();
	event('import.source', $locks);
	
	foreach($locks as $name => &$s){
		$status		= $s->getValue('status');
		if ($status == 'complete') continue;
		if ($s->lockTimeout()) break;

		$s2	= array($name => $name);
		event('import.synch', $s2);
	}
}
?>