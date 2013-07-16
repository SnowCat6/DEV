<? function findTour_tez()
{
	$formURL	= getURLEx('findtour_order_tez');
?>
    <script type="text/javascript">
        function showteztourSearch() {
            var path = 'http://json.tez-tour.com/static/ats/';
            var now = new Date();
            var dateTo = new Date();
            dateTo.setDate(now.getDate()+7);
            var monthes = ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
            var teztourSearchSettings = {
                "fromCountryId":[1102],
                "fromCityId":[2729],
                "toCountryId":1104,
                "departureDateMin":( now.getDate() < 10 ? "0"+now.getDate() : now.getDate() )+"."+monthes[now.getMonth()]+"."+now.getFullYear(),
                "departureDateMax":( dateTo.getDate() < 10 ? "0"+dateTo.getDate() : dateTo.getDate() )+"."+monthes[dateTo.getMonth()]+"."+dateTo.getFullYear(),
                "nightsMin":7,
                "nightsMax":15,
                "nightsLimits":[2,20],
                "adults":2,
                "adultsLimits":[1,12],
                "children":0,
                "childrenLimits":[0,12],
                "childrenBirthday":[],
                "priceMin":0,
                "priceMax":9999,
                "currency":5561,
                "findByPrice":true,
                "tourId":[1285],
                "hotelClassId":[9006279, 9006280, 9006281],
                "feedId":[9006288, 9006289],
                "hotelId":[0],
                "hotelInStop":false,
                "noTicketsTo":false,
                "noTicketsFrom":false,
                "locale":"ru",
                "partnerLink":"<? if(isset($formURL)) echo $formURL ?>"
            }
            var JSON=window.JSON||{};JSON.stringify=JSON.stringify||function(obj){var t=typeof(obj);if(t!="object"||obj===null){if(t=="string")obj='"'+obj+'"';return String(obj);}else{var n,v,json=[],arr=(obj&&obj.constructor==Array);for(n in obj){v=obj[n];t=typeof(v);if(t=="string")v='"'+v+'"';else if(t=="object"&&v!==null)v=JSON.stringify(v);json.push((arr?"":'"'+n+'":')+String(v));}return(arr?"[":"{")+String(json)+(arr?"]":"}");}};var url=path+'search_'+teztourSearchSettings.locale+'.html';return('<iframe id="teztourSearchFrame" width="908" height="464" marginwidth="0" marginheight="0" frameborder="0" scrolling="no" name='+JSON.stringify(teztourSearchSettings)+' src="'+url+'"></iframe>');
        };
    </script>

    <div id="teztourSearch" style="width:908px;height:464px;"><script type="text/javascript">document.write(showteztourSearch());</script></div>
<? } ?>
