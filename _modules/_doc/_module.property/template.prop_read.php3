<?
function prop_read($db, $fn, &$data)
{
	
	list($fn, $val) = explode(':', $fn, 2);
	$fn = getFn("prop_read_$fn");
	if ($fn) return $fn($val, $data);

	$props = module("prop:getEx:$data[id]:$data[group]");
	if (!$props) return;
	$props	= prop_order($data['id'], $props);

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
function prop_order($docID, &$props)
{
	$db		= module('doc');
	$p		= module("prop:get:$docID");
	
	$parents= explode(', ', $p[':parent']);
	foreach($parents as $parent)
	{
		$parent	= (int)$parent;
		$data	= $db->openID($parent);
		$fields	= $data['fields'];
		$any	= $fields['any'];
		$order	= $any['orderProps'];
		if ($order) break;
	}
	if (!is_array($order)) return $props;
	
	$ret	= array();
	foreach($order as $name => &$val){
		if (!isset($props[$name])) continue;
		$ret[$name]	= $props[$name];
	}
	foreach($props as $name => &$val){
		if (isset($order[$name])) continue;
		$ret[$name]	= $val;
	}
	return $ret;
}
function prop_read_plain(&$val, &$data)
{
	$group	=$data['group'];
	if (!$group) $group = 'globalSearch,globalSearch2,productSearch,productSearch2';
	$props	= module("prop:getEx:$data[id]:$group");
	if (!$props) return;
	$props	= prop_order($data['id'], $props);

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
	$props	= module("prop:getEx:$data[id]:$data[group]");
	if (!$props) return;
	$props	= prop_order($data['id'], $props);

	$cols = (int)$cols;
	if ($cols < 1) $cols = 1;
	
	$ix	= 1;
	$c	= count($props);
	$p	= array();
	foreach($props as $name => &$data){
		if ($name[0] == ':' || $name[0] == '!') continue;
		if (!$data['visible']) continue;
		$p[floor($ix*$cols/$c)][] = $data;
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
    <td {!$class}><table><tr>
        <th>{$now[name]}</th>
        <td>{$now[property]}</td>
    </tr></table></td>
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