<? function widget_siteMenu($id, $data){ ?>

<link rel="stylesheet" type="text/css" href="css/siteMenu.css">

<div class="siteMenu menu {$data[class]}">
	{{doc:read:menu=@!place:$id}}
</div>

<? } ?>

<?
//	+function widget_siteMenuInline
function widget_siteMenuInline($id, $data){ ?>

<link rel="stylesheet" type="text/css" href="css/siteMenu.css">

<div class="siteMenu menu inline {$data[class]}">
	{{doc:read:menu=@!place:$id}}
</div>

<? } ?>