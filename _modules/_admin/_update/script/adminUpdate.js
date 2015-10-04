// JavaScript Document

$(function()
{
	"use strict";
	$.get('admin_update_check.htm?ajax')
		.done(function(data){
			$(".adminUpdateMessage").html(data);
			doUpdateLink();
		});
	$(document).on("jqReady", doUpdateLink);
});

function doUpdateLink()
{
	"use strict";
	$(".cmsDownloadUpdateLink")
		.click(function()
		{
			$(this).text('Start download...');
			$.get('admin_update_download.htm?ajax')
				.done(function(data)
				{
					$(".adminUpdateMessage").html(data);
					doUpdateLink();
				});
			return false;
		});
	
	$(".cmsUpdateLink")
		.click(function(){
			$(this).text('Start update...');
			$.get('admin_update_install.htm?ajax')
				.done(function(data)
				{
					$(".adminUpdateMessage").html(data);
					doUpdateLink();
				});
			return false;
		});
}
