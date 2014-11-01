<?
function admin_edit($val, &$data)
{
	setNoCache();
	module('script:ajaxLink');
	define('noCache', true);
	
	$class	= array('adminEditArea');
	$menu	= array();
	
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
		
		$menu[]	= adminEditBuildMenuEntry('inline', array(
			'href'	=> '#',
			'id'	=> 'inlineEditor',
			'title'	=> 'Нажмите для редактирования контента на странице.'
		));
	}
	
	adminEditBuildMenu($menu, $data);

	if ($data[':sortable'])
	{
		module('script:draggable');
		$menu[]	= adminEditBuildMenuEntry('C', array(
			'class'	=> 'admin_sort_handle',
			'title'	=> 'Сортировка элементов, нажмите и переместите элемент на нужную позицию.'
		));
	}

	if (!$data[':useTopMenu'])	$class[] = 'adminBottom';
	if ($data[':class'])		$class[] = $data[':class'];
	
	$class	= implode(' ', $class);
	
?>
<link rel="stylesheet" type="text/css" href="css/admin.css"/>
<div class="{$class}">
<a style="display:none"></a>
<div class="adminEditMenu">
<? foreach($menu as $name => $tag){ echo $tag; } ?>
</div>
<?= $layout ?>
</div>
<? } ?>

<? function adminEditBuildMenu(&$menu, $data)
{
	foreach($data as $name => $url)
	{
		if ($name[0] != ':') continue;
		unset($data[$name]);
	}
	if (count($data) < 3){
		foreach($data as $name => $url) $menu[] = adminEditBuildMenuEntry($name, $url);
		return;
	}
	$menu2	= array();
	foreach($data as $name => $url) $menu2[] = adminEditBuildMenuEntry($name, $url);
	$menu2	= implode('', $menu2);
	$menu[]	= "<a>Меню</a><span class=\"adminDropMenu\">$menu2</span>";
}
function adminEditBuildMenuEntry($name, $url)
{
	$attr	= array();
	list($name, $iid) = explode('#', $name);
	if ($iid) $attr['id'] = $iid;
	
	if (is_array($url)){
		foreach($url as $attrName => $val) $attr[$attrName]	= $val;
	}else $attr['href']	= $url;
	
	foreach($attr as $attrName => &$val){
		$val = $attrName . '="' . htmlspecialchars($val) . '"';
	}
	$attr	= implode(' ', $attr);
	$n		= htmlspecialchars($name);
	return "<a $attr>$n</a>";
}
?>