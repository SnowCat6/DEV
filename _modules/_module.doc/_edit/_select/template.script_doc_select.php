<? function script_doc_select($val){?>
{{script:jq}}
{{script:overlay}}
<script>
(function( $ ){
	$.fn.docSelect= function(callback)
	{
		$(".docSelectHolder").remove();
		
		return $(this)
		.css("position", "relative")
		.click(function()
		{
			var holder = $(".docSelectHolder");
			if (holder.size()) {
				holder.remove();
				return false;
			}
			
			var p = $(this).offset();
			var s	= "?" + $(this).attr("rel");
			$('<div class="docSelectHolder" />')
				.appendTo('body')
				.css({left: p.left+50, top:p.top - $('body').scrollTop()})
				.load("{{url:ajax_read_docSelect}}" + s, function()
				{
					$(this).append('<a href="#" class="selectClose">Закрыть</a>');
					$(".docSelectHolder").show();
					$(".docSelect a").click(function(){
						if (callback) callback.call(this, $(this).attr("rel"));
						return false;
					});
					$(".selectClose,.docSelect a").click(function(){
						$(".docSelectHolder").remove();
						return false;
					});
				});
			return false;
		})
	}
})( jQuery );
</script>
<? } ?>

<? function style_doc_select(){ ?>
<style>
.docSelectHolder{
	display:none;
	position:absolute;
	z-index:9999;

}
.ajaxOverlay .docSelectHolder{
	position:fixed;
}
.docSelectHolder a{
	display:block;
	padding:2px 10px;
	text-decoration:none;
}
.docSelectHolder a:hover{
	text-decoration:underline;
}
a.selectClose{
	position:absolute;
	right:0; top: -16px;
	display:block;
	background:white;
	padding:2px 5px;
	margin:0; height:16px;
}
.docSelectHolder .holder{
	max-height:400px;
	max-width:500px;
	overflow:auto;
	border:solid 1px #ccc;
	background:white;
	box-shadow:0 0 10px rgba(0, 0, 0, 0.3);
}
</style>
<? } ?>