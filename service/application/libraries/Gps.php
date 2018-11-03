<?php
// namespace Ehome\Library\Net;
class Gps
{

    //
    // Krasovsky 1940
    //
    // a = 6378245.0, 1/f = 298.3
    // b = a * (1 - f)
    // ee = (a^2 - b^2) / a^2;
    const PI = 3.14159265358979324;
    const A  = 6378245.0;
    const EE = 0.00669342162296594323;
    //
    // World Geodetic System ==> Mars Geodetic System
    public static function transform($wgLat, $wgLon)
    {
        if ($this->outOfChina($wgLat, $wgLon)) {
            $mgLat = $wgLat;
            $mgLon = $wgLon;
            return;
        }
        $dLat      = $this->transformLat($wgLon - 105.0, $wgLat - 35.0);
        $dLon      = $this->transformLon($wgLon - 105.0, $wgLat - 35.0);
        $radLat    = $wgLat / 180.0 * self::PI;
        $magic     = sin($radLat);
        $magic     = 1 - self::ee * $magic * $magic;
        $sqrtMagic = sqrt($magic);
        $dLat      = ($dLat * 180.0) / ((self::a * (1 - self::ee)) / ($magic * $sqrtMagic) * self::PI);
        $dLon      = ($dLon * 180.0) / (self::a / $sqrtMagic * cos($radLat) * self::PI);
        $lat     = $wgLat + $dLat;
        $lng     = $wgLon + $dLon;
        return compact('lat', 'lng');
    }

    public static function outOfChina($lat, $lon)
    {
        if ($lon < 72.004 || $lon > 137.8347) {
            return true;
        }

        if ($lat < 0.8293 || $lat > 55.8271) {
            return true;
        }

        return false;
    }

    public static function transformLat($x, $y)
    {
        $ret = -100.0 + 2.0 * $x + 3.0 * $y + 0.2 * $y * $y + 0.1 * $x * $y + 0.2 * sqrt(abs($x));
        $ret += (20.0 * sin(6.0 * $x * self::PI) + 20.0 * sin(2.0 * $x * self::PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($y * self::PI) + 40.0 * sin($y / 3.0 * self::PI)) * 2.0 / 3.0;
        $ret += (160.0 * sin($y / 12.0 * self::PI) + 320 * sin($y * self::PI / 30.0)) * 2.0 / 3.0;
        return $ret;
    }

    public static function transformLon($x, $y)
    {
        $ret = 300.0 + $x + 2.0 * $y + 0.1 * $x * $x + 0.1 * $x * $y + 0.1 * sqrt(abs($x));
        $ret += (20.0 * sin(6.0 * $x * self::PI) + 20.0 * sin(2.0 * $x * self::PI)) * 2.0 / 3.0;
        $ret += (20.0 * sin($x * self::PI) + 40.0 * sin($x / 3.0 * self::PI)) * 2.0 / 3.0;
        $ret += (150.0 * sin($x / 12.0 * self::PI) + 300.0 * sin($x / 30.0 * self::PI)) * 2.0 / 3.0;
        return $ret;
    }
    /**
     * 获取两坐标位置的距离,
     * 距离单位：米
     * decimal 保留小数位
     */
    public static function getDistance($lat1, $lng1, $lat2, $lng2, $decimal = 2)
    {
        $earth_radius = 6378.137; //地球半径
        $radLat1      = $lat1 * self::PI / 180.0;
        $radLat2      = $lat2 * self::PI / 180.0;
        $a            = $radLat1 - $radLat2;
        $b            = ($lng1 * self::PI / 180.0) - ($lng2 * self::PI / 180.0);
        $s            = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $s            = $s * $earth_radius;
        // $s            = round($s * 1000);
        return round($s, $decimal);
    }
    public static function getDistance2($lat1, $lng1, $lat2, $lng2, $decimal = 2)
    {
        $fEARTH_RADIUS = 6378137;
        $fRadLon1      = deg2rad($lng1);
        $fRadLon2      = deg2rad($lng2);
        $fRadLat1      = deg2rad($lat1);
        $fRadLat2      = deg2rad($lat2);
        //计算经纬度的差值
        $fD1 = abs($fRadLat1 - $fRadLat2);
        $fD2 = abs($fRadLon1 - $fRadLon2);
        //距离计算
        $fP = pow(sin($fD1 / 2), 2) +
        cos($fRadLat1) * cos($fRadLat2) * pow(sin($fD2 / 2), 2);
        return intval($fEARTH_RADIUS * 2 * asin(sqrt($fP)) + 0.5);
    }
    public static function getDistance3($lon1, $lat1, $lon2, $lat2)
    {
        return (2 * ATAN2(SQRT(SIN(($lat1 - $lat2) * PI() / 180 / 2)
             * SIN(($lat1 - $lat2) * PI() / 180 / 2) +
            COS($lat2 * PI() / 180) * COS($lat1 * PI() / 180)
             * SIN(($lon1 - $lon2) * PI() / 180 / 2)
             * SIN(($lon1 - $lon2) * PI() / 180 / 2)),
            SQRT(1 - SIN(($lat1 - $lat2) * PI() / 180 / 2)
                 * SIN(($lat1 - $lat2) * PI() / 180 / 2)
                 + COS($lat2 * PI() / 180) * COS($lat1 * PI() / 180)
                 * SIN(($lon1 - $lon2) * PI() / 180 / 2)
                 * SIN(($lon1 - $lon2) * PI() / 180 / 2)))) * 6378140;
    }
}

/*$Gps= new getGps();
$Gps->transform(30.28798,120.10939);*/
