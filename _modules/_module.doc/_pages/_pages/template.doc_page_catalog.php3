<? function doc_page_catalog(&$db, &$menu, &$data){
	$id = $db->id();
?>
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css"/>
{beginAdmin}
{document}
{endAdminTop}

<? $search = module("doc:search:$id", getValue('search')) ?>
<div class="product list">
<?	if ($search){ ?>
<h2>Поиск по каталогу</h2>
<?
	$search['parent*']	= "$id:catalog";
	$search['type']		= 'product';
	module('doc:read:catalog', $search);
?>
<? }else{ ?>
{{doc:read:catalog=parent*:$id:catalog;type:product}}
<? } ?>
</div>
<? } ?>