/********************************************
*    Open source code written by Katsumi    *
*          This script is released          *
*            under the LGPL v2.1.           *
*  http://hp.vector.co.jp/authors/VA016157/ *
*  https://github.com/kmorimatsu            *
********************************************/

saveCSV=function(obj){
	var csv="日付,一日の陽性者数,陽性者累積数,一日の死者数,死者累積数,一日の陽性者数7日平均,一日の死者数7日平均,実効再生産数,実効再生産数速報値\r\n";
	for(var i=0;i<prefdata['data'].length;i++){
		csv+=prefdata['labels'][i]+",";
		csv+=prefdata['data'][i][0]+",";
		csv+=prefdata['data'][i][1]+",";
		csv+=prefdata['data'][i][2]+",";
		csv+=prefdata['data'][i][3]+",";
		csv+=prefdata['data'][i][4]+",";
		csv+=prefdata['data'][i][5]+",";
		csv+=prefdata['data'][i][6]+",";
		csv+=prefdata['data'][i][7]+"\r\n";
	}
	obj.download="covid19_data_"+prefdata['pname']+".csv";
	obj.href="data:application/csv;charset=utf-8,"+encodeURIComponent("\uFEFF"+csv);
	obj.click();
};
