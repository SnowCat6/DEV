<? function script_preview($val){
	m('script:jq');
?>
<script>
var previewLoaded = false;
var mouseX = mouseY = 0;
var previewDB = new Array();
$(function(){
	$(".previewLink a,a.preview")
	.hover(function()
	{
 		previewLoaded = true;
		
		var lnk = 'preview_' + $(this).attr("href").replace(/^\//, '');
		var data = previewDB[lnk];
		if (data){
			$("<div id='previewHolder'>")
			.css("z-index", 999)
			.html(data)
			.appendTo('body');
			previewMove();
			return;
		}
		
		$.ajax(lnk).done(function(data)
		{
			$("#previewHolder").remove();
			previewDB[lnk] = data;
			if (!previewLoaded) return;
			$("<div id='previewHolder'>")
			.css("z-index", 999)
			.html(data)
			.appendTo('body');
			previewMove();
		});
	}, function (){
 		previewLoaded = false;
 		$("#previewHolder").remove();
	});
	$(".previewLink a,a.preview")
		.removeClass('previewLink')
		.removeClass('preview');
	
	$('body').on("mousemove.preview", function(e)
	{
		mouseX = e.clientX;
		mouseY = e.clientY;
		if (!previewLoaded) return;
		previewMove();
	});
});
function previewMove()
{
	var overlay = $('#previewHolder');
	var x = mouseX+15, y = mouseY+15;
	var w = $(window).width() - 25, h = $(window).height() - 25;
	if (x + overlay.width() > w) x = Math.max(0, w - overlay.width());
	if (y + overlay.height()> h) y = Math.max(0, h - overlay.height());
	overlay.css({left:x, top:y});
}
</script>
<? } ?>
<? function style_preview($val){ ?>
<style>
#previewHolder{
	position:fixed;
	background:#fff;
	left:0; top:0;
	box-shadow:0 0 30px rgb(0, 0, 0);
	border:solid 1px #eee;
	max-width:500px;
	min-width:400px;
	min-height:80px;
}
#previewHolder *{
	margin:0;
}
.previewTitle{
	position:absolute;
	top:0; left: 0; right: 0;
	background:rgba(0, 0, 0, 0.5);
	font-size:18px; font-weight:normal;
	padding:5px 10px;
	color:white;
	text-shadow:2px 2px 5px rgba(0, 0, 0, 0.5);
}
.previewProperty{
	position:absolute;
	bottom: 0;left: 0; right: 0;
	background:rgba(0, 0, 0, 0.5);
	padding:5px 10px;
	color:white;
}
.previewImage{
	text-align:center;
}
</style>
<? } ?>
