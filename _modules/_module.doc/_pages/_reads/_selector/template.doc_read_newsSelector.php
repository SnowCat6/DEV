<? function doc_read_newsSelector($db, $val, $search)
{
	module('script:jq');
?>
<style>
.newsSelector td td
{
	padding:5px;
	text-decoration:none;
	color:#330099;
	height:32px;

	background: -moz-linear-gradient(top,  rgba(255,255,255,1) 1%, rgba(209,238,255,1) 100%); /* FF3.6+ */
	background: -webkit-gradient(linear, left top, left bottom, color-stop(1%,rgba(255,255,255,1)), color-stop(100%,rgba(209,238,255,1))); /* Chrome,Safari4+ */
	background: -webkit-linear-gradient(top,  rgba(255,255,255,1) 1%,rgba(209,238,255,1) 100%); /* Chrome10+,Safari5.1+ */
	background: -o-linear-gradient(top,  rgba(255,255,255,1) 1%,rgba(209,238,255,1) 100%); /* Opera 11.10+ */
	background: -ms-linear-gradient(top,  rgba(255,255,255,1) 1%,rgba(209,238,255,1) 100%); /* IE10+ */
	background: linear-gradient(to bottom,  rgba(255,255,255,1) 1%,rgba(209,238,255,1) 100%); /* W3C */
	filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#d1eeff',GradientType=0 ); /* IE6-9 */

}
.newsSelectorArrow{
	vertical-align:top;
}
.newsSelectorArrow div{
	position:relative;
}
.newsSelector a{
	color:#330099;
	display:block;
	text-decoration:none;
}
.newsSelector .current a{
	color:white;
}
.newsSelector .current td{
	background:#008bc7;
	color:white;
	filter:none;
}
.newsSelector .current span{
	display:block;
}
.newsSelector span{
	display:none;
}
.newsSelector th div{
	display:none;
}
.newsSelector th .current{
	display:block;
}
</style>
<script language="javascript">
$(function(){
	$(".newsSelector table tr").hover(function(){
		$(".newsSelector .current").removeClass('current');
		$(this).addClass('current');
		$(".newsSelector #image" + $(this).attr("id")).addClass("current");
	});
});
</script>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="newsSelector">
<tr>
    <th valign="top"><?
	$db->seek(0);
	$class = NULL;
	while($data = $db->next()){
		$id		= $db->id();
		$title	= docTitle($id);
		$class	= is_null($class)?' class="current"':'';
		echo "<div id=\"imageNewsSelector$id\"$class>";
		displayThumbImageMask($title, 'design/selectorMask.png') || print('<img src="design/spacer.gif" width="500" height="250" />');
		echo "</div>";
	}
	?></th>
    <td width="100%" valign="top">
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<?
	$db->seek(0);
	$class = NULL;
	while($data = $db->next()){
		$id		= $db->id();
		$url	= getURL($db->url());
		$title	= htmlspecialchars($data['title']);
		$date	= makeDate($data['datePublish']);
		if ($date) $date = date('d.m.Y', $date);
		$class	= is_null($class)?' class="current"':'';
		$menu	= doc_menu($id, $data);
?>
<tr {!$class} id="NewsSelector{$id}"><td class="newsSelectorArrow"><div><span></span></div></td><td width="100%">
<?
		beginAdmin();
		echo "<a href=\"$url\">$title</a>";
		endAdmin($menu);
?>
</td></tr>
<? } ?>
</table>
</td>
</tr>
</table>

<? return $search; } ?>