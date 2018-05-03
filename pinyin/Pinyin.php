<?php
namespace yuanshuai\yscomponents\pinyin;
use yii\base\Component;
use Overtrue\Pinyin\Pinyin as BasePinyin;
/**
 * Class Pinyin
 * @package yuanshuai\yscomponents\pinyin
 */
class Pinyin extends Component
{
    public $type = "";
    private $pinyin;
    public function init()
    {
        $this->pinyin = new BasePinyin($this->type);
    }

    public function convert($str,$option = BasePinyin::NONE)
    {
        return $this->pinyin->convert($str,$option);
    }

    public function permalink($str,$delimiter = '-')
    {
        return $this->pinyin->permalink($str,$delimiter);
    }

    public function abbr($str, $delimiter = '')
    {
        return $this->pinyin->abbr($str,$delimiter);
    }

    public function sentence($str, $withTone = false)
    {
        return $this->pinyin->sentence($str,$withTone);
    }

    public function name($str,$option = BasePinyin::NONE)
    {
        return $this->pinyin->name($str,$option);
    }
}