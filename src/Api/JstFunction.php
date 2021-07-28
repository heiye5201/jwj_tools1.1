<?php
namespace jwj_tools\tools\Api;


class JstFunction
{

	//对结果集进行字段筛选
    public function field_screen($data, $fileld_arr)
    {
        $returnData = [];
        foreach ($data as $k1 => $v1) {
            foreach ($v1 as $k2 => $v2) {
                if (in_array($k2, $fileld_arr)) {
                    $returnData[$k1][$k2] = $v2;
                }
            }
        }
        return $returnData;
    }


	//两个地图经纬度距离计算
	 public function getDistance($lat1, $lng1, $lat2, $lng2){ 
		$lat1 = (float)$lat1;
		$lng1 = (float)$lng1;
		$lat2 = (float)$lat2;
		$lng2 = (float)$lng2;
        $earthRadius = 6367000; //approximate radius of earth in meters 
        $lat1 = ($lat1 * pi() ) / 180; 
        $lng1 = ($lng1 * pi() ) / 180; 
        $lat2 = ($lat2 * pi() ) / 180;
        $lng2 = ($lng2 * pi() ) / 180; 
        $calcLongitude = $lng2 - $lng1; 
        $calcLatitude = $lat2 - $lat1; 
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2); 
        $stepTwo = 2 * asin(min(1, sqrt($stepOne))); 
        $calculatedDistance = $earthRadius * $stepTwo; 
        return round($calculatedDistance); 
    }



}