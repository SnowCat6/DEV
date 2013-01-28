//	RootPath='';
//	ImageFolder='';  

function doEdit(name, h){
	//Init FCK Editor
	oFCKeditor = new FCKeditor(name, '', h*14+80, 'BasicEx');
	oFCKeditor.BasePath	= BasePath;

	oFCKeditor.Config['ImageUpload'] = false;
	oFCKeditor.Config['FlashUpload'] = false;
	oFCKeditor.Config['LinkUpload']  = false;

	var cnn = '../filemanager/browser/default/browser.html?Connector='+RootPath+'file_connector.htm&ServerPath='+ImageFolder;
	
	oFCKeditor.Config['ImageBrowserURL']= cnn + '&Type=Image';
	oFCKeditor.Config['ImageUploadURL'] = RootPath+'file_upload.htm?Type=Image';

	oFCKeditor.Config['FlashBrowserURL']=cnn + '&Type=Flash';
	oFCKeditor.Config['FlashUploadURL'] = RootPath+'file_upload.htm?Type=Flash';

	oFCKeditor.Config['LinkBrowserURL'] = cnn;

	try{
		if (browser){
			var mN=document.location.protocol+'//'+document.location.host;
			var cnn = RootPath+browser+'/ckfinder.html?Connector='+mN+RootPath+'file_fconnector.htm&ServerPath='+ImageFolder;
			//	CKFinder
			oFCKeditor.Config['ImageBrowserURL'] = cnn + '&type=Image';
			oFCKeditor.Config['FlashBrowserURL'] = cnn + '&type=Flash';
			oFCKeditor.Config['LinkBrowserURL']  = cnn;
		}
	}catch(e){};

//	Build edit
	oFCKeditor.ReplaceTextarea();
}

$(function(){
	$("textarea").each(function(){
		var id = $(this).attr("id");
		if (id == '' || $(this).hasClass("FCKEditor")) return;
		$(this).addClass("FCKEditor");
		doEdit($(this).attr('name'), $(this).attr('rows'));
	});
});