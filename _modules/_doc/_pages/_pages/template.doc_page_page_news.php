<? function doc_page_page_news(&$db, &$menu, &$data)
{
	$id = $db->id();
?>
<div class="documentHolder">
{beginAdmin}
{document}
{endAdminTop}
</div>

{{doc:read:news3=parent:$id}}
<? event('document.gallery',	$id)?>
<? event('document.feedback',	$id)?>
<? event('document.comment',	$id)?>
<? } ?>