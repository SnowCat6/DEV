<? function snippets_tools($val, $data){
	m('style:snippetEdit');
	m('script:snippetEdit');
?>
<div class="snippetEdit">
<div class="snippetEditButton">Сниппеты</div>
<div class="snippetEditHolder shadow">
<?
$snippets = module('snippets:get');
foreach($snippets as $name => $code){ ?>
<a href="#">{$name}</a>
<? } ?>
</div>
</div>
<? } ?>
<? function style_snippetEdit($val){ ?>
<style>
.snippetEdit{
	position:relative;
}
.snippetEdit .snippetEditHolder{
	display:none;
	position:absolute;
	top:100%; right:0;
	width:auto;
	min-width:150px;
	background:white;
	border:solid 1px #888;
	padding-right:20px;
}
.snippetEdit:hover .snippetEditHolder{
	display:block;
}
.snippetEdit .snippetEditHolder a{
	display:block;
	width:100%;
	padding:5px 10px;
	color:#000;
	text-decoration:none;
}
.snippetEdit .snippetEditHolder a:hover, .snippetEdit .snippetEdit:hover .snippetEditButton{
	background:#09F;
}
.snippetEdit .snippetEditButton{
	cursor:pointer;
	padding:2px 10px;
}
</style>
<? } ?>
<? function script_snippetEdit($val){ m('script:jq'); ?>
<script>
$(function(){
	$(".snippetEditHolder a").click(function(){
		snippetInsert(null, $(this).text());
		return false;
	});
});
function snippetInsert(name, snippet){
<? if (module('snippets:visual')){ ?>
	var code = '<p class="snippet ' + snippet + '">' + "</p>";
<? }else{ ?>
	var code = '[[' + snippet + ']]';
<? } ?>
	editorInsertHTML(name, code);
	snippet.selectedIndex = 0;
}
</script>
<? } ?>