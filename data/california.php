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
	if (!file_exists('./california.js')) break;
	// If the JS file was created more than an hour ago, break;
	if (filemtime('./california.js')+3600<time()) break;
	// Rediect
	header('Location: california.js');
	exit;
}
// Create JS file again
@file_put_contents('./california.js',makeJS());
header('Location: california.js');
exit;

function makeJS(){
	// Prepare arrays
	$plist=array();
	$data=array();
	// Read csv file from https://github.com/nytimes/covid-19-data
	//$csv=loadCSV('./us-counties.csv');
	$csv=loadCSV('https://raw.githubusercontent.com/nytimes/covid-19-data/master/us-counties.csv');
	//$csv2=loadCSV('./us-states.csv');
	$csv2=loadCSV('https://raw.githubusercontent.com/nytimes/covid-19-data/master/us-states.csv');
	// Rearrange CSV
	$csv=convertCSV($csv2,'(California),').convertCSV($csv,'([^,]+),California,');
	//file_put_contents('./result.csv',$csv);exit;
	// Check the csv file and prepare result array as $m
	//                 date        , area ,       cases    , c_cases  , deaths   , c_deaths
	preg_match_all('/(202[^,\r\n]+),([^,\r\n]+),([0-9\.]+),([0-9\.]+),([0-9\.]+),([0-9\.]+).*/',$csv,$m);
	$num=count($m[0]);
	
	$pnum=-1;
	$cname='';
	for($i=0;$i<$num;$i++){
		// Fetch information
		// Note that sanitizing of dara is done here.
		// I believe NHK web constructer, but the site may be cracked.
		$dat=htmlentities(str_replace('/0','/',str_replace('-','/',$m[1][$i])),ENT_QUOTES,'UTF-8');
		$pname=htmlentities($m[2][$i],ENT_QUOTES,'UTF-8');
		$nums=array($m[3][$i],$m[4][$i],$m[5][$i],$m[6][$i]); // This contains numbers only. See regular expression.
		
		if ($cname!=$pname) {
			// Begin new prefecture
			// Prepare arrays
			$cname=$pname;
			$pnum++;
			$plist[$pnum]=$pname;
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
	$js.="  The data was fetched from https://github.com/nytimes/covid-19-data\n";
	$js.="  , modified and converted to Javascript code.\n";
	$js.="*/\n";
	$js.="var data=new Array();\n";
	for($i=0;$i<=count($plist);$i++){
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

function loadCSV($url){
	$csv='';
	$handle=fopen($url,'r');
	while($line=fgets($handle)){
		if (preg_match('/California/',$line)) $csv.=$line;
	}
	fclose($handle);
	return $csv;
}

function convertCSV($csv,$regex){
	/*
		Format of CSV (conties):
			date,county,state,fips,cases,deaths
			2020-01-21,Snohomish,Washington,53061,1,0
			$regex='([^,]+),California,';
		Format of CSV (states):
			date,state,fips,cases,deaths
			2020-01-21,Washington,53,1,0
			$regex='([A-Z][a-z]+),';
			$regex='(California),';
	*/
	// Create counties array
	$counties=array(
		'California'=>array(),
		'Alameda'=>array(),
		'Alpine'=>array(),
		'Amador'=>array(),
		'Butte'=>array(),
		'Calaveras'=>array(),
		'Colusa'=>array(),
		'Contra Costa'=>array(),
		'Del Norte'=>array(),
		'El Dorado'=>array(),
		'Fresno'=>array(),
		'Glenn'=>array(),
		'Humboldt'=>array(),
		'Imperial'=>array(),
		'Inyo'=>array(),
		'Kern'=>array(),
		'Kings'=>array(),
		'Lake'=>array(),
		'Lassen'=>array(),
		'Los Angeles'=>array(),
		'Madera'=>array(),
		'Marin'=>array(),
		'Mariposa'=>array(),
		'Mendocino'=>array(),
		'Merced'=>array(),
		'Modoc'=>array(),
		'Mono'=>array(),
		'Monterey'=>array(),
		'Napa'=>array(),
		'Nevada'=>array(),
		'Orange'=>array(),
		'Placer'=>array(),
		'Plumas'=>array(),
		'Riverside'=>array(),
		'Sacramento'=>array(),
		'San Benito'=>array(),
		'San Bernardino'=>array(),
		'San Diego'=>array(),
		'San Francisco'=>array(),
		'San Joaquin'=>array(),
		'San Luis Obispo'=>array(),
		'San Mateo'=>array(),
		'Santa Barbara'=>array(),
		'Santa Clara'=>array(),
		'Santa Cruz'=>array(),
		'Shasta'=>array(),
		'Sierra'=>array(),
		'Siskiyou'=>array(),
		'Solano'=>array(),
		'Sonoma'=>array(),
		'Stanislaus'=>array(),
		'Sutter'=>array(),
		'Tehama'=>array(),
		'Trinity'=>array(),
		'Tulare'=>array(),
		'Tuolumne'=>array(),
		'Ventura'=>array(),
		'Yolo'=>array(),
		'Yuba'=>array()
	);
	preg_match_all('/'.$regex.'/',$csv,$m);
	for($i=0;$i<count($m[1]);$i++){
		$counties[$m[1][$i]]=array();
	}
	//print_r($counties);exit;
	// Read CSV
	preg_match_all('/(202[\-0-9]+),'.$regex.'[^,]+,([0-9\.]*),([0-9\.]*)/',$csv,$m);
	for($i=0;$i<count($m[0]);$i++){
		$counties[$m[2][$i]][]=array($m[0][$i],$m[1][$i],$m[2][$i],$m[3][$i],$m[4][$i]);
	}
	//print_r($counties['California']);exit;
	// Construct CSV
	$csv2='';
	foreach($counties as $array){
		$prevp=$prevd=0;
		for($i=0;$i<count($array);$i++){
			$csv2.=$array[$i][1].','.$array[$i][2].','.($array[$i][3]-$prevp).','.$array[$i][3].','.($array[$i][4]-$prevd).','.$array[$i][4]."\n";
			$prevp=$array[$i][3];
			$prevd=$array[$i][4];
		}
	}
	//file_put_contents('./result.csv',$csv2);exit;
	return $csv2;
}
