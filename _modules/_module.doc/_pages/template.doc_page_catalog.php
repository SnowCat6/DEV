<? function doc_page_catalog(&$db, &$menu, &$data){
	$id = $db->id();
?>
<link rel="stylesheet" type="text/css" href="../../../_templates/baseStyle.css"/>
{beginAdmin}
<h1>{!$data[title]}</h1>
{document}
{endAdminTop}

{{doc:read:menu=parent:$id;type:catalog}}

<? $search = module("doc:search:$id:Свойства товара", getValue('search')) ?>
<div class="product list">
<?	if ($search){ ?>
<h2>Поиск по каталогу</h2>
<?
		$search['parent']	= $id;
		$search['type']		= 'product';
		module('doc:read:catalog2', $search);
?>
<? }else{ ?>
{{doc:read:catalog2=parent:$id;type:product}}
<? } ?>
</div>
<? } ?>