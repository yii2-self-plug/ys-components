<?php
/**
 * Created by PhpStorm.
 * User: 0489617
 * Date: 2017/11/27
 * Time: 14:26
 */

namespace yuanshuai\yscomponents\extension\helpers;


class CdnHelper
{
    public static function url($url,$alias = null)
    {
        if (empty($url)) {
            return "";
        }
        if (strrpos($url,"http") === 0) {
            return $url;
        }
        if (!is_null($alias)) {
            return \Yii::getAlias($alias).$url;
        }
        if (env("ENABLE_OSS",false)) {
            return \Yii::$app->oss->cdn.$url;
        }
        if (env("TENCENT_COS_OPEN",false)) {
            return \Yii::$app->cos->cdn.$url;
        }
        return \Yii::getAlias("@storageUrl").$url;
    }
}