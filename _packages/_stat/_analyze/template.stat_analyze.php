<?
function stat_analyze($db, &$data)
{
	if (!hasAccessRole('admin,developer,SEO,writer')) return;

	m('page:title', 'Статистика за последний месяц');

	$max		= 30;
	$search		= array();

	$date1		= time()-60*60*24*$max;
	$date2		= time();
	$search['date']	= "$date1-$date2";
	
	$sql	= implode(' AND ', stat2sql($search));
	
	$actionStat	= array();
	$actionTree	= array();
	$pointer	= &$actionStat;
	$pass		= array();
	
	$prevIP		= '';
	$prevTime	= 0;
	$seq		= 0;
	$seek		= 0;
	$prevURL	= '';
	
//	$db	= new dbRow('stat_tbl');
	$db->open($sql, 100000);
	$db->sort = 'userIP, date ASC';
	while($data = $db->next())
	{
		$ip		= $data['userIP'];
		$time	= $data['date'];
		if ($ip != $prevIP){
			$prevIP 	= $ip;
			$prevTime	= 0;
			$seek		= 0;
			$pointer	= &$actionStat;
			$pass		= array();
			$seq++;
		}else
		if ($prevTime && $time - $prevTime > 60*60){
			$seek		= 0;
			$pointer	= &$actionStat;
			$pass		= array();
			$seq++;
		}

		$prevTime	= $time;
		
		$url		= $data['url'];
		$url		= preg_replace('#^(.*://)#',	'', $url);
		$url		= preg_replace('#^([^/]*/)#',	'', $url);
		$url		= preg_replace('#(\?.*)#',		'', $url);
		$url		= strtolower($url);
		$url		= "/$url";

		if (!preg_match('#(\.htm)|(/)$#', $url)) continue;
		if ($prevURL == $url) continue;
		if ($pass[$url]) continue;
		$pass[$url]	= true;
		
		$prevURL	= $url;
		
		$pointer	= &$pointer['url'][$url];
		$pointer['count']++;

		++$seek;
	}
	echo '<pre>';
	showStatPaths($actionStat['url'], 0);
	echo '</pre>';
}
function showStatPaths(&$actionStat, $deep)
{
	if (!$actionStat) return;
	
	$stat	= array();
	foreach($actionStat as $url => &$val)
	{
		$count	= $val['count'];
		if ($count < 5) continue;
		$stat[$url]	= $count;
	}
	arsort($stat);
	
	$ix		= 0;
	$prefix	= str_repeat('&nbsp;', $deep*4);
	foreach($stat as $url => $count)
	{
		$u		= getURL('') . trim($url, '/');
		$name	= "<a href=\"$u\" target=\"_blank\">$url ($count)</a>\r\n";
		if ($deep){
			echo $prefix, $name;
		}else{
			if ($ix) echo $prefix, "<hr />", $name;
			else echo $prefix, $name;
		}

		if ($deep < 10) showStatPaths($actionStat[$url]['url'], $deep + 1);
		if ($ix++ > 5) break;
	}
}
?>