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
	draw_graph_2('graph1',yAxes,prefdata['pname']+'の一日の陽性者数推移',{type:4,name:'一日の陽性者数７日平均'},viewPercent);
	draw_graph_2('graph2',yAxes,prefdata['pname']+'の一日のPCR検査数推移',{type:5,name:'一日のPCR検査数７日平均'},viewPercent);
	var yAxes={
		ticks:{
			min: 0,
			callback: function(value, index, values){
				return  value + '%';
			}
		}
	};
	draw_graph_2('graph3',yAxes,prefdata['pname']+'の陽性率推移',{type:6,name:'陽性率７日平均'},viewPercent,graphMax ? graphMax:35);
};

draw_graph_2=function(canvas_name,yAxes,title,data1,viewPercent,ymax){
	var d1=new Array();
	var lbl=new Array();
	var start=0;
	if (0==viewPercent) {
		start=prefdata['data'].length-7;
	} else if (0<viewPercent && viewPercent<=100) {
		start=parseInt(prefdata['data'].length*(100-viewPercent)/100);
	}
	for(var i=start;i<prefdata['data'].length;i++){
		d1[i-start]=prefdata['data'][i][data1.type];
		lbl[i-start]=prefdata['labels'][i].substring(5,prefdata['labels'][i].length);
	}
	var ctx = document.getElementById(canvas_name);
	var setting= {
		type: 'line',
		data: {
			labels: lbl,
				datasets: [
				{
					label:data1.name,
					data: d1,
					borderColor: "rgba(0,0,255,1)",
					backgroundColor: "rgba(0,0,0,0)"
				}
			],
		},
		options: {
			aspectRatio: aspectRatio,
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
	};
	if (ymax !== undefined) setting.options.scales.yAxes[0].ticks.max=ymax;
	var myLineChart = new Chart(ctx, setting);
};
