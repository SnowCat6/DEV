<?
function doc_read_menuLink_beginCache(&$db, $val, &$search){
	$search['currentPage']	= currentPage();
	return	 hashData($search);
}

function doc_read_menuLink(&$db, $val, &$search)
{
	$split = ' id="first"';
	$options		= $search['options'];
	$classContainter= $options['class'];
	$classEntry 	= $options['classEntry'];
	$classLink 		= $options['classLink'];
	$classActive 	= $options['classActive'];
	if (!$classActive) $classActive = 'current';

	while($data = $db->next())
	{
		$id		= $db->id();
		$url	= getURL($db->url());
		$class	= currentPage() == $id?'current':'';
		@$fields	= $data['fields'];

		$draggable	= docDraggableID($id, $data);

		$class	= array();
		if ($fields['class']) $class[] = $c;
		if ($classEntry)$class[] = $classEntry;
		if ($classLink)	$class[] = $classLink;
		if ($class) $class = " class=\"" . implode(' ', $class) . "\"";
		else $class = '';
?>
<a href="{$url}" {!$draggable} {!$split} title="{$data[title]}"{!$class}>{$data[title]}</a>
<? $split = ''; } ?>
<? return $search; } ?>