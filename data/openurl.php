<?php

function saveRemoteFile($url, $filename) {

  global $GlobalFileHandle;

  set_time_limit(0);

  # Open the file for writing...
  $GlobalFileHandle = fopen($filename, 'w+');

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_FILE, $GlobalFileHandle);
  curl_setopt($ch, CURLOPT_HEADER, 0);
  curl_setopt($ch, CURLOPT_USERAGENT, "MY+USER+AGENT"); //Make this valid if possible
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); # optional
  curl_setopt($ch, CURLOPT_TIMEOUT, -1); # optional: -1 = unlimited, 3600 = 1 hour
  curl_setopt($ch, CURLOPT_VERBOSE, false); # Set to true to see all the innards

  # Only if you need to bypass SSL certificate validation
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

  # Assign a callback function to the CURL Write-Function
  curl_setopt($ch, CURLOPT_WRITEFUNCTION, 'curlWriteFile');

  # Exceute the download - note we DO NOT put the result into a variable!
  curl_exec($ch);

  # Close CURL
  curl_close($ch);

  # Close the file pointer
  fclose($GlobalFileHandle);
}

function curlWriteFile($cp, $data) {
  global $GlobalFileHandle;
  $len = fwrite($GlobalFileHandle, $data);
  return $len;
}

function openURL($url) {
  $filename =tempnam(sys_get_temp_dir(), 'Temp');;
  saveRemoteFile($url,$filename);
  $result=file_get_contents($filename);
  unlink($filename);
  return $result;
}
