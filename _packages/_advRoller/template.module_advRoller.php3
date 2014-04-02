<? function module_advRoller(){
	module('script:jq');
	module('script:advRoller');

$ix			= 0;
$class		= '';
$advTable	= array();
do{
	++$ix;
	$adv	= "advPopup_$ix";
	$file 	= images."/$adv.html";
	if (@filesize($file)) $advTable[$ix] = $adv;
}while($ix < 20);

$idEmpty = NULL;
if (access('write', "text:advPopup")){
	for($ix = 1; $ix < 20; ++$ix){
		if (isset($advTable[$ix])) continue;
		$advTable[$ix] = $idEmpty = "advPopup_$ix";
		break;
	}
}
?>
<link rel="stylesheet" type="text/css" href="advRoller.css"/>
<div class="advRoller">
<?
$class = NULL;
foreach($advTable as $ix => &$adv){
	$class = is_null($class)?'':' style="display:none"';
?><div id="adv{$ix}"{!$class} class="content">{{read:$adv}}</div><? } ?>
<? if (count($advTable) > 1){ ?>
<div class="seek">
<?
$class = NULL; $num = 0;
foreach($advTable as $ix => &$adv){
	++$num;
	$class	= is_null($class)?' class="current"':'';
	$rel	= $idEmpty == $adv?' rel="empty"':'';
?><a href="#" id="adv{$ix}"{!$class}{!$rel}>{$num}</a><? } ?>
</div>
<? } ?>
</div>
<? } ?>
<? function script_advRoller($val){ ?>
<script type="text/javascript">
/*<![CDATA[*/
var seekTimer = 0;
var seekTimeout= 1000*5;
$(function(){
	$(".advRoller .seek a").hover(function(){
		var now = $(".advRoller .seek a.current");
		var id = now.attr("id");
		now.removeClass("current");
		$(".advRoller > div#" + id).hide();
		
		id = $(this).attr("id");
		$(this).addClass("current");
		$(".advRoller > div#" + id).show();
		
		clearTimeout(seekTimer);
	}, setNextTimeout);
	
	$(".advRoller").hover(function(){
		clearTimeout(seekTimer);
	}, setNextTimeout);
});
function setNextTimeout(){
	clearTimeout(seekTimer);
	seekTimer = setTimeout(nextSeek, seekTimeout);
}
function nextSeek()
{
	var now = $(".advRoller .seek a.current");
	var id = now.attr("id");

	var next = now.next();
	if (next.attr("rel") == "empty") next = next.next();
	if (next.length == 0){
		next =  $.find(".advRoller .seek a");
		next = $(next[0]);
	}

	var nextId = next.attr("id");
	if (id == nextId) return;
	
	now.removeClass("current");
	$(".advRoller > div#" + id).fadeOut();
	
	next.addClass("current");
	$(".advRoller > div#" + nextId).fadeIn();

	setNextTimeout();
}
setNextTimeout();
 /*]]>*/
</script>

<? } ?>