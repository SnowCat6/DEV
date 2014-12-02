<? function doc_page_article(&$db, &$menu, &$data)
{
	$id	= $db->id();
	$menuInline	= doc_menu_inlineEx($menu, $data, 'document');
	$date		= $data['datePublish'];
	if ($date){
		$date	= date('d.m.Y');
	}
?>
<link rel="stylesheet" type="text/css" href="css/pageStyle.css">
<div class="article titleImage">
	{{doc:titleImage:$id:mask=mask:design/articleMask.png;hasAdmin:true;adminMenu:$menu}}
    <date>{$date}</date>
	<h1>{$data[title]}</h1>
</div>
<div class="titleArticle">
    {beginAdmin:$menuInline}
    {document}
    {endAdmin}
</div>

<? event('document.gallery',	$id)?>
<? event('document.feedback',	$id)?>
<? event('document.comment',	$id)?>

<? } ?>