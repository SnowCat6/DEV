<? function module_advRoller(){
	module('script:jq');

$ix			= 0;
$class		= '';
$advTable	= array();
do{
	++$ix;
	$adv	= "advPopup_$ix";
	$file 	= images."/$adv.html";
	if (access('write', "text:$adv")){
		$advTable[$ix] = $adv;
	}else{
		if (@filesize($file))
			$advTable[$ix] = $adv;
	}
}while(is_file($file)); ?>
<link rel="stylesheet" type="text/css" href="advRoller.css"/>
<div class="advRoller">
<?
$class = NULL;
foreach($advTable as $ix => &$adv){
	$class = is_null($class)?'':' style="display:none"';
?><div id="adv{$ix}"{!$class}>{{read:$adv}}</div><? } ?>
<? if (count($advTable) > 1){ ?>
<div class="seek">
<?
$class = NULL; $num = 0;
foreach($advTable as $ix => &$adv){
	++$num;
	$class = is_null($class)?' class="current"':'';
?><a href="#" id="adv{$ix}"{!$class}>{$num}</a><? } ?>
</div>
<? } ?>
</div>

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
function nextSeek(){
	var now = $(".advRoller .seek a.current");
	var id = now.attr("id");
	now.removeClass("current");
	$(".advRoller > div#" + id).hide();

	var next = now.next();
	if (next.length == 0){
		next =  $.find(".advRoller .seek a");
		next = $(next[0]);
	}

	var nextId = next.attr("id");
	next.addClass("current");
	$(".advRoller > div#" + nextId).show();

	setNextTimeout();
}
setNextTimeout();
 /*]]>*/
</script>

<? } ?>