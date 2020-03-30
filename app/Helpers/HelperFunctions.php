<?php
namespace App\Helpers;

use DB;

class HelperFunctions
{
    public static function get_in_range_entities($myLat, $myLng, $model, $entityName, $latCol='lat', $lngCol='lng', $kiloMeters=100, $maxNumber=999)
    {
        $distaneFormula =self::DistanceFormula($myLat, $myLng, $entityName, $latCol, $lngCol);

        return $model->select($entityName.'.*', DB::raw($distaneFormula .' as DIstance'))
              ->where(DB::raw($distaneFormula), '<=', $kiloMeters / 6371.0)
              ->orderBy('DIstance')
              ->limit($maxNumber);
    }

    public static function DistanceFormula($myLat, $myLng, $entityName, $latCol, $lngCol)
    {
        return "ACOS(COS(RADIANS($myLat)) * COS(RADIANS($entityName.$latCol)) * COS(RADIANS($entityName.$lngCol) - RADIANS($myLng)) + SIN(RADIANS($myLat)) * SIN(radians($entityName.$latCol)))" ;
    }
}
