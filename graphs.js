/********************************************
*    Open source code written by Katsumi    *
*          This script is released          *
*            under the LGPL v2.1.           *
*  http://hp.vector.co.jp/authors/VA016157/ *
*  https://github.com/kmorimatsu            *
********************************************/

draw_graphs=function(viewPercent){
	var yAxes={
		ticks:{
			min: 0,
			callback: function(value, index, values){
				return  value + '人';
			}
		}
	};
	draw_graph_2('positiveNums',yAxes,prefdata['pname']+'の一日の陽性者数推移',{type:0,name:'一日の陽性者数'},{type:4,name:'一日の陽性者数７日平均'},viewPercent);
	yAxes={
		type: 'logarithmic',
		ticks:{
			min: 0,
			callback: function(value, index, values){
				return  value + '人';
			}
		}
	};
	draw_graph_2('positiveNumsLog',yAxes,prefdata['pname']+'の一日の陽性者数推移対数表示',{type:0,name:'一日の陽性者数'},{type:4,name:'一日の陽性者数７日平均'},viewPercent);
	yAxes={
		ticks:{
			max: 3,
			min: 0,
			callback: function(value, index, values){
				return  value;
			}
		},
	};
	draw_graph_2('eRepNums',yAxes,prefdata['pname']+'の実行再生産数推移',{type:7,name:'実行再生産数速報値'},{type:6,name:'実行再生産数'},viewPercent);
	yAxes={
		ticks:{
			min: 0,
			callback: function(value, index, values){
				return  value + '人';
			}
		}
	};
	draw_graph_2('deathNums',yAxes,prefdata['pname']+'の死者数推移',{type:2,name:'一日の死者数'},{type:5,name:'一日の死者数７日平均'},viewPercent);
};

draw_graph_2=function(canvas_name,yAxes,title,data1,data2,viewPercent){
	var d1=new Array();
	var d2=new Array();
	var lbl=new Array();
	var start=0;
	if (0==viewPercent) {
		start=prefdata['data'].length-7;
	} else if (0<viewPercent && viewPercent<=100) {
		start=parseInt(prefdata['data'].length*(100-viewPercent)/100);
	}
	for(var i=start;i<prefdata['data'].length;i++){
		d1[i-start]=prefdata['data'][i][data1.type];
		d2[i-start]=prefdata['data'][i][data2.type];
		lbl[i-start]=prefdata['labels'][i].substring(5,prefdata['labels'][i].length);
	}
	var ctx = document.getElementById(canvas_name);
	var myLineChart = new Chart(ctx, {
		type: 'line',
		data: {
			labels: lbl,
				datasets: [
				{
					label: data2.name,
					data: d2,
					borderColor: "rgba(255,0,0,1)",
					backgroundColor: "rgba(0,0,0,0)"
				},
				{
					label:data1.name,
					data: d1,
					borderColor: "rgba(0,0,255,1)",
					backgroundColor: "rgba(0,0,0,0)"
				}
			],
		},
		options: {
			title: {
				display: true,
				text: title
			},
			scales: {
				yAxes: [yAxes]
			},
		    chartArea: {
		        backgroundColor: 'rgba(230, 238, 255, 0.6)'
		    },
		}
	});
};
