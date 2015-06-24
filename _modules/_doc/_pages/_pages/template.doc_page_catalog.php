<? function doc_page_catalog(&$db, &$menu, &$data){
	$id		= $db->id();
	$menu	= doc_menu_inlineEx($menu, $data, 'document');
?>
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css"/>

{beginAdmin}
{document}
{endAdminTop}

<? $search = module("doc:search:$id", getValue('search')) ?>
<div class="product list">
<? module('doc:read:catalog', $search) ?>
</div>
<? } ?>