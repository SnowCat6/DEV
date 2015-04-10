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
	return module("text:note:$nLen", $val);
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
function makeProperty($property)
{
	if (!$property) return '';
	if (!is_array($property)) return $property;

	foreach($property as $name => &$val)
	{
		if ($val && $name[0] != ':'){
			if (is_array($val)) $val = makeProperty($val);
			else if (is_string($name)) $val = "$name=\"" . htmlspecialchars($val) . '"';
		}else{
			unset($property[$name]);
		}
	}
	return implode(' ', $property);
}
function dbSeek(&$db, $maxRows, $query = array(), $maxEntry = 0)
{
	ob_start();
	$seek		= seek($db->rows(), $maxRows, $query, $maxEntry);
	$db->max	= $maxRows;
	$db->seek($seek);
	return ob_get_clean();
}
function seek($rows, $maxRows, $query, $maxEntry = 20)
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
	if (!$maxEntry)	$maxEntry	= getCacheValue('seekMaxEntry');
	if (!$maxEntry) $maxEntry	= 20;
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
//	Запустить модули, если имеются в тексте
//	Специальная функция для динамического отображения пользовательских вызовов
function show($val)
{
	echo showEx($val);
}
function showEx($val)
{
	event('document.compile', $val);
	//	{\{moduleName=values}\}
	//	Специальная версия для статических страниц
	$val	= preg_replace_callback('#{{([^}]+)}}#u', 'parsePageModuleFn', $val);
	return $val;
}
function parsePageModuleFn($matches)
{
	//	module						=> module("name")
	//	module=name:val;name2:val2	=> module("name", array($name=>$val));
	//	module=val;val2				=> module("name", array($val));
	$baseCode	= $matches[1];
	@list($moduleName, $moduleData) = explode('=', $baseCode, 2);
	//	name:val;nam2:val
	$module_data= array();
	$d			= explode(';', $moduleData);
	foreach($d as $row)
	{
		//	val					=> [] = val
		//	name:val			=> [name] = val
		//	name.name.name:val	=> [name][name][name] = val;
		$name = NULL; $val = NULL;
		list($name, $val) = explode(':', $row, 2);
		if (!$name) continue;
		
		if ($val){
			$d2		= &$module_data;
			$name	= explode('.', $name);
			foreach($name as $n) @$d2 = &$d2[$n];
			$d2	= $val;
		}else{
			$module_data[] = $name;
		}
	}
	
	return mEx($moduleName, $module_data);
}

?>