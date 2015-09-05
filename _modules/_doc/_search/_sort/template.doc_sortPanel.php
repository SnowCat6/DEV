<?
function doc_sortPanel($db, $val, &$search)
{
	$search	= module("doc:sort", $search);
?>
<link rel="stylesheet" type="text/css" href="css/sortPanel.css">

{push}
<div class="sort inline">

<div class="orderFilter">
Сначала: <span class="holder">{{display:sortNames}}</span>
</div>

<div class="pagesFilter">
<span class="holder">{{display:sortPages}}</span>
</div>

</div>
{pop:sortPanel}
<? return $search; } ?>
