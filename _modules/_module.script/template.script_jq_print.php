<? function script_jq_print($val){ 
	m('script:jq');
	m('scriptLoad', "script/jquery.printElement.min.js");
?>
<script>
/*<![CDATA[*/
	jQuery.browser = {};
	jQuery.browser.mozilla = /mozilla/.test(navigator.userAgent.toLowerCase()) && !/webkit/.test(navigator.userAgent.toLowerCase());
	jQuery.browser.webkit = /webkit/.test(navigator.userAgent.toLowerCase());
	jQuery.browser.opera = /opera/.test(navigator.userAgent.toLowerCase());
	jQuery.browser.msie = /msie/.test(navigator.userAgent.toLowerCase());
 /*]]>*/
</script>
<? } ?>
