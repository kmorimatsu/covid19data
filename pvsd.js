/********************************************
*    Open source code written by Katsumi    *
*          This script is released          *
*            under the LGPL v2.1.           *
*  http://hp.vector.co.jp/authors/VA016157/ *
*  https://github.com/kmorimatsu            *
********************************************/

draw_graphs=function(viewPercent){
	this.viewPercent = viewPercent===undefined ? this.viewPercent:viewPercent;
	var dlabel=analyze_pvsd();
	var yAxes={
		ticks:{
			min: 0,
			callback: function(value, index, values){
				return  value + '人';
			}
		}
	};
	draw_graph_2('pvsd',yAxes,prefdata['pname']+'の陽性者数と死者数の比較',{type:8,name:dlabel},{type:4,name:'一日の陽性者数７日平均'},this.viewPercent);
};

analyze_pvsd=function(){
	var i;
	if (document.getElementById('daybefore').value.length && document.getElementById('mulnum').value.length) {
		var mul=parseInt(document.getElementById('mulnum').value);
		var diff=parseInt(document.getElementById('daybefore').value);
	} else {
		var maxPval=1;
		var maxDval=1;
		var maxPpos=0;
		var maxDpos=0;
		// Deterine peak numbers and dates
		for(i=0;i<prefdata['data'].length;i++){
			if (maxPval<prefdata['data'][i][4]) {
				maxPval=prefdata['data'][i][4];
				maxPpos=i;
			}
			if (maxDval<prefdata['data'][i][5]) {
				maxDval=prefdata['data'][i][5];
				maxDpos=i;
			}
			// Clear previous data
			prefdata['data'][i][8]=undefined;
		}
		// Construct 8th column as shifted and nultiplied numbers of death
		var mul=parseInt(maxPval/maxDval + 0.5);
		var diff=maxDpos-maxPpos;
		document.getElementById('mulnum').value=mul.toString();
		document.getElementById('daybefore').value=diff.toString();
	}
	for(i=0;i<prefdata['data'].length-diff;i++){
		if (prefdata['data'][i+diff] && prefdata['data'][i]) prefdata['data'][i][8]=prefdata['data'][i+diff][5]*mul;
	}
	return diff.toString()+'日後の死者数７日平均x'+mul.toString();
};