<? function module_tools(){ ?>
{{script:jq_ui}}
{{script:toolsMail}}
<table width="100%" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td>Адреса электронной почты</td>
    <td>Лог ошибок</td>
  </tr>
  <tr>
    <td width="50%"><textarea id="mailInput" name="mailInput" class="input w100" rows="20"></textarea></td>
    <td width="50%"><textarea id="mailError" name="mailError" class="input w100" rows="20"></textarea></td>
  </tr>
</table>
<p><table border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td><input type="button" id="doReformat" value="Обработать" /></td>
    <td>Разделять по</td>
    <td>
    <input name="splitMails" type="text" id="splitMails" value="20" size="5"></td>
    <td>писем</td>
  </tr>
</table>
</p>
<? } ?>

<? function script_toolsMail(){ ?>
<script>
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
</script>
<? } ?>