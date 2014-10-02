<?
function startDrop($search, $template = '', $bSortable = false, $accept = NULL)
{
	if (!$search || testValue('ajax')) return;
	setNoCache();

	$class	= $bSortable?'sortable':'';
	$rel	= array(
		'drop_data'	=> array(
			'template'	=> $template,
			'drop_data'	=> $search,
			'drop_type'	=> $accept
		)
	);
	$rel	= htmlspecialchars(json_encode($rel));
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

	$rel	= array('drag_data'	=>$data);
	$rel	= htmlspecialchars(json_encode($rel));
	return " rel=\"$rel\"";
}
?>