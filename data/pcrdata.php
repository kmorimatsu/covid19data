<?php
/********************************************
*    Open source code written by Katsumi    *
*          This script is released          *
*            under the LGPL v2.1.           *
*  http://hp.vector.co.jp/authors/VA016157/ *
*  https://github.com/kmorimatsu            *
********************************************/

/*
	Strategy to convert NEC CSV data to Javascript data.
	
	CSV data header:
		1  公表_年月日
		2  集計_年月日
		3  全国地方公共団体コード
		4  都道府県コード
		5  都道府県名
		6  PrefectureName
		7  PrefectureCode
		8  PCR検査陽性者
		9  PCR検査実施人数
		10 入院治療等を要する者
		11 うち重症
		12 退院又は療養解除となった者の数
		13 死亡（累積）
		14 確認中
	                           1       2     3      4       5      6     7      8       9      10    11    12     13    14
	By using a regexp'/(202[^,\r\n]+),[^,]*,[^,]*,([^,]*),([^,]*),[^,]*,[^,]*,([^,]*),([^,]*),[^,]*,[^,]*,[^,]*,([^,]*),/'
		$m[1]: 公表_年月日
		$m[2]: 都道府県コード (1-47, 48:その他 49:空港検疫 50:チャーター便)
		$m[3]: 都道府県名
		$m[4]: PCR検査陽性者
		$m[5]: PCR検査実施人数
		$m[6]: 死亡（累積）
	
	When pref code of 50 appears, make sums for the numbers as the data of all Japan.
	
	Following values will be calculated
		// $nums[0]: positives/day
		// $nums[1]: positives accumulated
		// $nums[2]: PCR/day
		// $nums[3]: PCR accumulated
		// $nums[4]: 7 day avarage of positive/day
		// $nums[5]: 7 day avarage of PCR/day
		// $nums[6]: 7 day avarage of positive %

*/

while(1){
	// If the JS file doesn't exist, break;
	if (!file_exists('./pcrdata.js')) break;
	// If the JS file was created more than an hour ago, break;
	if (filemtime('./pcrdata.js')+3600<time()) break;
	// Rediect
	header('Location: pcrdata.js');
	exit;
}
// Create JS file again
@file_put_contents('./pcrdata.js',makeJS());
header('Location: pcrdata.js');
exit;

function makeJS(){
	// Prepare arrays
	$plist=array();
	$data=array();
	// Read csv files from NEC site
	//         Publish date               pref #                   # positive, #PCR ,            # death
	// Example: 2021/4/21,2021/4/20,470007,47,沖縄県,okinawa,47_okinawa,11652,181529,1251,10,10277,130,6
	$csv=file_get_contents('https://covid-19.nec-solutioninnovators.com/download/japan_covid19.csv');
	//$csv=file_get_contents('./japan_covid19.csv');
	$csv=preg_replace_callback( // "xx,xxx" -> xxxxx replacement
		'/"([0-9,]+)"/',
		function ($m) {
			return str_replace(',','',$m[1]);
		},
		$csv
	);
	// Check the csv file and prepare result array as $m
	preg_match_all('/(202[^,\r\n]+),[^,]*,[^,]*,([^,]*),([^,]*),[^,]*,[^,]*,([^,]*),([^,]*),[^,]*,[^,]*,[^,]*,([^,]*),/',$csv,$m);
	// $1: date, $2: #pref, $3: pref name, $4: #positives, $5: #PCR, $6: #death,
	$num=count($m[0]);
	$positivesa=array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
	$pcra=array(0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0);
	$pcr=array();
	$inf=array();
	
	// $nums[0]: positives/day
	// $nums[1]: positives accumulated
	// $nums[2]: PCR/day
	// $nums[3]: PCR accumulated
	// $nums[4]: 7 day avarage of positive/day
	// $nums[5]: 7 day avarage of PCR/day
	// $nums[6]: 7 day avarage of positive %

	for($i=0;$i<$num;$i++){
		// Fetch information
		// Note that sanitizing of dara is done here.
		// I believe NEC web constructer, but the site may be cracked.
		$dat=htmlentities($m[1][$i],ENT_QUOTES,'UTF-8');
		$pnum=(int)$m[2][$i];
		$nums=array(0,0,0,0,0,0,0);
		switch($pnum){
			case 48: // Other
			case 49: // Airport
				continue;
			case 50: // Charter
				// End of 1-50 prefecture numbers.
				// Create Japan data here
				$pname='全国';
				$pnum=0;
				$nums[1]=0; // positives
				for($j=$i-49;$j<=$i;$j++) $nums[1]+=(int)$m[4][$j];
				$nums[3]=0; // PCR
				for($j=$i-49;$j<=$i;$j++) $nums[3]+=(int)$m[5][$j];
				break;
			default: // Prefectures
				$pname=htmlentities($m[3][$i],ENT_QUOTES,'UTF-8');
				// Accumlated number must increase
				if (50<=$i) {
					if ($m[4][$i]<$m[4][$i-50]) $m[4][$i]=$m[4][$i-50];
					if ($m[5][$i]<$m[5][$i-50]) $m[5][$i]=$m[5][$i-50];
				}
				$nums[1]=(int)$m[4][$i]; // positives
				$nums[3]=(int)$m[5][$i]; // PCR
				break;
		}
		$plist[$pnum]=$pname;
		// positive/day
		$nums[0]=$nums[1]-$positivesa[$pnum];
		$positivesa[$pnum]=$nums[1];
		// PCR/day
		$nums[2]=$nums[3]-$pcra[$pnum];
		$pcra[$pnum]=$nums[3];
		
		if (empty($data[$pnum])) {
			// Begin new prefecture
			// Prepare arrays
			$data[$pnum]=array();
			$inf[$pnum]=array(0,0,0,0,0,0,0); // Infected
			$pcr[$pnum]=array(0,0,0,0,0,0,0); // PCR
		}
		
		// Prepare numbers
		// $nums[0]: positives/day
		// $nums[1]: positives accumulated
		// $nums[2]: PCR/day
		// $nums[3]: PCR accumulated
		// $nums[4]: 7 day avarage of positive/day
		// $nums[5]: 7 day avarage of PCR/day
		// $nums[6]: 7 day avarage of positive %
		$inf[$pnum][]=$nums[0];
		$pcr[$pnum][]=$nums[2];
		array_shift($inf[$pnum]);
		array_shift($pcr[$pnum]);
		for($j=$nums[4]=0;$j<7;$j++) $nums[4]+=$inf[$pnum][$j];
		$nums[4]=round($nums[4]/7,2);                           // 7 day avarage of infection
		for($j=$nums[5]=0;$j<7;$j++) $nums[5]+=$pcr[$pnum][$j];
		$nums[5]=round($nums[5]/7,2);                           // 7 day avarage of PCR
		$nums[6]=$nums[5] ? round(100*$nums[4]/$nums[5],2) : 0; // 7 day avarage of positive% in PCR
		
		// Update data
		$data[$pnum][$dat]=$nums;
	}

	// Construct JavaScript
	$js="/*\n";
	$js.="  The data was fetched from https://covid-19.nec-solutioninnovators.com/download/japan_covid19.csv\n";
	$js.="  , modified and converted to Javascript code.\n";
	$js.="*/\n";
	$js.="var pcrdata=new Array();\n";
	for($i=0;$i<=47;$i++){
		$js.="pcrdata[$i]=new Array();\n";
		$js.="pcrdata[$i]['pname']='$plist[$i]';\n";
		$js.="pcrdata[$i]['labels']=new Array();\n";
		$js.="pcrdata[$i]['data']=new Array();\n";
		$j=0;
		foreach($data[$i] as $dat=>$nums){
			$js.="pcrdata[$i]['labels'][$j]='$dat';\n";
			$js.="pcrdata[$i]['data'][$j]=[".implode(',',$nums)."];\n";
			$j++;
		}
	}
	return $js;
}
