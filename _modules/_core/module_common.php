<?
function removeEmpty(&$src){
	if (!is_array($src)) return;
	foreach($src as $name => &$val){
		removeEmpty($val);
		if (!$val) unset($src[$name]);
	}
}
//	Объединение массовов
//	data[name]...	- Заменяет конечный элемент
//	data[+name]....	- Добавляет конечный элемент
//	data[-name]...	- Удаляет из массива конечный элемент
function mergeEx(&$dst, &$src){
}
//	Объеденить массивы
function dataMerge(&$dst, $src, $bOverride = false)
{
	if (!is_array($src)) return;
	foreach($src as $name => &$val)
	{
		if (is_array($val)){
			if (isset($dst[$name])) dataMerge($dst[$name], $val);
			else $dst[$name] = $val;
		}else{
			if ($bOverride) $dst[$name] = $val;
			else
			if (!isset($dst[$name])) $dst[$name] = $val;
		}
	}
}

function makeNote($val, $nLen = 200)
{
	$nLen	= (int)$nLen;
	$val	= strip_tags($val);
	$val	= preg_replace('#(\s+)#', ' ', $val);
	$val	= trim($val);
	if (!function_exists('mb_strrpos')){
		if (strlen($val) <= $nLen) return $val;
		while(ord($val[$nLen]) >= 0x80) ++$nLen;
		$val	= substr($val, 0, $nLen);
		return $val .' ....';
	}
	
	$minLen	= $nLen - $nLen / 3;
	$val	= mb_substr($val, 0, $nLen, 'UTF-8');
	if (is_int($nPos = mb_strrpos($val, '.')) && $nPos > $minLen)		$val = mb_substr($val, 0, $nPos+1, 'UTF-8');
	else if (is_int($nPos = mb_strrpos($val, '!')) && $nPos > $minLen)	$val = mb_substr($val, 0, $nPos+1, 'UTF-8');
	else if (is_int($nPos = mb_strrpos($val, '?')) && $nPos > $minLen)	$val = mb_substr($val, 0, $nPos+1, 'UTF-8');
	$val .= ' ..';
	return $val;
}

function makeQueryString($data, $name = '', $bNameEncode = true)
{
	if ($bNameEncode) $name = urlencode($name);
	if (!is_array($data)) return $name?"$name=$data":$data;

	$v = '';
	foreach($data as $n => &$val)
	{
		if ($v) $v .= '&';
		$n = urlencode($n);
		
		if (is_array($val)){
			$v .= makeQueryString($val, $name?$name."[$n]":$n, false);
		}else{
			if (!preg_match('#^\d+$#', $n)){
				$val = urlencode($val);
				$v  .= $name?$name."[$n]=$val":"$n=$val";
			}else{
				$v  .= $name?$name."[]=$val":"$val";
			}
		}
	}
	return $v;
}
function makeFormInput($data, $name = '')
{
	if (!is_array($data)){
		$name	= htmlspecialchars($name);
		$data	= htmlspecialchars($data);
		return $name?"<input type=\"hidden\" name=\"$name\" value=\"$data\" /> ":'';
	}

	$v = '';
	foreach($data as $n => &$val)
	{
		$v .= makeFormInput($val, $name?$name."[$n]":$n, false);
	}
	return $v;
}
function dbSeek(&$db, $maxRows, $query = array())
{
	ob_start();
	$seek		= seek($db->rows(), $maxRows, $query);
	$db->max	= $maxRows;
	$db->seek($seek);
	return ob_get_clean();
}
function seek($rows, $maxRows, $query)
{
	removeEmpty($query);
	if (isset($query['search']['url'])) $query = $query['search']['url'];
	
	$pages		= ceil($rows / $maxRows);
	if ($pages < 2) return 0;
	//	Страницы номеруються с 1 по ???
	$thisPage	= min(getValue('page'), $pages);
	$thisPage	= max(1, $thisPage);
	$seek		= $maxRows * ($thisPage - 1);
//	echo "rows: $rows, max: $maxRows, pages: $pages, page: $thisPage, seek: $seek";
	
	$seekEntry	= array();
	$minEntry	= 0;
	$maxEntry	= 20;
	//	Кнопка предыдущая
	if ($thisPage != 1){
		$seekEntry[$minEntry++] = seekLink('&lt;', $thisPage - 1, $query);
	}
	//	Кнопка следующая
	if ($thisPage < $pages){
		$seekEntry[$maxEntry--] = seekLink('&gt;', $thisPage + 1, $query);
	}

	$seekCount	= $maxEntry - $minEntry;
	if ($thisPage - $seekCount/2 < 1){
		for($ix = 0; $ix < $seekCount; ++$ix){
			if ($ix < $pages) $seekEntry[$minEntry + $ix] = seekLink($ix + 1, $ix + 1, $query, $thisPage);
		}
	}else
	if ($thisPage + $seekCount/2 > $pages){
		for($ix = 0; $ix < $seekCount; ++$ix){
			if ($pages - $ix < 1) break;
			$seekEntry[$maxEntry - $ix] = seekLink($pages - $ix, $pages - $ix, $query, $thisPage);
		}
	}else{
		for($ix = 0; $ix < $seekCount; ++$ix){
			$p = floor($thisPage - $seekCount / 2);
			$seekEntry[$minEntry + $ix] = seekLink($p + $ix, $p + $ix, $query, $thisPage);
		}
	}
	ksort($seekEntry);
	
	echo '<div class="seek">';
	echo implode(' ', $seekEntry);
	echo '</div>';
	
	return $seek;
}
function seekLink($title, $page, $query, $thisPage = NULL)
{
	$class = $page == $thisPage?' class="current"':'';
	$query['page'] = $page;
	if ($url = $query[':url']){
		unset($query[':url']);
	}else{
		$url= globalRootURL.getRequestURL();
	}
	$q	= makeQueryString($query);
	
	if ($title == $page){
		$v = "<a href=\"$url?$q\"$class>$title</a>";
	}else{
		$id	= $title == '&lt;'?'nav':'nav2';
		$v	= "<a href=\"$url?$q\"id=\"$id\"$class>$title</a>";
	}
	return $v;
}

?>