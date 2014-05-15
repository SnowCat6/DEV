<?
function prop_read($db, $fn, &$data)
{
	
	list($fn, $val) = explode(':', $fn, 2);
	$fn = getFn("prop_read_$fn");
	if ($fn) return $fn($val, $data);

	$props = module("prop:getEx:$data[id]:$data[group]");
	if (!$props) return;

	$split = '<ul>';
	foreach($props as $name => $data)
	{
		if ($name[0] == ':' || $name[0] == '!') continue;
		if (!$data['visible']) continue;

		echo $split; $split = '';
		$note	= htmlspecialchars($data['note']);
		$name	= htmlspecialchars($name);
		$prop	= htmlspecialchars($data['property']);
		
		if ($prop){
			$prop	= propFormat($prop, $data, true);
			echo "<li title=\"$note\">$name: <b>$prop</b></li>";
		}else{
			echo "<li title=\"$note\">$name</li>";
		}
	}
	if (!$split) echo '</ul>';
}

function prop_read_plain(&$val, &$data)
{
	$group	=$data['group'];
	if (!$group) $group = 'globalSearch,globalSearch2,productSearch,productSearch2';
	$props	= module("prop:getEx:$data[id]:$group");
	if (!$props) return;

	$split = '';
	foreach($props as $name => $data){
		if ($name[0] == ':' || $name[0] == '!') continue;
		if (!$data['visible']) continue;

		$prop	= htmlspecialchars($data['property']);
		if (!$prop) continue;
		$prop	= propFormat($prop, $data, true);
		echo $split, $prop;
		$split = ', ';
	}
}

function prop_read_table($cols, &$data)
{
	$props = module("prop:getEx:$data[id]:$data[group]");
	if (!$props) return;

	$cols = (int)$cols;
	if ($cols < 1) $cols = 1;
	
	$p = array();
	$ix= 0;
	foreach($props as $name => &$data){
		if ($name[0] == ':' || $name[0] == '!') continue;
		if (!$data['visible']) continue;
		$p[$ix%$cols][] = $data;
		++$ix;
	}
	$width	= floor(100/$cols);
	$rows	= count($p[0]);
?>
<table border="0" cellspacing="0" cellpadding="0" class="read property">
<? for($row = 0; $row < $rows; ++$row){
	$class = $row%2?' class="alt"':'';
?>
<tr<?= $class?>>
<? for($col = 0; $col < $cols; ++$col){
	$now	= $p[$col][$row];
	$class	= $col?'':' id="first"';
?>
<? if ($col){ ?>
    <td class="split">&nbsp;</td>
<? } ?>
    <th {!$class}>{$now[name]}</th>
    <td {!$class}>{$now[property]}</td>
<? } ?>
</tr>
<? } ?>
</table>
<? } ?>
<? function prop_read_count(&$propNames, &$data)
{
	$cols	= (int)$data['cols'];
	if ($cols < 2) $cols = 1;
	
	if (!beginCache("prop:readCount:$cols:$propNames")) return;
	$count	= module("prop:count:$propNames", $data);
	if (!$count) return endCache();
	
	$ix		= 0;
	$names	= array();
	foreach($count as $propName => $counts){
		foreach($counts as $name => $c){
			$names[floor($ix / $cols)][]	= array($propName, $name, $c);
			++$ix;
		}
	}
?>
<? foreach($names as &$col){ ?>
<ul>
<? foreach($col as $name => &$val){
	list($propName, $name, $count) = $val;
	$url	= getURL('search_product', 'search[prop]['.urlencode($propName).']='.urlencode($name));
?>
<li><a href="{!$url}"><span>{$name}</span> <sup>{$count}</sup></a></li>
<? } ?>
</ul>
<? } ?>
<? endCache(); } ?>