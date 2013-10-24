<? function doc_page_article_feedback(&$db, &$menu, &$data){
	$id		= $db->id();
	$parents= getPageParents($id);
	$parent	= array_pop($parents);
	module("doc:page:$parent");
}
?>