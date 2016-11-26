// JavaScript Document

$(function(){
	$("#doReformat").click(function(){
		val = doMailReformat($("#mailInput").val());
		$("#mailInput").val(val);
	});
	$("#mailInput").change(function(){
		val = doMailReformat($("#mailInput").val());
		$("#mailInput").val(val);
	});
});

function doMailReformat(input)
{
	var re = /,|;|\r|\n/;
	var input = input.split(re);
	var splits = $("#splitMails").val();
	
	var mails = new Array();
	var errors = new Array();
	
	for (mail in input)
	{
		mail = input[mail];
		mail = mail.replace(/^\s+|\s+$/g,'');
		if (mail == '') continue;
		if (/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,6}$/i.test(mail)) {
			// Successful match
			mails[mail] = mail;
		} else {
			// Match attempt failed
			errors[mail] = mail;
		}
	}

	var ix = 0;
	input = '';
	for (mail in mails){
		if (ix) input += '; ';
		if (++ix % splits == 0) input += "\r\n\r\n";
		input += mail;
	}
	
	var e = 'Писем: ' + ix + "\r\n\r\n";
	for(error in errors){
		e += error + "; ";
	}
	$("#mailError").val(e);

	return input;
}