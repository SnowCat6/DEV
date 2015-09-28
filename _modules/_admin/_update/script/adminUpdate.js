// JavaScript Document

$(function()
{
	$.get('admin_update_check.htm')
		.done(function(data){
			$(".adminUpdateMessage").html(data);
			doUpdateLink();
		});
});

function doUpdateLink()
{
	$(".cmsDownloadUpdateLink")
		.click(function()
		{
			$(this).text('Start download...');
			$.get('admin_update_download.htm')
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
			$.get('admin_update_install.htm')
				.done(function(data)
				{
					$(".adminUpdateMessage").html(data);
					doUpdateLink();
				});
			return false;
		});
}
