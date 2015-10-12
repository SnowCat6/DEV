<?
class adminMenu
{
	static function show($data, $layout)
	{
		setNoCache();
		
		$menu	= array();
		$class	= array('adminEditArea');
		
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
			
			$menu[]	= self::adminEditBuildMenuEntry('inline', array
			(
				'href'	=> '#',
				'id'	=> 'inlineEditor',
				'title'	=> 'Нажмите для редактирования контента на странице.'
			));
		}
		
		meta::begin(array(':menuHeader' => NULL));
		self::adminEditBuildMenu($menu, $data);
		meta::end();
	
		if ($id = $data[':sortable'])
		{
			module('script:draggable');
			$menu[]	= self::adminEditBuildMenuEntry('C', array
			(
				'class'		=> 'admin_sort_handle',
				'title'		=> 'Сортировка элементов, нажмите и переместите элемент на нужную позицию.',
				'sort_index'=> $id
			));
		}
	
		switch($data[':type'])
		{
		case 'bottom':
			$class[] = 'adminBottom';
			break;
		case 'left':
			$class[] = 'adminLeft';
			break;
		default:
			$class[] = 'adminRight';
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
	<? }

    static function adminEditBuildMenu(&$menu, $data)
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
                $menu[] = self::adminEditBuildMenuEntry($name, $url);
            return;
        }
    
        $menu2	= array();
        foreach($data as $name => $url)
            $menu2[] = self::adminEditBuildMenuEntry($name, $url);
    
        $menu2	= implode('', $menu2);
        $menu[]	= "<a href='#'>Меню</a><span class=\"adminDropMenu\">$menu2</span>";
    }
    static function adminEditBuildMenuEntry($name, $url)
    {
        if (!$url)
		{
            if (is_string($name)){
                meta::set(':menuHeader', "<span class='adminMenuTitle'>$name</span>");
                return;
            }
            if (is_null(meta::get(':menuHeader')))
                return;
            meta::set(':menuHeader', '<hr />');
            return;
        }
    
        $header	= meta::get(':menuHeader');	
        meta::set(':menuHeader', '');
        
        $attr	= array();
        list($name, $iid) = explode('#', $name);
        if ($iid) $attr['id'] = $iid;
        
        if (is_array($url)){
            foreach($url as $attrName => $val) $attr[$attrName]	= $val;
        }else $attr['href']	= $url;
    
        $attr	= makeProperty($attr);
        $n		= htmlspecialchars($name);
        return "$header<a $attr>$n</a>";
    }
};
?>