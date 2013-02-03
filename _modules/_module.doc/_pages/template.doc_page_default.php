<? function doc_page_default(&$db, &$menu, &$data){ ?>
{beginAdmin}
<h2>{!$data[title]}</h2>
{!$data[document]}
{endAdminTop}
<?
$s = array();
$s['parent'] = $db->id();
module('doc:read:default', $s);
?>
<? } ?>