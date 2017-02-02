// JavaScript Document

var focusKeeper = null;
var SEOdictonary = new Array();

$(function() 
{
	$('#seoTabs .sortable').sortable({axis: 'y'});
	
	$('.focusKeeper .input').focus(function(){
		focusKeeper = $(this);
		showSEOreplace($(this));
	}).blur(function(){
		hideSEOreplace($(this));
	})
	.keyup(function(){
		showSEOreplace($(this));
	});
	
	$(".SEOhelper a").click(function()
	{
		if (focusKeeper){
			focusKeeper.focus();
			var val = $(this).text();
			if (val == '{?}') val = $(this).attr('title');
			insertAtCaret(focusKeeper.get(0), " " + val + " ");
			showSEOreplace(focusKeeper);
		}
		return false;
	})
	.each(function()
	{
		var tx = $(this).attr("title");
		if (tx.length == 0) return;
		var key = $(this).text();
		SEOdictonary[key] = tx;
	});
});

function insertAtCaret(txtarea, text)
{
    var scrollPos = txtarea.scrollTop;
    var strPos = 0;
    var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? 
        "ff" : (document.selection ? "ie" : false ) );
 
    if (br == "ie") { 
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart ('character', -txtarea.value.length);
        strPos = range.text.length;
    }
    else if (br == "ff") strPos = txtarea.selectionStart;

    var front = (txtarea.value).substring(0,strPos);  
    var back = (txtarea.value).substring(strPos,txtarea.value.length); 
    txtarea.value=front+text+back;
    strPos = strPos + text.length;
    if (br == "ie")
	{ 
        txtarea.focus();
        var range = document.selection.createRange();
        range.moveStart ('character', -txtarea.value.length);
        range.moveStart ('character', strPos);
        range.moveEnd ('character', 0);
        range.select();
    }
    else if (br == "ff") {
        txtarea.selectionStart = strPos;
        txtarea.selectionEnd = strPos;
        txtarea.focus();
    }
    txtarea.scrollTop = scrollPos;
}

function showSEOreplace(focusItem)
{
	var textReplace = getTextReplace(focusItem.val());
	
	if (textReplace.length == 0)
	{
		hideSEOreplace(focusItem);
		return;
	}
	
	var replacer = $(".SEOreplacer");
	if (replacer.length == 0)
	{
		$("body").append("<div class='SEOreplacer'></div>");
		replacer = $(".SEOreplacer");
	}
	replacer.css({
		left: focusItem.position().left,
		top: focusItem.position().top + focusItem.height(),
		width: focusItem.width() - 20
	})
	.insertAfter(focusItem)
	.html(textReplace).show();
}
function hideSEOreplace()
{
	$(".SEOreplacer").hide();
}
function getTextReplace(textReplace)
{
	for(key in SEOdictonary)
	{
		keyReg = new RegExp(escapeRegExp(key), "g");
		textReplace = textReplace.replace(keyReg, SEOdictonary[key]);
	}
	return textReplace;
}
function escapeRegExp(str) {
  return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}

