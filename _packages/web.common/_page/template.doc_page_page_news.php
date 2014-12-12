<? function doc_page_page_news(&$db, &$menu, &$data)
{
	$id			= $db->id();
	$menuInline	= doc_menu_inlineEx($menu, $data, 'document');
?>
<link rel="stylesheet" type="text/css" href="css/pageStyle.css">
<div class="titleImage">
	{{doc:titleImage:$id=mask:design/pageMask.png;hasAdmin:true;adminMenu:$menu}}
	<h1>{$data[title]}</h1>
</div>

<div class="pageContent">
    {beginAdmin:$menuInline}
    {document}
    {endAdmin}
</div>
{{doc:read:news=parent:$id}}
<? } ?>