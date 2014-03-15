<? function doc_page_news(&$db, &$menu, &$data)
{
	$id = $db->id();
?>
{beginAdmin}
{document}
{endAdminTop}
{{doc:read:news3=parent:$id}}
<? event('document.gallery',	$id)?>
<? event('document.feedback',	$id)?>
<? event('document.comment',	$id)?>
<? } ?>