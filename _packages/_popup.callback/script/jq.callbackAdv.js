// JavaScript Document
$(function()
{
	$(".callbackAdvHolder form")
	.submit(function(){
		$(".callbackAdvHolder").hide();
	});
		
	$(".callbackAdvButton, .callbackButton a").click(callbackAdvShow);
	$(".callbackAdvClose").click(callbackAdvClose);
	$(".callbackAdvHolder")
	.click(function(e){
		if(e.target != this) return; 
		callbackAdvClose();
	});
	
	if (advGetOptions("bCallbackDisabled")) return;
	
	if (advGetOptions("callbackAdvTimeout3") && 
		advGetCookie("callbackAdv") == 'hide') return;

	var timeout = advGetOptions("callbackAdvTimeout");
	if (advGetCookie("callbackAdv")){
		timeout = advGetOptions("callbackAdvTimeout2");
		advSetOptions("callbackAdvTimeout2", 0);
	}
	
	setTimeout(function()
	{
		if (!advGetCookie("callbackAdv"))	advSetCookie('callbackAdv', 'pause');
		else advSetCookie('callbackAdv', 'hide');

		callbackAdvShow();
	}, timeout*1000);
});

function callbackAdvShow()
{
	$(".callbackAdvHolder")
	.show()
	.addClass("callbackAdvActive")
	$(".callbackAdvButton").addClass("callbackAdvActive");

	$(".callbackAdvPhone input").focus();
}
function callbackAdvClose()
{
	advSetCookie('callbackAdv', 'hide');

	$(".callbackAdvHolder")
	.hide()
	.removeClass("callbackAdvActive");
	$(".callbackAdvButton").removeClass("callbackAdvActive");
	
	if (advGetOptions("callbackAdvTimeout2") == 0) return false;
	setTimeout(callbackAdvShow, advGetOptions("callbackAdvTimeout2")*1000);
	advSetOptions("callbackAdvTimeout2", 0);
	return false;
}
function advGetOptions(key){
	var d = $("body").data("callbackAdv");
	if (d) return d[key];
	d = $.parseJSON($(".callbackAdv").attr("rel"));
	$("body").data("callbackAdv", d);
	return d[key];
}
function advSetOptions(key, value){
	var d = $("body").data("callbackAdv");
	d[key] = value;
}
function  advSetCookie(name, val)
{
	var date = new Date(new Date().getTime() + advGetOptions("callbackAdvTimeout3") * 1000);
	document.cookie = name + '=' + val + "; path=/; expires=" + date.toUTCString();
}
function advGetCookie(name) {
	var matches = document.cookie.match(new RegExp(
		"(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
	));
	return matches ? decodeURIComponent(matches[1]) : undefined;
}