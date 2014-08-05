<?
function admin_edit($val, &$data)
{
	setNoCache();
	$layout	= $data[':layout'];
	$bTop	= $data[':useTopMenu'];
	$dragID	= $data[':draggable'];
	
	if ($dragID) module('script:draggable');
	module('script:ajaxLink');
	define('noCache', true);

	$inline	= $data[':inline'];
	$action	= $inline['action'];
	if ($action)
	{
		$inline['layout']	= $layout;
		$layout	= m("editor:inline", $inline);
	}else{
//		$inline	= NULL;
	}

	$class	= array('adminEditArea');
	if (!$bTop) $class[] = 'adminBottom';
	if ($data[':class']) $class[] = $data[':class'];

	$menu	= array();
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
		foreach($attr as $attrName => &$val) $val = $attrName . '="' . htmlspecialchars($val) . '"';
		$menu[$name]	= implode(' ', $attr);
	}
	$class	= implode(' ', $class);
?>
<link rel="stylesheet" type="text/css" href="admin.css"/>
<div class="{$class}">

<div class="adminEditMenu">
<? if ($dragID){ ?><span class="ui-icon ui-icon-arrow-4-diag"{!$dragID}></span><? } ?>
<? if ($inline){ ?><a href="#" id="inlineEditor">Inline</a><? } ?>
<? foreach($menu as $name => $attr){?>
<a {!$attr}>{$name}</a>
<? } ?>
</div>

<?= $layout ?>
</div>
<? } ?>
