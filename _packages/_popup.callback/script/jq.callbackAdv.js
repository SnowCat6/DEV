// JavaScript Document

$(function()
{
	if (callbackAdvTimeout3 && 
		advGetCookie("callbackAdv") == 'hide') return;

	var timeout = callbackAdvTimeout;
	if (advGetCookie("callbackAdv")){
		timeout = callbackAdvTimeout2;
		callbackAdvTimeout2 = 0;
	}
	
	setTimeout(function()
	{
		if (!advGetCookie("callbackAdv"))	advSetCookie('callbackAdv', 'pause');
		else advSetCookie('callbackAdv', 'hide');

		$(".callbackAdvHolder")
		.click(function(e){
			if(e.target != this) return; 
			callbackAdvClose();
		});
		$(".callbackAdvHolder form")
		.submit(function(){
			$(".callbackAdvHolder").hide();
		});
		callbackAdvShow();
	}, timeout*1000);
	$(".callbackAdvClose").click(callbackAdvClose);
});

function  advSetCookie(name, val)
{
	var date = new Date(new Date().getTime() + callbackAdvTimeout3 * 1000);
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
	
	if (callbackAdvTimeout2 == 0) return false;
	setTimeout(callbackAdvShow, callbackAdvTimeout2*1000);
	callbackAdvTimeout2 = 0;
	return false;
}

function advGetCookie(name) {
	var matches = document.cookie.match(new RegExp(
		"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
	));
	return matches ? decodeURIComponent(matches[1]) : undefined;
}