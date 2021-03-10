/********************************************
*    Open source code written by Katsumi    *
*          This script is released          *
*            under the LGPL v2.1.           *
*  http://hp.vector.co.jp/authors/VA016157/ *
*  https://github.com/kmorimatsu            *
********************************************/

prefp=new Array();
prefp['北海道']=   5310559;
prefp['青森県']=   1262823;
prefp['岩手県']=   1240522;
prefp['宮城県']=   2313215;
prefp['秋田県']=    980684;
prefp['山形県']=   1089806;
prefp['福島県']=   1862705;
prefp['茨城県']=   2882943;
prefp['栃木県']=   1952926;
prefp['群馬県']=   1949440;
prefp['埼玉県']=   7322645;
prefp['千葉県']=   6268585;
prefp['東京都']=  13843403;
prefp['神奈川県']= 9179835;
prefp['新潟県']=   2245057;
prefp['富山県']=   1050246;
prefp['石川県']=   1142965;
prefp['福井県']=    773731;
prefp['山梨県']=    818391;
prefp['長野県']=   2063865;
prefp['岐阜県']=   1999406;
prefp['静岡県']=   3656487;
prefp['愛知県']=   7539185;
prefp['三重県']=   1790376;
prefp['滋賀県']=   1412881;
prefp['京都府']=   2591779;
prefp['大阪府']=   8824566;
prefp['兵庫県']=   5483450;
prefp['奈良県']=   1340070;
prefp['和歌山県']=  934051;
prefp['鳥取県']=    560517;
prefp['島根県']=    679626;
prefp['岡山県']=   1899739;
prefp['広島県']=   2819962;
prefp['山口県']=   1368495;
prefp['徳島県']=    736475;
prefp['香川県']=    961900;
prefp['愛媛県']=   1351510;
prefp['高知県']=    705880;
prefp['福岡県']=   5111494;
prefp['佐賀県']=    819110;
prefp['長崎県']=   1351249;
prefp['熊本県']=   1756442;
prefp['大分県']=   1142943;
prefp['宮崎県']=   1079727;
prefp['鹿児島県']= 1613969;
prefp['沖縄県']=   1448101;

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
			'death':data[i]['data'][lastnum][3],
			'deathpp':(100000*data[i]['data'][lastnum][3]/prefp[data[i]['pname']]).toFixed(2),
			'mortality':(100*data[i]['data'][lastnum][3]/data[i]['data'][lastnum][1]).toFixed(2)
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
	// Sort array for mortality
	gdata.sort(function(a,b){ return b.mortality-a.mortality; });
	labels=new Array();
	data2=new Array();
	for(i=0;i<gdata.length;i++){
		labels.push(gdata[i]['pref']);
		data2.push(gdata[i]['mortality']);
	}
	draw_bargraph('bargraph7',labels,date+'までの死亡率','陽性者100人当たりの死者数',data2);
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
