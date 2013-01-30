<? function backup_all(&$db, $val, &$data){ ?>
<h1>Резервные копии</h1>
<?
	$backupFolder	= localHostPath.'/_backup';
	$folders		= getDirs($backupFolder);
	if (!$folders){
		module('message:error', 'Резервные копии не найдены');
		return module('display:message');
	}
	
	module('script:popupWindow');

	$folders = array_reverse($folders);
	foreach($folders as $name => $path){
		$name	= htmlspecialchars($name);
		$url	= getURL("backup_$name");
		$time	= date('d.m.Y H:i:s', filemtime($path));
		echo "<div><b><a href=\"$url\" id=\"ajax\">$name</a></b> <i>$time</i></div>";
		@$note	= file_get_contents("$path/note.txt");
		echo "<blockquote><pre>$note</pre></blockquote>";
	}
?>
<? } ?>
