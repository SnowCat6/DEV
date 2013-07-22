<? function doc_page_default(&$db, &$menu, &$data)
{
	$id = $db->id();
?>
{beginAdmin}
{document}
{endAdminTop}
<? event('document.gallery',	$id)?>
<? event('document.feedback',	$id)?>
<? event('document.comment',	$id)?>
<? } ?>