<!doctype html>
<html>
<head>
<meta charset="utf-8">
<!-- TemplateBeginEditable name="head" -->
<!-- TemplateEndEditable -->
{head}
<style>
body{
	font-family:Gotham, "Helvetica Neue", Helvetica, Arial, sans-serif;
	font-size:12px;
	background:white;
	coor: #333;
	padding:0; margin:0;
}
/**************************************/
.head{
	background:#333333;
}
.head .h1{
	width:1000px;
	margin:auto;
}
/**************************************/
.logo{
	color:#e5e5e5;
	font-family:"Times New Roman";
	font-size:37px;
	font-style:italic;
	position:relative;
	width: 450px; height:95px;
	font-weight:bold;
	text-shadow:2px 2px 2px #000;
	padding-left:10px;
}
.logo a{ text-decoration:none; color: #e5e5e5; }
.logo div{ position: absolute; }
.logo .l1{ left:0; top: 0; font-size:82px; }
.logo .l2{ left: 220px; top: 10px; }
.logo .l3{ left: 220px; top: 45px; font-size:20px; }
.logo .l4{ left: 260px; top: 40px; }
/**************************************/
.logo2{
	color:#e5e5e5;
	font-family:"Times New Roman";
	font-size:17px;
	font-style:italic;
	position:relative;
	width: 250px; height:50px;
	font-weight:bold;
	text-shadow:2px 2px 2px #000;
	padding-left:10px;
}
.logo2 a{ text-decoration:none; color: #e5e5e5; }
.logo2 div{ position: absolute; }
.logo2 .l1{ left: 0; top: 0; font-size:42px; }
.logo2 .l2{ left: 120px; top: 8px; }
.logo2 .l3{ left: 120px; top: 20px; font-size:12px; }
.logo2 .l4{ left: 140px; top: 23px; }
/**************************************/
.holder{
	position:relative;
	width:1000px; height:560px;
	overflow:hidden;
	margin:auto;
}
.holder .pr{
	position:absolute;
	top:100%; left:0;
}
.holder .pr.current{
	top:0;
}
.buyHolder{
	transition-duration: 0.5s;
	position:absolute;
	right: -370px;; top: 0; bottom: 0;
	z-index:2;
}
.holder:hover .buyHolder{
	transition-duration: 0.2s;
	right: 0;
}
.selector{
	transition-duration: 0.2s;
	position:relative;
	height:80px; width:450px;
	padding:5px 10px;
	text-shadow:1px 1px 0px rgba(0, 0, 0, 0.5);
	color:white;
	background:rgba(0, 0, 0, 0.3);
}
.selector:hover, .selector.current{
	transition-duration: 0.2s;
	background:rgba(0, 0, 0, 0.6);
}
.selector a{
	color:white;
	text-decoration:none;
}
.selector .title{
	position:absolute;
	left: 100px; top: 10px;
}
.selector .s1{
	position: relative;
	display:inline-block;
	*display: inline;
	zoom: 1;
	cursor:pointer;
	
	width:80px; height:80px;
	background:#eee;
	overflow:hidden;
}
.selector .s2{
	position:absolute;
	left: 100px; top: 50px;
}
.selector .s2 div{
	position: relative;
	display:inline-block;
	*display: inline;
	zoom: 1;
	cursor:pointer;

	width:35px; height:35px;
	background:#eee;
	float:left; margin-right:5px;
	overflow:hidden;
}
.selector .s1.current:before,
.selector .s2 div.current:before{
	position:absolute;
	content: " ";
	display:block;
	border:dotted 1px #fff;
	top: 0; left: 0; right: 0; bottom: 0;
}
.selector .s1 img, .selector .s2 img{
	width:100%;
	height:100%;
	border:0;
	display:block;
}
.selector h2{
	margin:0;
	font-size: 18px;
}
.holder .btn{
	z-index:2;
	position:absolute;
	border-radius:10px;
	padding:15px 10px 0 30px;
	width: 350px; height:70px;
	color:white;
	text-shadow:1px 1px 0px rgba(0, 0, 0, 0.5);
}
.holder .btn h3{
	font-size:20px;
	margin:0; font-weight:normal;
}
.holder .btn a{
	color:white;
}
.holder .buyButton{
	background:#004001;
	right:0; bottom: 105px;
}
.holder .helpButton{
	background:#333333;
	right:0; bottom: 10px;
}
/**************************************/
.body{
	width:1000px;
	margin:auto;
	padding:20px 0;
}
/**************************************/
.copyright{
	background:#333333;
	color:white;
	margin:20px 0;
}
.copyright .ctx{
	width:1000px;
	margin:auto;
	padding:10px 0;
}
.copyright .hr{
	border-top:solid 1px #eee;
}
/***************************/
.buyForm{
	display:none;
	position:absolute;
	height: 300px; top: 30%; margin-top:-150px;
	width:600px; left:50%; margin-left:-300px;
}
.buyForm h2{
	margin:0;
	font-size:28px; font-weight:normal;
	color: #fff;
}
.buyClose{
	position:absolute;
	right: 0; top: 0;
	border:solid 1px #888;
	background:white; color:#888;
	padding:2px 7px;
	border-radius: 4px;
	font-weight:bold;
}
.buyClose a{
	color:#888;
	text-decoration:none;
}
.buyForm form{
	text-shadow:1px 1px 1px #ccc;
	padding:15px;
	display:block;
	background:white;
	border-radius:10px;
}
</style>
</head>

<body>
{admin}
<div class="head">
  <div class="h1">
    <div class="logo">
      <div class="l1"><a href="{{url}}">M4P</a></div>
      <div class="l2">Мебель</div>
      <div class="l3">для</div>
      <div class="l4">Человека</div>
    </div>
  </div>
</div>
<!-- TemplateBeginEditable name="body" -->
<div class="buyForm">
  <div class="buyClose"><a href="#">X</a></div>
  <h2>Оформление заказа:</h2>
  <form method="post" action="{{url:buy}}">
    <input type="submit" class="button" value="Заказать" />
  </form>
</div>
{{script:jq}}
{{script:overlay}}
<script>
$(function(){
	$(".selector .s1, .selector .s2 div")
		.hover(selector);
});
function selector(){
		var id = $(this).attr("id");
		if (!id) return;
		$(".holder .pr, .selector .s1, .selector .s2 div")
			.removeClass("current");
		$("div#" + id)
			.addClass("current");
}
</script>
<div class="holder"> {{doc:read:holder=prop.!place:holder}}
  <div class="pr current" id="p1e1"> <img src="../_sites/m4p/design/p1e1.jpg" width="1000" height="561"  alt="" border="0" /> </div>
  <div class="pr" id="p1e2"> <img src="../_sites/m4p/design/p1e2.jpg" width="1000" height="561"  alt="" border="0" /> </div>
  <div class="pr" id="p1e3"> <img src="../_sites/m4p/design/p1e3.jpg" width="1000" height="561"  alt="" border="0" /> </div>
  <div class="pr" id="p1e4"> <img src="../_sites/m4p/design/p1e4.jpg" width="1000" height="561"  alt="" border="0" /> </div>
  <div class="pr" id="p1e5"> <img src="../_sites/m4p/design/p1e5.jpg" width="1000" height="561"  alt="" border="0" /> </div>
  <div class="pr" id="p2e1"> <img src="../_sites/m4p/design/p2e1.jpg" width="1000" height="561"  alt="" border="0" /> </div>
  <div class="buyButton btn">
    <h3><a href="">КУПИТЬ</a></h3>
    Мы предварительно позвоним вам<br>
    для согласования времени доставки </div>
  <div class="helpButton btn">
    <h3><a href="">ИНСТУКЦИЯ</a></h3>
    Это очень легко, удобно и комфортно </div>
</div>
<div class="body">{{display}}</div>
<!-- TemplateEndEditable -->
<div class="copyright">
	<div class="ctx">
        <div class="logo2">
            <div class="l1"><a href="{{url}}">M4P</a></div>
            <div class="l2">Мебель</div>
            <div class="l3">для</div>
            <div class="l4">Человека</div>
        </div>
        <div class="hr"></div>
        (c) 2014
    </div>
</div>
</body>
</html>