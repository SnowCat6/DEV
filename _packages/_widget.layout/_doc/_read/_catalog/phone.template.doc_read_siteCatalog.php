<?
function phone_doc_read_siteCatalog($db, &$val, &$search)
{
	$p		= dbSeek($db, 6, array('search' => getValue('search')));
?>
<link rel="stylesheet" type="text/css" href="css/readCatalog.css">

<div class="documentHolder">
{{display:searchPanel}}
</div>

{!$p}
<? while($data = $db->next())
{
	$id		= $db->id();
	$link	= getURL($db->url());
	$note	= docNote($data);
?>
<div class="readCatalogItems">
	<div class="image">
        {{doc:titleImage:$id=clip:330x200;property.href:$link}}
    </div>
    <div class="content">
        <h2><a href="{!$link}" title="{$data[title]}">{$data[title]}</a></h2>
        <p>{{prop:read:plain=id:$id}}</p>
        <blockquote>{!$note}</blockquote>
    </div>
</div>
<? } ?>
{!$p}
<? } ?>
