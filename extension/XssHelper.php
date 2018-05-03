<?php
/**
 * Copyright 2008-2015 OPPO Mobile Comm Corp., Ltd, All rights reserved.
 * FileName:XssHelper.php
 * Author:80047746
 * Create Date:2016-10-28
 */

namespace yuanshuai\yscomponents\extension;

/**
 * 预防XSS攻击
 * Class XssHelper
 * @package yii\liuxy\components\helpers
 */
class XssHelper {

    static $_filters = ['javascript', 'vbscript', 'expression',
        'applet', 'meta', 'xml', 'script', 'embed', 'object',
        'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound',
        'onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate',
        'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus',
        'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur',
        'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect',
        'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete',
        'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave',
        'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange',
        'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown',
        'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture',
        'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout',
        'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart',
        'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize',
        'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted',
        'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'];

    static $_prePattern = '('.
    '(&#[xX]0{0,8}([9ab]);)'.
    '|'.
    '|(�{0,8}([9|10|13]);)'.
    ')*';

    static $_search = 'abcdefghijklmnopqrstuvwxyz'.
    'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.
    '1234567890!@#$%^&*()'.
    '~`";:?+/={}[]-_|\'\\';

    /**
     * 校验请求地址是否有xss代码
     * @return bool
     */
    public static function check() {
        $temp = strtoupper(urldecode(urldecode($_SERVER['REQUEST_URI'])));
        if(strpos($temp, '<') !== false || strpos($temp, '"') !== false || strpos($temp, 'CONTENT-TRANSFER-ENCODING') !== false) {
            return false;
        }
        return true;
    }

    /**
     * 过滤xss攻击代码
     * @param $val
     * @return string|array
     */
    public static function filter($val) {
        if (is_string($val)) {
            return static::filterValue($val);
        }  else if (is_array($val)) {
            return static::filterArray($val);
        }
        return $val;
    }

    private static function filterValue($val) {
        $len = strlen(static::$_search);
        for ($i = 0; $i < $len; $i++) {
            // ;? matches the ;, which is optional
            // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars
            // @ @ search for the hex values
            $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord(static::$_search[$i])).';?)/i', static::$_search[$i], $val); // with a ;
            // @ @ 0{0,7} matches '0' zero to seven times
            $val = preg_replace('/(�{0,8}'.ord(static::$_search[$i]).';?)/', static::$_search[$i], $val); // with a ;
        }
        // now the only remaining whitespace attacks are \t, \n, and \r
        $found = true; // keep replacing as long as the previous round replaced something
        $size = sizeof(static::$_filters);
        while ($found == true) {
            $val_before = $val;
            for ($i = 0; $i < $size; $i++) {
                $pattern = '/';
                for ($j = 0; $j < strlen(static::$_filters[$i]); $j++) {
                    if ($j > 0) {
                        $pattern .= static::$_prePattern;
                    }
                    $pattern .= static::$_filters[$i][$j];
                }
                $pattern .= '/i';
                $replacement = substr(static::$_filters[$i], 0, 2).' '.substr(static::$_filters[$i], 2); // add in <> to nerf the tag
                $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags
                if ($val_before == $val) {
                    // no replacements were made, so exit the loop
                    $found = false;
                }
            }
        }
        return $val;
    }

    private static function filterArray($rows) {
        foreach($rows as $key=>&$val) {
            if (is_array($val)) {
                $val = static::filterArray($val);
            } else {
                $val = static::filterValue($val);
            }
        }
        return $rows;
    }
}