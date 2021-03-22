/********************************************
*    Open source code written by Katsumi    *
*          This script is released          *
*            under the LGPL v2.1.           *
*  http://hp.vector.co.jp/authors/VA016157/ *
*  https://github.com/kmorimatsu            *
********************************************/

draw_graphs=function(viewPercent){
	var i;
	var labels=new Array();
	var data2=new Array();
	var lastnum=data[1]['labels'].length-1;
	var date=data[1]['labels'][lastnum];
	var gdata=new Array();
	for(i=1;i<data.length;i++){
		gdata.push({
			'pref':data[i]['pname'],
			'positives':data[i]['data'][lastnum][1],
			'positivespp':(100000*data[i]['data'][lastnum][1]/prefp[data[i]['pname']]).toFixed(2),
			'positivesppw':(100000*(data[i]['data'][lastnum][1]-data[i]['data'][lastnum-7][1])/prefp[data[i]['pname']]).toFixed(2),
			'death':data[i]['data'][lastnum][3],
			'deathpp':(100000*data[i]['data'][lastnum][3]/prefp[data[i]['pname']]).toFixed(2),
			'deathppm':(100000*(data[i]['data'][lastnum][3]-data[i]['data'][lastnum-28][3])/prefp[data[i]['pname']]).toFixed(2),
			'mortality':(100*data[i]['data'][lastnum][3]/data[i]['data'][lastnum][1]).toFixed(2),
			'mortalitym':(100*(data[i]['data'][lastnum][3]-data[i]['data'][lastnum-28][3])/(data[i]['data'][lastnum-24][1]-data[i]['data'][lastnum-24-28][1])).toFixed(2)
		});
	}
	// Sort array for positives
	gdata.sort(function(a,b){ return b.positives-a.positives; });
	for(i=0;i<gdata.length;i++){
		labels.push(gdata[i]['pref']);
		data2.push(gdata[i]['positives']);
	}
	draw_bargraph('bargraph1',labels,date+'までの累積感染者数','感染者数',data2);
	draw_bargraph('bargraph2',labels,date+'までの累積感染者数対数表示','感染者数',data2,1);
	// Sort array for positives per population
	gdata.sort(function(a,b){ return b.positivespp-a.positivespp; });
	labels=new Array();
	data2=new Array();
	for(i=0;i<gdata.length;i++){
		labels.push(gdata[i]['pref']);
		data2.push(gdata[i]['positivespp']);
	}
	draw_bargraph('bargraph3',labels,date+'までの累積感染者数','10万人当たりの感染者数',data2);
	// Sort array for positives in last week per population
	gdata.sort(function(a,b){ return b.positivesppw-a.positivesppw; });
	labels=new Array();
	data2=new Array();
	for(i=0;i<gdata.length;i++){
		labels.push(gdata[i]['pref']);
		data2.push(gdata[i]['positivesppw']);
	}
	draw_bargraph('bargraph3.5',labels,date+'までの直近7日間の感染者数','10万人当たりの感染者数',data2);
	// Sort array for death
	gdata.sort(function(a,b){ return b.death-a.death; });
	labels=new Array();
	data2=new Array();
	for(i=0;i<gdata.length;i++){
		labels.push(gdata[i]['pref']);
		data2.push(gdata[i]['death']);
	}
	draw_bargraph('bargraph4',labels,date+'までの累積死者数','死者数',data2);
	draw_bargraph('bargraph5',labels,date+'までの累積死者数対数表示','死者数',data2,1);
	// Sort array for death per population
	gdata.sort(function(a,b){ return b.deathpp-a.deathpp; });
	labels=new Array();
	data2=new Array();
	for(i=0;i<gdata.length;i++){
		labels.push(gdata[i]['pref']);
		data2.push(gdata[i]['deathpp']);
	}
	draw_bargraph('bargraph6',labels,date+'までの累積死者数','10万人当たりの死者数',data2);
	// Sort array for death in 28 days per population
	gdata.sort(function(a,b){ return b.deathppm-a.deathppm; });
	labels=new Array();
	data2=new Array();
	for(i=0;i<gdata.length;i++){
		labels.push(gdata[i]['pref']);
		data2.push(gdata[i]['deathppm']);
	}
	draw_bargraph('bargraph6.5',labels,date+'までの直近28日間の死者数','10万人当たりの死者数',data2);
	// Sort array for mortality
	gdata.sort(function(a,b){ return b.mortality-a.mortality; });
	labels=new Array();
	data2=new Array();
	for(i=0;i<gdata.length;i++){
		labels.push(gdata[i]['pref']);
		data2.push(gdata[i]['mortality']);
	}
	draw_bargraph('bargraph7',labels,date+'までの死亡率','陽性者100人当たりの死者数',data2);
	// Sort array for mortality in last month
	gdata.sort(function(a,b){ return b.mortalitym-a.mortalitym; });
	labels=new Array();
	data2=new Array();
	for(i=0;i<gdata.length;i++){
		labels.push(gdata[i]['pref']);
		data2.push(gdata[i]['mortalitym']);
	}
	draw_bargraph('bargraph7.5',labels,date+'までの直近28日間の死亡率','陽性者100人当たりの死者数',data2);
};

draw_bargraph=function(id,labels,title,name,data,log){
	var ctx = document.getElementById(id);
	var myBarChart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: labels,
			datasets: [
				{
					label: name,
					data: data,
					backgroundColor: "rgba(219,39,91,0.5)"
				}
			]
		},
		options: {
			title: {
				display: true,
				text: title
			},
			scales: {
				yAxes: [{
					type: (log ? 'logarithmic':'linear'),
					ticks: {
						Min: 0,
						callback: function(value, index, values){
							return value + '人';
						}
					}
				}],
				xAxes: [{
					ticks: {
						autoSkip:false,
						maxRotation: 80,
						minRotation: 80
					}
				}]
			},
		    chartArea: {
		        backgroundColor: 'rgba(230, 238, 255, 0.6)'
		    },
		}
	});
};
