<?
function admin_edit($val, &$data)
{
//	$userID		= userID();
//	$bHasMenu	= getStorage(":menu", "user$userID");
	
	setNoCache();
	
	$menu	= array();
	$class	= array('adminEditArea');
	
	if ($dragID = $data[':draggable']){
		module('script:draggable');
		$menu[]	= "<span $dragID class=\"ui-icon ui-icon-arrow-4-diag\" title=\"Петеащите элемент в нужный слот\"></span>";
	}

	$layout	= $data[':layout'];
	$inline	= $data[':inline'];
	$action	= $inline['action'];
	if ($action)
	{
		$inline['layout']	= $layout;
		$layout	= m("editor:inline", $inline);
		
		$menu[]	= adminEditBuildMenuEntry(0, 'inline', array(
			'href'	=> '#',
			'id'	=> 'inlineEditor',
			'title'	=> 'Нажмите для редактирования контента на странице.'
		));
	}
	
	adminEditBuildMenu($menu, $data);

	if ($id = $data[':sortable'])
	{
		module('script:draggable');
		$menu[]	= adminEditBuildMenuEntry(0, 'C', array(
			'class'		=> 'admin_sort_handle',
			'title'		=> 'Сортировка элементов, нажмите и переместите элемент на нужную позицию.',
			'sort_index'=> $id
		));
	}

	switch($data[':type']){
	case 'bottom':
		$class[] = 'adminBottom';
		break;
	case 'left':
		$class[] = 'adminLeft';
		break;
	}
	$class[] = is_array($data[':class'])?implode(' ', $data[':class']):$data[':class'];
?>
<link rel="stylesheet" type="text/css" href="css/adminEdit.css">
<div class="{!$class|implode: }" id="adminEditArea" {!$data[:style]|style} {!$data[:attr]|property}>
    <a style="display:none"></a>
    <div class="adminEditMenu" id="adminEditMenu" >{!$menu|implode}</div>

{!$data[:before]}
{!$layout}
{!$data[:after]}

</div>
<? } ?>

<? function adminEditBuildMenu(&$menu, $data)
{
	$max	= (int)$data[':maxMenu'];
	if (!$max) $max = 2;
	
	foreach($data as $name => $url)
	{
		if (!$name) ++$max;
		if ($name[0] != ':') continue;
		unset($data[$name]);
	}

	if (count($data) < $max){
		foreach($data as $name => $url)
			$menu[] = adminEditBuildMenuEntry(0, $name, $url);
		return;
	}

	$menu2	= array();
	foreach($data as $name => $url)
		$menu2[] = adminEditBuildMenuEntry(count($menu2), $name, $url);

	$menu2	= implode('', $menu2);
	$menu[]	= "<a href='#'>Меню</a><span class=\"adminDropMenu\">$menu2</span>";
}
function adminEditBuildMenuEntry($ix, $name, $url)
{
	if (!$url)
		return $ix?'<hr />':'';
	
	$attr	= array();
	list($name, $iid) = explode('#', $name);
	if ($iid) $attr['id'] = $iid;
	
	if (is_array($url)){
		foreach($url as $attrName => $val) $attr[$attrName]	= $val;
	}else $attr['href']	= $url;

	$attr	= makeProperty($attr);
	$n		= htmlspecialchars($name);
	return "<a $attr>$n</a>";
}
?>