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

	if ($id = $data[':sortable'])
	{
		module('script:draggable');
		$menu[]	= adminEditBuildMenuEntry('C', array(
			'class'		=> 'admin_sort_handle',
			'title'		=> 'Сортировка элементов, нажмите и переместите элемент на нужную позицию.',
			'sort_index'=> $id
		));
	}

	if (!$menu) return;
	
	switch($data[':type']){
	case 'bottom':
		$class[] = 'adminBottom';
		break;
	case 'left':
		$class[] = 'adminLeft';
		break;
	}
	
	if ($data[':class']){
		$class[] = is_array($data[':class'])?implode(' ', $data[':class']):$data[':class'];
	}
	$class	= implode(' ', $class);
	
	$style	= array();
	$styles	= $data[':style'];
	if (!is_array($styles)) $styles = array();
	foreach($styles as $name=>$value){
		$style[]	= "$name: $value";
	}
	$style	= implode('; ', $style);
	if ($style) $style = "style=\"$style\"";
?>
<link rel="stylesheet" type="text/css" href="css/adminEdit.css">
<div class="{$class}" {!$style} id="adminEditArea">
    <a style="display:none"></a>
    <div class="adminEditMenu" id="adminEditMenu" >
<? foreach($menu as $name => $tag){ echo $tag; } ?>
    </div>
<?= $data[':before'] ?>
<?= $layout ?>
<?= $data[':after'] ?>
</div>
<? } ?>

<? function adminEditBuildMenu(&$menu, $data)
{
	$max	= (int)$data[':maxMenu'];
	foreach($data as $name => $url)
	{
		if ($name[0] != ':') continue;
		unset($data[$name]);
	}
	if ($max <= 0 || count($data) < $max){
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