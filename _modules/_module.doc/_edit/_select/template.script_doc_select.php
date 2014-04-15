<? function script_doc_select($val){?>
{{script:jq}}
{{script:overlay}}
<script>
(function( $ ){
	$.fn.docSelect= function(callback)
	{
		return $(this)
		.css("position", "relative")
		.click(function()
		{
			var holder = $(".docSelectHolder");
			if (holder.size()) {
				holder.remove();
				return false;
			}
			
			var s	= "?" + $(this).attr("rel");
			$('<div class="docSelectHolder" />')
				.appendTo($(this))
				.load("{{url:ajax_read_docSelect}}" + s, function()
				{
					$(".docSelectHolder").show();
					$(".docSelect a").click(function(){
						$(".docSelectHolder").remove();
						if (callback) callback.call(this, $(this).attr("rel"));
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
	top:0; left:50%;
	z-index:9999;
}
.panel div.docSelectHolder, .panel .docSelectHolder div{
	padding:0;
}
.docSelect{
	max-height:400px;
	overflow:auto;
	border:solid 1px #ccc;
	background:white;
	box-shadow:0 0 10px rgba(0, 0, 0, 0.3);
}
a.selectClose{
	position:absolute;
	right:0; bottom: 100%;
	display:block;
	background:white;
	padding:2px 5px;
	margin:0;
}
.docSelect a{
	display:block;
	padding:2px 10px;
	text-decoration:none;
}
.docSelect a:hover{
	text-decoration:underline;
}
</style>
<? } ?>