<?
function admin_menu(&$val, &$data)
{
	if (!$val) $val = 'main';
	@$store = &$GLOBALS['_CONFIG']['adminToolbar']['menu'];
	if (!is_array($store)) $store = array();

	//slotName => name:url
	if ($data){
		if (!is_array($store[$val])) $store[$val] = array();
		if (is_array($data)){
			foreach($data as $slotName => $menuData){
				$store[$val][$slotName] = $menuData;
			}
		}
	}else{
		$adminSlotNames = array();
		$adminSlotNames['edit']	=	'Изменить';
		
		echo '<ul class="adminRight adminMenu adminPopup">';
		foreach($store as $slotName => $subMenu)
		{
			$name = $adminSlotNames[$slotName];
			if (!$name) $name = $slotName;
			
			$url = '#';
			echo "<li><a href=\"$url\">$name</a>";
			if ($subMenu){
				echo '<ul>';
				foreach($subMenu as $data){
					$name = $url = '';
					list($name, $url) = explode(':', $data);
					if (!$url) $url = '#';
					echo "<li><a href=\"$url\">$name</a>";
				}
				echo '</ul>';
			}
			echo '</li>';
		}
		echo '</ul>';
	}
}
?>