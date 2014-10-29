<?
function startDrop($search, $template = '', $bSortable = false, $accept = NULL)
{
	if (!$search || testValue('ajax')) return;

	setNoCache();
	module('script:draggable');

	$rel	= array();
	
	if ($search[':sortable'])
	{
		$rel['sort_data']	= $search[':sortable'];
		unset($search[':sortable']);
	}
	$rel['drop_data']	= array(
		'template'	=> $template,
		'drop_data'	=> $search,
		'drop_type'	=> array_values($accept)
	);
	
	$rel	= htmlspecialchars(json_encode($rel));

	$class	= $bSortable?'sortable':'';
	echo "<div class=\"admin_droppable $class\" rel=\"$rel\">";
}
function endDrop($search)
{
	if (!$search || testValue('ajax')) return;
	setNoCache();
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