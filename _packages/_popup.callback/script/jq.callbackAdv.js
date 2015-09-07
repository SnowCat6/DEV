// JavaScript Document
var callBackOptions = null;
$(function()
{
	callBackOptions	=$.parseJSON($(".callbackAdv").attr("rel"));
	
	$(".callbackAdvHolder form")
	.submit(function(){
		$(".callbackAdvHolder").hide();
	});
		
	$(".callbackAdvButton").click(callbackAdvShow);
	$(".callbackAdvClose").click(callbackAdvClose);
	$(".callbackAdvHolder")
	.click(function(e){
		if(e.target != this) return; 
		callbackAdvClose();
	});
	
	if (callBackOptions.bCallbackDisabled) return;
	
	if (callBackOptions.callbackAdvTimeout3 && 
		advGetCookie("callbackAdv") == 'hide') return;

	var timeout = callBackOptions.callbackAdvTimeout;
	if (advGetCookie("callbackAdv")){
		timeout = callBackOptions.callbackAdvTimeout2;
		callBackOptions.callbackAdvTimeout2 = 0;
	}
	
	setTimeout(function()
	{
		if (!advGetCookie("callbackAdv"))	advSetCookie('callbackAdv', 'pause');
		else advSetCookie('callbackAdv', 'hide');

		callbackAdvShow();
	}, timeout*1000);
});

function  advSetCookie(name, val)
{
	var date = new Date(new Date().getTime() + callBackOptions.callbackAdvTimeout3 * 1000);
	document.cookie = name + '=' + val + "; path=/; expires=" + date.toUTCString();
}
function callbackAdvShow()
{
	$(".callbackAdvHolder")
	.show()
	.addClass("callbackAdvActive")

	$(".callbackAdvPhone input").focus();
}
function callbackAdvClose()
{
	advSetCookie('callbackAdv', 'hide');

	$(".callbackAdvHolder")
	.hide()
	.removeClass("callbackAdvActive");
	
	if (callBackOptions.callbackAdvTimeout2 == 0) return false;
	setTimeout(callbackAdvShow, callBackOptions.callbackAdvTimeout2*1000);
	callBackOptions.callbackAdvTimeout2 = 0;
	return false;
}

function advGetCookie(name) {
	var matches = document.cookie.match(new RegExp(
		"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
	));
	return matches ? decodeURIComponent(matches[1]) : undefined;
}