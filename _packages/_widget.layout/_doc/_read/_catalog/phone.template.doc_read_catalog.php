<?
function phone_doc_read_catalog_beginCache($db, &$val, &$search)
{
	$s	= getValue('search');
	$search['prop'] = $s['prop'];
	$search['page']	= getValue('page');
	
	return hashData($search);
}

function phone_doc_read_catalog($db, &$val, &$search)
{
	$p		= dbSeek($db, 6, array('search' => getValue('search')));
?>
<link rel="stylesheet" type="text/css" href="css/readCatalog.css">
{!$p}
<? while($data = $db->next())
{
	$id		= $db->id();
	$link	= getURL($db->url());
	$note	= docNote($data);
?>
<div class="oldMasterList">
	<div class="image">
        {{doc:titleImage:$id=clip:722x360;property.href:$link}}
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
