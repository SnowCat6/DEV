<?
function admin_edit($val, &$data)
{
	setNoCache();
	module('script:ajaxLink');
	define('noCache', true);
	
	$layout	= $data[':layout'];
	$class	= array('adminEditArea');
	$menu	= array();
	
	if ($dragID = $data[':draggable']){
		module('script:draggable');
		$menu[]	= "<span $dragID class=\"ui-icon ui-icon-arrow-4-diag\" title=\"Петеащите элемент в нужный слот\"></span>";
	}

	$inline	= $data[':inline'];
	$action	= $inline['action'];
	if ($action)
	{
		$inline['layout']	= $layout;
		$layout	= m("editor:inline", $inline);
		$menu[]	= '<a id="inlineEditor" href="#">inline</a>';
	}
	
	if ($data[':sortable'])
	{
		module('script:draggable');
		$data['С']	= array(
			'class'	=> 'admin_sort_handle',
			'title'	=> 'Сортировка элементов, нажмите и переместите элемент на нужную позицию.'
		);
	}

	if (!$data[':useTopMenu'])	$class[] = 'adminBottom';
	if ($data[':class'])		$class[] = $data[':class'];

	foreach($data as $name => $url)
	{
		if ($name[0] == ':') continue;

		$attr	= array();
		list($name, $iid) = explode('#', $name);
		if ($iid) $attr['id'] = $iid;
		
		if (is_array($url)){
			foreach($url as $attrName => $val){
				$attr[$attrName]	= $val;
			}
		}else{
			$attr['href']	= $url;
		}
		foreach($attr as $attrName => &$val){
			$val = $attrName . '="' . htmlspecialchars($val) . '"';
		}
		$attr	= implode(' ', $attr);
		$n		= htmlspecialchars($name);
		$menu[$name]= "<a $attr>$n</a>";
	}
	
	$class	= implode(' ', $class);
?>
<link rel="stylesheet" type="text/css" href="css/admin.css"/>
<div class="{$class}">

<div class="adminEditMenu">
<? foreach($menu as $name => $tag){ ?>{!$tag}<? } ?>
</div>

<?= $layout ?>
</div>
<? } ?>
