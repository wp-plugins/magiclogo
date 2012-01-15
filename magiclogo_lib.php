<?php
//--------------------------Copyright 2011 Magic Logo LLC, all rights reserved-------------------------
function displayFile($file){
//	echo "File:{$file}<br>\n";
	$size = getimagesize($file);
	header('Content-Description: File Transfer');
    header('Content-Disposition: inline; filename="'.getFileName($file).'"');
    header('Content-Transfer-Encoding: binary');
    header("Accept-Ranges: bytes");
    $mtime=filemtime($file);
    $mdate=gmdate("D, d M Y H:i:s", $mtime);
    header("Date: {$mdate} GMT");
	header("Last-Modified: {$mdate} GMT");;
	header('ETag: ' . sha1_file($file));
	header('Content-Type: '.$size['mime']);
	header('Content-Length: ' . filesize($file));
	ob_clean();
	flush();
	readfile($file);
	exit;
}
//--------------------
function printValue($v=''){
	$type=strtolower(gettype($v));
	$plaintypes=array('string','integer');
	if(in_array($type,$plaintypes)){return $v;}
	$rtn = '<pre class="w_times" type="'.$type.'">'."\n";
	ob_start();
	print_r($v);
	$rtn .= ob_get_contents();
	ob_end_clean();
	$rtn .= "\n</pre>\n";
	return $rtn;
	}
//-----------------------------
function abortMagic($msg=''){
	$file="{$_SERVER['DOCUMENT_ROOT']}/{$_REQUEST['src']}";
	displayFile($file);
	exit;
}
//-----------------------------
function getMagicLogo($src,$params=array()){
	global $localpath;
	//first look for a localfile with todays stamp - magiclogo_20111121.(png|gif|jpg)
	$ext=getFileExtension($src);
	$cdate=date('Ymd');
	$localfile="{$localpath}/magiclogo";
	$fields=array('pos','size','code');
	$parts=array($localfile);
	foreach($fields as $field){
    	if(isset($_REQUEST[$field]) && strlen(trim($_REQUEST[$field]))){$parts[]=strtoupper(trim($_REQUEST[$field]));}
	}
	$localfile = implode('_',$parts);
	//remove yesterdays file and the day before
	$ydate=date('Ymd',strtotime('-1 Day'));
	$yesterdayfile="{$localfile}_{$ydate}.{$ext}";
	if(is_file($yesterdayfile)){unlink($yesterdayfile);}
	$ydate=date('Ymd',strtotime('-2 Day'));
	$yesterdayfile="{$localfile}_{$ydate}.{$ext}";
	if(is_file($yesterdayfile)){unlink($yesterdayfile);}

	$localfile .= "_{$cdate}.{$ext}";

	if(is_file($localfile) && filesize($localfile) > 0){return $localfile;}
	$url='http://stage.magiclogo.net/magiclogo?host='.$_SERVER['HTTP_HOST'].'&src='.urlencode($src);
	$opts=array('code','pos','size');
	foreach($opts as $opt){
		if($params[$opt]){$url .= '&'.$opt.'='.$params[$opt];}
	}
	$ch = curl_init($url);
    curl_setopt($ch, CURLOPT_NOBODY, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $data = curl_exec($ch);
    curl_close($ch);
    $link=trim($data);
    if($link=='-/-'){
		//nothing to do - use original src
		if(preg_match('/^http/i',$src)){
			$filename=getFileName($src);
			//$localfile="{$localpath}/{$filename}";
			$ok=getRemoteImage($src,$localfile);
			return $localfile;
		}
		return $src;
	}
    $filename=getFileName($link);
	//$localfile="{$localpath}/{$filename}";
	if(!is_file($localfile)){
		$linkurl="http://stage.magiclogo.net/{$link}";
		$ok=getRemoteImage($linkurl,$localfile);
	}
	return $localfile;
}
//----------------------
function getRemoteImage($remote_url, $target){
	$path=getFilePath($target);
	if(!is_dir($path)){buildDir($path);}
	elseif(is_file($target)){unlink($target);}
	$ch = curl_init($remote_url);
	$fp = fopen($target, "wb");
	//echo $remote_url . "<hr>" . $target."<hr>".printValue($fp);exit;
	$options = array(
		CURLOPT_FILE => $fp,
		CURLOPT_HEADER => 0,
		CURLOPT_FOLLOWLOCATION => 1,
		CURLOPT_TIMEOUT => 600,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_FRESH_CONNECT => 1,
		);
	curl_setopt_array($ch, $options);
	curl_exec($ch);
	if ( curl_errno($ch) ) {
		echo "ERROR:" . curl_error($ch);
		curl_close($ch);
		fclose($fp);
		exit;
	}
	curl_close($ch);
	fclose($fp);
}
//----------------------
function buildDir($dir='',$mode=0777,$recursive=true){
	//info:recursive folder generator
	if(is_dir($dir)){return 0;}
	return mkdir($dir,$mode,$recursive);
	}
//----------------------
function getFileName($file='',$stripext=0){
	//info: returns the file name of filename without its path
	//params (string) filename, (boolean) strip_extension
	$file=preg_replace('/\\+/','/',$file);
	$tmp=preg_split('/[\/]/',$file);
	$name=array_pop($tmp);
	if($stripext){
		$stmp=explode('.',$name);
		array_pop($stmp);
		$name=implode('.',$stmp);
    	}
	return $name;
	}
//----------------------
function getFilePath($file=''){
	//info: returns the path of of filename without the filename
	//params (string) filename
	if(!strlen(trim($file))){return '';}
	if(preg_match('/\//',$file)){$tmp=explode("/",$file);}
	else{$tmp=explode("\\",$file);}
	$name=array_pop($tmp);
	$path=implode('/',$tmp);
	return $path;
	}
//----------------------
function getFileExtension($file=''){
	//info: returns the file extension of filename
	//params (string) filename
	$tmp=explode('.',$file);
	$ext=array_pop($tmp);
	return strtolower($ext);
	}
?>