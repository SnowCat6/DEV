// JavaScript Document
$(function()
{
	$(".code_editor")
	.removeClass("code_editor")
	.each(function()
	{
		var editor = CodeMirror.fromTextArea(this, {
                    autoCloseBrackets: true,
                    autoCloseTags: true,
                    autoFormatOnStart: false,
                    autoFormatOnUncomment: true,
                    continueComments: true,
                    enableCodeFolding: true,
                    enableCodeFormatting: true,
                    enableSearchTools: true,
                    highlightMatches: true,
                    indentWithTabs: false,
                    lineNumbers: false,
                    lineWrapping: true,
                    mode: 'htmlmixed',
                    matchBrackets: true,
                    matchTags: true,
                    showAutoCompleteButton: true,
                    showCommentButton: true,
                    showFormatButton: true,
                    showSearchButton: true,
                    showTrailingSpace: true,
                    showUncommentButton: true,
                    styleActiveLine: true,
                    theme: 'default',
                    useBeautifyOnStart: false
			});
	});
});
