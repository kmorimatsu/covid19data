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
		if (!(data[i]['pname'])) break;
		lastnum=data[i]['labels'].length-1;
		gdata.push({
			'pref':data[i]['pname'],
			'positives':data[i]['data'][lastnum][1],
			'positivespp':parseFloat(0+(100000*data[i]['data'][lastnum][1]/prefp[data[i]['pname']]).toFixed(2)),
			'positivesppw':parseFloat(0+(100000*(data[i]['data'][lastnum][1]-data[i]['data'][lastnum-7][1])/prefp[data[i]['pname']]).toFixed(2)),
			'ern':parseFloat(0+data[i]['data'][lastnum][6]),
			'ern30':parseFloat(0+(data[i]['data'][lastnum][4]>=30 ? data[i]['data'][lastnum][6]:0)),
			'death':data[i]['data'][lastnum][3],
			'deathpp':parseFloat(0+(100000*data[i]['data'][lastnum][3]/prefp[data[i]['pname']]).toFixed(2)),
			'deathppm':parseFloat(0+(100000*(data[i]['data'][lastnum][3]-data[i]['data'][lastnum-28][3])/prefp[data[i]['pname']]).toFixed(2)),
			'mortality':parseFloat(0+(100*data[i]['data'][lastnum][3]/data[i]['data'][lastnum][1]).toFixed(2)),
			'mortalitym':parseFloat(0+(100*(data[i]['data'][lastnum][3]-data[i]['data'][lastnum-28][3])/(data[i]['data'][lastnum-24][1]-data[i]['data'][lastnum-24-28][1])).toFixed(2)),
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
		var updown='';
		if (gdata[i]['ern']<0.75) updown='↓↓↓';
		else if (gdata[i]['ern']<0.85) updown='↓↓';
		else if (gdata[i]['ern']<0.95) updown='↓';
		else if (gdata[i]['ern']>1.25) updown='↑↑↑';
		else if (gdata[i]['ern']>1.15) updown='↑↑';
		else if (gdata[i]['ern']>1.05) updown='↑';
		labels.push(updown+' '+gdata[i]['pref']);
		data2.push(gdata[i]['positivesppw']);
	}
	draw_bargraph('bargraph3.5',labels,date+'までの直近7日間の感染者数','10万人当たりの感染者数',data2);
	// Sort array for execution reproduction number (more than 30 positives)
	gdata.sort(function(a,b){ return b.ern30-a.ern30+(b.ern/1000-a.ern/1000); });
	labels=new Array();
	data2=new Array();
	for(i=0;i<gdata.length;i++){
		labels.push(gdata[i]['pref']);
		data2.push(gdata[i]['ern30']);
	}
	draw_bargraph('bargraph3.7',labels,date+'の実効再生産数比較（陽性者30人以上の州）','実効再生産数',data2,0,'');
	// Sort array for positives in last week per population
	gdata.sort(function(a,b){ return b.positivesppw-a.positivesppw; });
	labels=new Array();
	data2=new Array();
	for(i=0;i<gdata.length;i++){
		labels.push(gdata[i]['pref']);
		data2.push(gdata[i]['ern']);
	}
	draw_bargraph('bargraph3.75',labels,date+'の実効再生産数比較','実効再生産数（人口当たりの陽性者数順）',data2,0,'',5);
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
	draw_bargraph('bargraph7',labels,date+'までの死亡率','陽性者死亡率',data2,0,'%');
	// Sort array for mortality in last month
	gdata.sort(function(a,b){ return b.mortalitym-a.mortalitym; });
	labels=new Array();
	data2=new Array();
	for(i=0;i<gdata.length;i++){
		labels.push(gdata[i]['pref']);
		data2.push(gdata[i]['mortalitym']);
	}
	draw_bargraph('bargraph7.5',labels,date+'までの直近28日間の死亡率','陽性者死亡率',data2,0,'%');
};

draw_bargraph=function(id,labels,title,name,data,log,postfix,ymax){
	var ctx = document.getElementById(id);
	var setting={
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
			aspectRatio: aspectRatio,
			title: {
				display: true,
				text: title
			},
			scales: {
				yAxes: [{
					type: (log ? 'logarithmic':'linear'),
					ticks: {
						min: 0,
						callback: function(value, index, values){
							return value + (postfix === undefined ? '人':postfix);
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
	};
	if (ymax !== undefined) setting.options.scales.yAxes[0].ticks.max=ymax;
	var myBarChart = new Chart(ctx, setting);
};
