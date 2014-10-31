<?
function startDrop($search, $template = '', $bSortable = false, $accept = NULL)
{
	if (!$search || testValue('ajax'))
		return pushStackName('');

	pushStackName('dropZone');
	setNoCache();
	module('script:draggable');

	$rel	= array();
	$class	= array();
	
	if ($search[':sortable'])
	{
		$class[]			= 'admin_sortable';
		$rel['sort_data']	= $search[':sortable'];
		unset($search[':sortable']);
	}
	if ($bSortable){
		$class[]	= 'sortable';
	}
	
	$class[]	= 'admin_droppable';
	$rel['drop_data']	= array(
		'template'	=> $template,
		'drop_data'	=> $search,
		'drop_type'	=> array_values($accept)
	);
	
	$class	= implode(' ', $class);
	$rel	= htmlspecialchars(json_encode($rel));

	echo "<div class=\"$class\" rel=\"$rel\">";
}
function endDrop()
{
	if (!popStackName()) return;
	echo "</div>";
}
function dragID($data)
{
	module('script:draggable');

	$data['drag_type']	= array_values($data['drag_type']);
	$rel	= array('drag_data'	=>$data);
	$rel	= htmlspecialchars(json_encode($rel));
	return " rel=\"$rel\"";
}
?>