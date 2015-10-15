// JavaScript Document

$(function()
{
	var data = $("#renderByDays").attr("rel");
	if (data) data = $.parseJSON(data);
	
	if (data)
	$.jqplot('renderByDays', [ data['max'], data['min'], data['avg'] ], {
		title:	'Время выполнения ' + data['days'] + ' дней',
		cursor: {
			show: true,
			tooltipLocation:'sw', 
			zoom: true
		},
		axes:{
			xaxis:{
				renderer: $.jqplot.DateAxisRenderer,
				numberTicks: data['days'],
				tickOptions:{
					formatString:'%b %d'
          		} 
		  	},
			yaxis:{
				min:0,
				tickOptions:{
					formatString:'%f сек.'
          		}
		  	}
		},
		series:[
			{	label:'Максимум',
				highlighter:{formatString: "%2$.4f сек./стр."}
			},
			{	label:'Минимум',
				highlighter:{formatString: "%2$.4f сек./стр."}
			},
			{	label:'Среднее',
				highlighter:{formatString: "%2$.4f сек./стр."}
			}
		],
        legend: {
            show: true
        },
		highlighter:{
			show: true,
			sizeAdjust: 7.5,
			useAxesFormatters: false
		}
	});
});


$(function()
{
	var data = $("#visitorsByDays").attr("rel");
	if (data) data = $.parseJSON(data);
	
	if (data)
	$.jqplot('visitorsByDays', [ data['users'], data['views'] ], {
		title:	'Посещаемость за последние ' + data['days'] + ' дней',
		axes:{
			xaxis:{
				renderer: $.jqplot.DateAxisRenderer,
				numberTicks: data['days'],
				tickOptions:{
					formatString:'%b %d'
          		} 
		  	},
			yaxis:{
				min:0,
				tickOptions:{
					formatString:'%d чел.'
          		} 
		  	}
		},
		series:[
			{	label:'Посетителей',
				highlighter:{formatString: "%2$d чел./день"}
			},
			{	label:'Просмотров',
				highlighter:{formatString: "%2$d стр./день"}
			},
		],
        legend: {
            show: true
        },
		highlighter:{
			show: true,
			sizeAdjust: 7.5,
			useAxesFormatters: false
		}
	});
});

$(function()
{
	var data = $("#visitorsByHours").attr("rel");
	if (data) data = $.parseJSON(data);
	
	if (data)
	$.jqplot('visitorsByHours', [ data['hours'], data['hoursNow'] ], {
		title:	'Посещаемость за день по часам',
		axes:{
			xaxis:{
				min:0, max:23,
				numberTicks: 24,
				tickOptions:{formatString:'%d час'}
		  	},
			yaxis:{
				min:0,
				tickOptions:{formatString:'%d чел.'}
		  	}
		},
		series:[
			{label:'Вчера ' + data['title1']},
			{label:'Сегодня ' + data['title2']}
		],
        legend: {
            show: true
        },
		highlighter:{
			show: true,
			sizeAdjust: 7.5,
			formatString: "%2$d чел./час",
			useAxesFormatters: false
		}
	});
});
