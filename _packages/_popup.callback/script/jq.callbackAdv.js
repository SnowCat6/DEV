// JavaScript Document

$(function()
{
	if (callbackAdvTimeout3 && 
		advGetCookie("callbackAdv")) return;
	setTimeout(function()
	{
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
	}, callbackAdvTimeout*1000);
	$(".callbackAdvClose").click(callbackAdvClose);
});

function callbackAdvShow()
{
	$(".callbackAdvHolder")
	.show()
	.addClass("callbackAdvActive")

	$(".callbackAdvPhone input").focus();
}
function callbackAdvClose()
{
		var date = new Date(new Date().getTime() + callbackAdvTimeout2 * 1000);
		document.cookie = "callbackAdv=hide; path=/; expires=" + date.toUTCString();

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