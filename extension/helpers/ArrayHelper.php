<?php
namespace yuanshuai\yscomponents\extension\helpers;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper as BaseArrayHelper;
/**
 * Created by PhpStorm.
 * User: root
 * Date: 7/20/17
 * Time: 9:26 AM
 */
class ArrayHelper extends BaseArrayHelper
{
    /**
     * 获取二维数组里的字段集和
     * @param array $array
     * @param array $fileds
     * @param string $default
     * @return array
     */
    public static function getFileds($array = [], $fileds = [],$default = "")
    {
        $result = [];
        foreach ($array as $itemArray) {
            $item = [];
            foreach ($fileds as $key => $filed) {
                if (is_array($itemArray)) {
                    if ($filed instanceof \Closure) {
                        $item[$key] = $filed($itemArray);
                    }else{
                        $value = static::getValue($itemArray,$filed,null);
                        $item[$filed] = $value ? $value : $default;
                    }
                }
                if (is_object($itemArray)) {
                    if ($filed instanceof \Closure) {
                        $item[$key] = $filed($itemArray);
                    }else{
                        if ($itemArray instanceof BaseActiveRecord) {
                            $item[$filed] = isset($itemArray->$filed) ? $itemArray->$filed : $default;
                        }
                    }
                }
            }
            $result[] = $item;
        }

        return $result;
    }

    /**
     * 获取一纬数组里的字段集合
     * @param array $array
     * @param array $flieds
     * @param string $default
     * @return mixed
     */
    public static function getFliedOne($array = [],$flieds = [],$default = "")
    {
        $result = [];
        foreach ($flieds as $key => $flied) {
            if ($flied instanceof \Closure) {
                $result[$key] = $flied($array);
            }else{
                if (is_object($array)){
                    $value = isset($array->$flied) ? $array->$flied : $default;
                }else{
                    $value = static::getValue($array,$flied,$default);
                }
                $result[$flied] = $value;
            }
        }
        return $result;
    }
}