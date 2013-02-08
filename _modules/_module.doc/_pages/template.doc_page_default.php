<? function doc_page_default(&$db, &$menu, &$data){ ?>
{beginAdmin}
{document}
{endAdminTop}
<?
$s = array();
$s['parent']	= $db->id();
$s['type']		= 'article';
module("doc:read:$data[doc_type]", $s);
?>
<? } ?>