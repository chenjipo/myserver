<?php 
//上海ip打印1，否则打印0
include(dirname(__FILE__).'/iplocation.php');
$ip = new iplocation();
$row = array();
$addrs=$ip->getAddr(getIP());
//$addrs=$ip->getAddr('120.31.66.117');//广东
//$addrs=$ip->getAddr('180.173.3.108');//上海ip
//var_dump($addrs);
$row['PROVINCE']=$addrs[0];
$row['CITY']=$addrs[1];
if ($row['PROVINCE'] == "上海市" || $row['PROVINCE'] == "上海"){
	$reault["area_id"] = "1";
}elseif ($row['CITY'] == "武汉市"){
	$reault["area_id"] = "2";
}elseif ($row['CITY'] == "1沈阳市"){
	$reault["area_id"] = "3";
}elseif ($row['CITY'] == "珠海市"){
	$reault["area_id"] = "4";
}elseif ($row['CITY'] == "广州市"){
	$reault["area_id"] = "5";
}elseif ($row['CITY'] == "福州市"){
	$reault["area_id"] = "6";
}elseif ($row['PROVINCE'] == "北京市" || $row['PROVINCE'] == "北京"){
	$reault["area_id"] = "7";
}elseif ($row['PROVINCE'] == "辽宁省" || $row['PROVINCE'] == "辽宁"){
	$reault["area_id"] = "8";
}elseif ($row['PROVINCE'] == "浙江省" || $row['PROVINCE'] == "浙江"){
	$reault["area_id"] = "9";
} else{
	$reault["area_id"] = "0";
}
include(dirname(dirname(dirname(__FILE__))).'/include/Area_Activity.php');
$reault["image"] = $areaimage;
$reault["link"] = $arealink;
$reault["flash"] = $areaflash;
echo json_encode($reault);

//获取当前访问ip
function getIP()
{
	if(!empty($_SERVER["HTTP_CLIENT_IP"]))
	{
		$cip = $_SERVER["HTTP_CLIENT_IP"];
	}
	else if(!empty($_SERVER["REMOTE_ADDR"]))
	{
		$cip = $_SERVER["REMOTE_ADDR"];
	}
	else
	{
		$cip = '';
	}
	preg_match("/[\d\.]{7,15}/", $cip, $cips);
	$cip = isset($cips[0]) ? $cips[0] : 'unknown';
	unset($cips);
	return $cip;
}
?>