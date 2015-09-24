<?
//	+function module_startDrop
function module_startDrop($val, $data)
{

	$rel	= array();
	$class	= array();
	
	if ($data[':sortable'])
	{
		$class[]			= 'admin_sortable';
		$rel['sort_data']	= $data[':sortable'];
		unset($data[':sortable']);
	}

	if ($data[':accept'])
	{
		$class[]			= 'admin_droppable';
		$rel['drop_data']	= $data;
	}
	
	if (!$class || !$rel)
		return stack::push('');
	
	setNoCache();
	stack::push('dropZone');
	module('script:draggable');

	$class	= implode(' ', $class);
	$rel	= htmlspecialchars(json_encode($rel));

	echo "<div class=\"$class\" rel=\"$rel\">";
}
//	+function module_endDrop
function module_endDrop()
{
	if (!stack::pop()) return;
	echo "</div>";
}
//	+function module_dragID
function module_dragID($val, $data)
{
	module('script:draggable');

	$data['drag_type']	= array_values($data['drag_type']);
	$rel	= array('drag_data'	=>$data);
	$rel	= htmlspecialchars(json_encode($rel));
	return " rel=\"$rel\"";
}
?>