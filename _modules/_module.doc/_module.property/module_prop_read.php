<?
function prop_read($db, $fn, $data)
{
	$props = module("prop:get:$data[id]:$data[group]");
	if (!$props) return;
	
	list($fn, $val) = explode(':', $fn, 2);
	$fn = getFn("prop_read_$fn");
	if ($fn) return $fn($props, $val);

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

function prop_read_plain(&$props)
{
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

function prop_read_table(&$props, $cols)
{
	$cols = (int)$cols;
	if ($cols < 1) $cols = 1;
	
	$p = array();
	foreach($props as $name => &$data){
		if ($name[0] == ':' || $name[0] == '!') continue;
		if (!$data['visible']) continue;
		$p[] = $data;
	}
	$width	= floor(100/$cols);
	$rows	= floor(count($props) / $cols);
?>
<table border="0" cellspacing="0" cellpadding="0" class="read property">
<? for($row = 0; $row <= $rows; ++$row){
	$class = $row%2?' class="alt"':'';
?>
<tr<?= $class?>>
<? for($col = 0; $col < $cols; ++$col){
	$now	= $p[($col*$rows)+$row];
	$class	= $col?'':' id="first"';
?>
<? if ($col){ ?>
    <td class="split">&nbsp;</td>
<? } ?>
    <th <?= $class?>><?= htmlspecialchars($now['name'])?></th>
    <td <?= $class?>><?= htmlspecialchars($now['property'])?></td>
<? } ?>
</tr>
<? } ?>
</table>
<? } ?>