// JavaScript Document
$(function(){
	$(".snippetEditHolder a").click(function(){
		snippetInsert(null, $(this).text());
		return false;
	});
});
function snippetInsert(name, snippet){
	var code = '['+'[' + snippet + ']'+']';
	editorInsertHTML(name, code);
}
