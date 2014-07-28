<? function doc_page_default(&$db, &$menu, &$data)
{
	$id		= $db->id();
	$menu	= doc_menu_inlineEx($menu, $data, 'document');
?>
{beginAdmin}
{document}
{endAdminTop}
<? event('document.gallery',	$id)?>
<? event('document.feedback',	$id)?>
<? event('document.comment',	$id)?>
<? } ?>