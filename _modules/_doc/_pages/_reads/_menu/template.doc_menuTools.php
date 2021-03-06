﻿<?
//	+function doc_read_menu
function doc_read_menu($db, $val, $search){
	$deep	= (int)$search[':deep'] - 1;
	return showDocMenuDeep($db, $search,  $deep > 0?$deep:0);
}
//	+function doc_read_menu_beginCache
function doc_read_menu_beginCache($db, $val, $search)	{
	$deep	= (int)$search[':deep'] - 1;
	return menuBeginCache($deep > 0?$deep:0, $search);
}
//	+function doc_read_menu2
function doc_read_menu2($db, $val, $search){
	return showDocMenuDeep($db, $search, 1);
}
//	+function doc_read_menu2_beginCache
function doc_read_menu2_beginCache($db, $val, $search){
	return menuBeginCache(2, $search);
}
//	+function doc_read_menu3
function doc_read_menu3($db, $val, $search){
	return showDocMenuDeep($db, $search, 2);
}
//	+function doc_read_menu3_beginCache
function doc_read_menu3_beginCache($db, $val, $search){
	return menuBeginCache(3, $search);
}

//	Common menu functions
function menuBeginCache($name, $search)
{
	if (userID()) return;
	$search['currentPage']	= currentPage();
	return	 $search;
}
function showDocMenuDeep($db, $search, $deep)
{
	$splitRange	= 0;
	if ($deep){
		$splitRange	= $search['!split'];

		$db2	= module('doc');
		$ids	= array();
		while($db->next()) $ids[] = $db->id();
		$tree	= module('doc:childs:' . $deep, array('parent' => $ids, 'type' => $search['type']));
		$d		= &$tree[':data'];
		$db->seek(0);
	}else{
		$tree	= array();
	}
	$parents	= getPageParents(currentPage());
	$parents	= array_flip($parents);
	
	$options		= $search['options'];
	$classContainter= $options['class'];
	$classEntry 	= $options['classEntry'];
	$classLink 		= $options['classLink'];
	$classActive 	= $options['classActive'];
	if (!$classActive) $classActive = 'current';
?>
<ul {!$classContainter|class}>
<?
$id	= 0;
$ixClass	= $search['indexClass'];

while($data = $db->next())
{
	$id		= $db->id();
	$url	= $db->url();
	$fields	= $data['fields'];
	$draggable	= docDraggableID($id, $data);

	$class	= array();
	if ($id == currentPage()) $class[]= $classActive;
	else if (isset($parents[$id])) $class[] = 'parent';

	if ($c	= $fields['class']) $class[] = $c;
	if ($ixClass) 	$class[]	= $ixClass . (int)$ix;
	if ($classEntry)$class[]	= $classEntry;
	if (($ix++ % $splitRange) == 0 && $splitRange) $class[] = 'altMenu';

	if ($db->ndx == 1) $ii .= ' id="first"';
	else $ii = '';
?>
	<li {!$class|class}{!$ii}>
        <a href="{{url:$url}}" {!$draggable} title="{$data[title]}"{!$classLink|class}>
            <span>{$data[title]}</span>
            {!$note}
        </a>
<?	showDocMenuDeepEx($db2, $tree[$id], $d, $search, 1, $id, $parents); ?>
	</li>
<? } ?>
</ul>
<?
	return $search;
}

function showDocMenuDeepEx($db2, &$tree, &$d, &$search, $deep, $parentID, $parents)
{
	if (!$tree) return;
	
	$bFirst		= true;
	$ix			= 0;
	$splitRange	= $search["!split$deep"];
	echo '<ul>';
	foreach($tree as $id => $childs)
	{
		$data	= $d[$id];
		$db2->setData($data);
		
		$url	= getURL($db2->url($id));
		$fields	= $data['fields'];
		$title	= htmlspecialchars($data['title']);
		$draggable	= docDraggableID($id, $data, array('drop_unset[parent]'=>$parentID));
		
		$class	= array();
		if ($id == currentPage()) $class[]= 'current';
		else if (isset($parents[$id])) $class[] = 'parent';

		if (@$c	= $fields['class']) $class[] = $c;
		if (($ix++ % $splitRange) == 0 && $splitRange) $class[] = 'altMenu';

		if ($class = implode(' ', $class)) $class = " class=\"$class\"";
		if ($bFirst) $class .= ' id="first"';
		$bFirst = false;

		echo "<li$class><a href=\"$url\" title=\"$title\"$draggable><span>$title</span></a>";
		showDocMenuDeepEx($db2, $childs, $d, $search, $deep+1, $id, $parents);
		echo "</li>";
	}
	echo '</ul>';
}?>