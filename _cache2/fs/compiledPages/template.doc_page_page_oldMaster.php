<? function doc_page_page_oldMaster($db, &$menu, &$data){
	$id	= $db->id();
?><? beginAdmin() ?><? document($data) ?><? endAdmin($menu) ?><? $module_data = array(); $module_data["parent"] = "$id"; moduleEx("doc:read:oldMaster", $module_data); ?><? } ?>
