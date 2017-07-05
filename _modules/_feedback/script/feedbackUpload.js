// JavaScript Document

$(function()
{
	$(".feedbackPolicyUpload")
	.fileUpload(function(responce)
	{
		for(var image in responce)
		{
			var attr = responce[image];
			if (attr['error']){
				alert(attr['error']);
				continue;
			}
			
			var fileName = attr['path'];
			$(this).find(".fileTitle").text(fileName);
			break;
		}
	});
});