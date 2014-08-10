<?
function import_cron($val, &$data)
{
	$locks	= array();
	event('import.source', $locks);

	foreach($locks as $name => &$synch){
		if ($s->lockTimeout()) return;
	}
	
	foreach($locks as $name => &$synch)
	{
		$status		= $synch->getValue('status');
		if ($status == 'complete') continue;
		if ($synch->lockTimeout()) break;
		
		$s2	= array($name => $name);
		event('import.synch', $s2);
	}
}
?>