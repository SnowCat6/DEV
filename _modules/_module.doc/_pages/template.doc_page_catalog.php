<? function doc_page_default(&$db, &$menu, &$data){
	$id = $db->id();
?>
{beginAdmin}
<h2>{!$data[title]}</h2>
{showDocument}
{endAdminTop}

{{doc:read:menu=parent:$id;type:catalog}}
<p>{{doc:read:catalog=parent:$id;type:product}}</p>
<? } ?>