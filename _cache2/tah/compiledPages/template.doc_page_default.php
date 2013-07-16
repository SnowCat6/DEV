<? function doc_page_default(&$db, &$menu, &$data){ ?>
<? beginAdmin() ?>
<? document($data) ?>
<? endAdmin($menu, true) ?>

<div class="news">
<?
$s = array();
$s['parent']	= $db->id();
$s['type']		= 'article';
module("doc:read:news", $s);
?>
</div>
<? } ?>