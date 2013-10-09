<? function script_autocomplete($val){?>
<? module('script:jq')?>
<script type="text/javascript">
/*<![CDATA[*/
var aoutocompleteNow = null;
$(function(){
	$(".autocomplete").each(function(index, element) {
		$(this)
			.autocomplete(window[$(this).attr("options")])
			.on('focus', function(event){
				aoutocompleteNow = $(this);
				$(this).autocomplete("search", "");
			});
    });
});
 /*]]>*/
</script>
<? } ?>
