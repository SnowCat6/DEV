<? function doc_page_page_address($db, &$menu, &$data){
	$id		= $db->id();
?><? beginAdmin() ?><? document($data) ?><? endAdmin($menu) ?><? $module_data = array(); $module_data["parent"] = "this"; moduleEx("doc:read:yandexMap", $module_data); ?>
<br><br>
<? $module_data = array(); $module_data["parent"] = "this"; moduleEx("doc:read:address", $module_data); ?><? } ?>
