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
	header('Location: data.js?t='.filemtime('./data.js'));
	exit;
}
// Create JS file again
@file_put_contents('./data.js',makeJS());
header('Location: data.js?t='.filemtime('./data.js'));
exit;

function makeJS(){

	require('./openurl.php');

	// Get prefecture name array
	$prefnames=prefnames();
	// Get data from mhlw and convert it to NHK style
	$mhlw=openURL('https://covid19.mhlw.go.jp/public/opendata/newly_confirmed_cases_daily.csv');
	//$mhlw=file_get_contents('./newly_confirmed_cases_daily.csv');
	$mhlw=preg_replace("/^\xEF\xBB\xBF/",'',$mhlw); // Remove BOM
	$mhlw2=openURL('https://covid19.mhlw.go.jp/public/opendata/deaths_cumulative_daily.csv');
	//$mhlw2=file_get_contents('./deaths_cumulative_daily.csv');
	$mhlw2=preg_replace("@^[\s\S]*?[\r\n]2020/5/9,.*[\r\n]+@",'',$mhlw2); // Remove BOM and 2020/5/9 data
	$mhlw2=file_get_contents('./deaths_cumulative_daily_before220510.csv').$mhlw2; // Add data before 2020/5/9
	// Get pref table
	$table=preg_replace('/[\r\n][\s\S]*/','',$mhlw);
	foreach($prefnames as $a=>$n) $table=str_replace($a,$n,$table);
	$preftable=preg_split('/,/',preg_replace('/^Date,/','',$table));
	// Convert mhlw string to array
	$mhlw=preg_split('/[\r\n]+/',$mhlw);
	$mhlw2=preg_split('/[\r\n]+/',$mhlw2);
	// Remove header
	array_shift($mhlw);
	array_shift($mhlw2);
	$num=count($mhlw)<count($mhlw2) ? count($mhlw):count($mhlw2);
	// Create NHK style CSV
	$csv="日付,都道府県コード,都道府県名,";
	$csv.="各地の感染者数_1日ごとの発表数,各地の感染者数_累計,";
	$csv.="各地の死者数_1日ごとの発表数,各地の死者数_累計,\n";
	for($i=0;$i<count($preftable);$i++){
		$infect=0;
		$death=0;
		for($j=0;$j<$num;$j++){
			// Get prefecture data
			$query='/^([^,]*,){'.($i+1).'}([^,]*)/';
			if (!preg_match($query,$mhlw[$j],$m)) continue;
			if (!preg_match($query,$mhlw2[$j],$m2)) continue;
			// The line starts with date
			$line=preg_replace('/,.*$/',',',$mhlw[$j]);
			// Add prefecture informatin
			$line.=$i<10 ? "0$i,":"$i,";
			$line.=$preftable[$i].',';
			// Add infection data
			$line.=$m[2].',';
			$infect+=$m[2];
			$line.=$infect.',';
			// Add death data
			$line.=($m2[2]-$death).','.$m2[2].',';
			$death=$m2[2];
			// Update csv
			$csv.="$line\n";
		}
	}

	// Prepare arrays
	$plist=array();
	$data=array();
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
	$js.="  The data was fetched from https://www.mhlw.go.jp/stf/covid-19/open-data.html\n";
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

function prefnames(){
	$prefnames=array();
	$prefnames['ALL']='全国';
	$prefnames['Hokkaido']='北海道';
	$prefnames['Aomori']='青森県';
	$prefnames['Iwate']='岩手県';
	$prefnames['Miyagi']='宮城県';
	$prefnames['Akita']='秋田県';
	$prefnames['Yamagata']='山形県';
	$prefnames['Fukushima']='福島県';
	$prefnames['Ibaraki']='茨城県';
	$prefnames['Tochigi']='栃木県';
	$prefnames['Gunma']='群馬県';
	$prefnames['Saitama']='埼玉県';
	$prefnames['Chiba']='千葉県';
	$prefnames['Tokyo']='東京都';
	$prefnames['Kanagawa']='神奈川県';
	$prefnames['Niigata']='新潟県';
	$prefnames['Toyama']='富山県';
	$prefnames['Ishikawa']='石川県';
	$prefnames['Fukui']='福井県';
	$prefnames['Yamanashi']='山梨県';
	$prefnames['Nagano']='長野県';
	$prefnames['Gifu']='岐阜県';
	$prefnames['Shizuoka']='静岡県';
	$prefnames['Aichi']='愛知県';
	$prefnames['Mie']='三重県';
	$prefnames['Shiga']='滋賀県';
	$prefnames['Kyoto']='京都府';
	$prefnames['Osaka']='大阪府';
	$prefnames['Hyogo']='兵庫県';
	$prefnames['Nara']='奈良県';
	$prefnames['Wakayama']='和歌山県';
	$prefnames['Tottori']='鳥取県';
	$prefnames['Shimane']='島根県';
	$prefnames['Okayama']='岡山県';
	$prefnames['Hiroshima']='広島県';
	$prefnames['Yamaguchi']='山口県';
	$prefnames['Tokushima']='徳島県';
	$prefnames['Kagawa']='香川県';
	$prefnames['Ehime']='愛媛県';
	$prefnames['Kochi']='高知県';
	$prefnames['Fukuoka']='福岡県';
	$prefnames['Saga']='佐賀県';
	$prefnames['Nagasaki']='長崎県';
	$prefnames['Kumamoto']='熊本県';
	$prefnames['Oita']='大分県';
	$prefnames['Miyazaki']='宮崎県';
	$prefnames['Kagoshima']='鹿児島県';
	$prefnames['Okinawa']='沖縄県';
	return $prefnames;
}