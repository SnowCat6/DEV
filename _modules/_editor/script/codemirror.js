// JavaScript Document
$(function()
{
	$(".code_editor").each(function()
	{
		var editor = CodeMirror.fromTextArea(this, {
		lineNumbers: true
		});
	});
});
