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
		$menu['d&d']= "href=\"#\"$dragID title=\"Петеащите элемент в нужный слот\"";
	}

	$inline	= $data[':inline'];
	$action	= $inline['action'];
	if ($action)
	{
		$inline['layout']	= $layout;
		$layout			= m("editor:inline", $inline);
		$menu['inline']	= 'id="inlineEditor" href="#"';
	}
	
	if ($data[':sortable'])
	{
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
		$menu[$name]	= implode(' ', $attr);
	}
	
	$class	= implode(' ', $class);
?>
<link rel="stylesheet" type="text/css" href="css/admin.css"/>
<div class="{$class}">

<div class="adminEditMenu">
<? foreach($menu as $name => $attr){ ?>
<a {!$attr}>{$name}</a>
<? } ?>
</div>

<?= $layout ?>
</div>
<? } ?>
