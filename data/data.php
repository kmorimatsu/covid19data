<?php
/********************************************
*    Open source code written by Katsumi    *
*          This script is released          *
*            under the LGPL v2.1.           *
*  http://hp.vector.co.jp/authors/VA016157/ *
*  https://github.com/kmorimatsu            *
********************************************/

while(1){
	// If the JS file doesn't exist, break;
	if (!file_exists('./data.js')) break;
	// If the JS file was created more than an hour ago, break;
	if (filemtime('./data.js')+3600<time()) break;
	// Rediect
	header('Location: data.js');
	exit;
}
// Create JS file again
@file_put_contents('./data.js',makeJS());
header('Location: data.js');
exit;

function makeJS(){
	// Prepare arrays
	$plist=array();
	$data=array();
	// Read csv files from NHK site
	$csv=file_get_contents('http://www3.nhk.or.jp/n-data/opendata/coronavirus/nhk_news_covid19_domestic_daily_data.csv');
	$csv=preg_replace('/(202[^,]+),/','$1,00,全国,',$csv);
	$csv.=file_get_contents('http://www3.nhk.or.jp/n-data/opendata/coronavirus/nhk_news_covid19_prefectures_daily_data.csv');
	// Check the csv file and prepare result array as $m
	preg_match_all('/(202[0-9\\/]+),([0-9]+),([^,]+),([0-9]+,[0-9]+,[0-9]+,[0-9]+)/',$csv,$m);
	$num=count($m[0]);

	for($i=0;$i<$num;$i++){
		// Fetch information
		// Note that sanitizing of dara is done here.
		// I believe NHK web constructer, but the site may be cracked.
		$dat=htmlentities($m[1][$i],ENT_QUOTES,'UTF-8');
		$pnum=(int)$m[2][$i];
		$pname=htmlentities($m[3][$i],ENT_QUOTES,'UTF-8');
		$nums=explode(',',$m[4][$i]); // This contains numbers only. See regular expression.
		$plist[$pnum]=$pname;
		
		if (empty($data[$pnum])) {
			// Begin new prefecture
			// Prepare arrays
			$data[$pnum]=array();
			$inf=array(0,0,0,0,0,0,0); // Infected
			$dea=array(0,0,0,0,0,0,0); // Death
			$avrinf=array(0,0,0,0,0);  // 7 days average of infection used for effective reproduction number
		}
		
		// Prepare numbers
		// $nums[0]: positive/day, $nums[1]: positive accumulated, $nums[2]: death/day, $nums[3]: death accumulated
		// $nums[4]: 7 day avarage of positive/day, $nums[5]: 7 day avarage of death/day
		// $nums[6]: Effective reproduction number, $nums[7]: Preliminary ERN
		$inf[]=$nums[0];
		$dea[]=$nums[2];
		$inf7=array_shift($inf);                                                      // # of infection 7 days ago
		array_shift($dea);
		$nums[]=round(($inf[0]+$inf[1]+$inf[2]+$inf[3]+$inf[4]+$inf[5]+$inf[6])/7,2); // 7 day avarage of infection
		$nums[]=round(($dea[0]+$dea[1]+$dea[2]+$dea[3]+$dea[4]+$dea[5]+$dea[6])/7,2); // 7 day avarage of death
		$nums[]=$avrinf[0] ? round($nums[4]/$avrinf[0],2) : -1;                       // Effective reproduction number
		$avrinf[]=$nums[4];
		array_shift($avrinf);
		$nums[]=$inf7 ? round(pow($nums[0]/$inf7,5/7),2) : -1;                        // Preliminary ERN
		
		// Update data
		$data[$pnum][$dat]=$nums;
	}

	// Construct JavaScript
	$js="/*\n";
	$js.="  The data was fetched from https://www3.nhk.or.jp/news/special/coronavirus/\n";
	$js.="  , modified and converted to Javascript code.\n";
	$js.="*/\n";
	$js.="var data=new Array();\n";
	for($i=0;$i<=47;$i++){
		$js.="data[$i]=new Array();\n";
		$js.="data[$i]['pname']='$plist[$i]';\n";
		$js.="data[$i]['labels']=new Array();\n";
		$js.="data[$i]['data']=new Array();\n";
		$j=0;
		foreach($data[$i] as $dat=>$nums){
			$js.="data[$i]['labels'][$j]='$dat';\n";
			$js.="data[$i]['data'][$j]=[".implode(',',$nums)."];\n";
			$j++;
		}
	}
	return $js;
}
