<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 17-5-15
 * Time: 下午2:51
 */
namespace yuanshuai\yscomponents\extension;
use backend\components\RedisConnection;
use common\components\helper\HtmlHelper;
use yii\caching\Cache;
use yii\db\ActiveRecord as BaseActiveRecord;
use Yii;
use yii\gii\components\ActiveField;
use yii\helpers\ArrayHelper;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\redis\Connection;

/**
 * 重新封装model类
 * Class ActiveRecord
 * @package yuanshuai\yscomponents\extension
 * @property integer created_time
 * @property integer updated_time
 */
class DbActiveRecord extends BaseActiveRecord
{
    const STATUS_OK = 1;
    const STATUS_NO = 0;
    protected $enableRedisAR = false;
    protected $enableCache = false;

    public $primaryKey = "id";
    public static $subTableKey = false;

    /**
     * 格式化表名
     * @return string
     */
    public static function tableName(){
        return "{{%".static::getTableName().static::shardTableRule()."}}";
    }

    /**
     * 获取表名
     *
     * @return string
     */
    public static function getTableName()
    {
        return Inflector::camel2id(StringHelper::basename(static::className()),"_");
    }

    /**
     * 生成表名
     *
     * @return string
     */
    public static function shardTableRule()
    {
        return static::$subTableKey ? '_' . sprintf('%02x', static::$subTableKey % 6) : '';
    }

    /**
     * 返回cache
     * @return Cache mixed
     */
    protected static function getCache()
    {
        return Yii::$app->cache;
    }

    /**
     * 设置redis
     * @return Connection mixed
     */
    protected static function getRedis()
    {
        return Yii::$app->redis;
    }

    /**
     * 获取redis对应的模型
     *
     * @return RedisActiveRecord
     */
    protected static function getRedisAR()
    {
        return new RedisActiveRecord();
    }

    /**
     * 保存之后
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($this->enableRedisAR) {
            $redisAr = static::getRedisAR()->get($this->getAttribute($this->primaryKey));
            if (!$redisAr) {
                $redisAr = static::getRedisAR();
            }
            if ($redisAr->getAttributes() != $this->getAttributes()) {
                $redisAr->setAttributes($this->getAttributes(),false);
                $redisAr->save();
            }
        }
        if ($this->enableCache) {
            $this->clearOneCache();
        }
    }

    /**
     * 清理单个缓存
     */
    private function clearOneCache()
    {
        self::getCache()->delete(self::GetCacheKey($this->getAttribute($this->primaryKey)));
    }

    /**
     * 获取单个缓存key
     *
     * @param $parmaryValue
     * @return string
     */
    public static function GetCacheKey($parmaryValue)
    {
        return self::tableName()."_".$parmaryValue;
    }

    /**
     * 删除之后更新缓存
     */
    public function afterDelete()
    {
        if ($this->enableRedisAR) {
            $redisAr = static::getRedisAR()->get($this->getAttribute($this->primaryKey));
            if ($redisAr) {
                $redisAr->delete();
            }
        }
        if ($this->enableCache) {
            $this->clearOneCache();
        }
    }

    public function beforeValidate()
    {
        if ($this->hasAttribute('created_time') && $this->hasAttribute('updated_time')) {
            $time = time();
            $this->updated_time = $time;
            if ($this->getIsNewRecord()) {
                if ($this->hasAttribute("status") && is_null($this->getAttribute("status"))) {
                    $this->status = static::STATUS_OK;
                }
                $this->created_time = $time;
            }
        }
        return parent::beforeValidate();
    }

    /**
     * 保存之前设置一些默认值
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if ($insert) {
            if ($this->enableRedisAR) {
                $this->setAttribute($this->primaryKey,$this->getGeneratorId());
                $this->setAttributeKeys();
            }
        }
        return parent::beforeSave($insert);
    }

    /**
     * 从缓存中获取ID
     */
    private function getGeneratorId(){
        $key = strtr(Consts::ID_GENERATOR_PREFIX,["{:name}"=>static::getTableName()]);
        if(!static::getCache()->exists($key)){
            $one = static::findOneArray([],["{$this->primaryKey} DESC"]);
            if ($one) {
                static::getCache()->set($key,$one["id"]);
            }
        };
        return static::getCache()->incrby($key,1);
    }

    /**
     * 设置字段
     */
    public function setAttributeKeys()
    {
        $key = strtr(Consts::ATTRIBUTES_KEYS,["{:name}"=>static::getTableName()]);
        $keys = array_keys($this->getAttributes());
        if (static::getCache()->get($key) != Json::encode($keys)) {
            static::getCache()->set($key,Json::encode($keys));
        }
    }

    /**
     * 获取所有的数据，包括分表的
     * @param array $condition
     * @param array $orderBy
     * @return array
     */
    public static function findAllArray($condition = [],$orderBy = [])
    {
        return static::find()->where($condition)->orderBy($orderBy)->asArray()->all();
    }

    /**
     * 获取一个数据
     * @param array $condition
     * @return array
     */
    public static function findOneArray($condition = [])
    {
        return static::find()->where($condition)->asArray()->one();
    }

    /**
     * 通过ID获取数据
     * @param $id
     * @param bool $cache 是否从缓存获取
     * @return bool|static
     */
    public function get($id = null,$cache = false)
    {
        if ($cache && $this->enableCache) {
            $model = self::getCache()->get(self::GetCacheKey($id));
            if ($model) {
                return $model;
            }
            $model = static::findOne([$this->primaryKey=>$id]);
            self::getCache()->set(self::GetCacheKey($id),$model);
        }else{
            $model = static::findOne([$this->primaryKey=>$id]);
        }
        if (!$model) {
            return false;
        }
        return $model;
    }

    /**
     * 重写获取错误方法
     * @param null $attribute
     * @return array|string
     */
    public function getErrors($attribute = null)
    {
        $error = parent::getErrors($attribute); // TODO: Change the autogenerated stub
        if (is_array($error)) {
            $errorStrs = [];
            foreach ($error as $err) {
                $errorStrs[] = $err[0];
            }
            return implode(" ",$errorStrs);
        }
        return $error;
    }

    /**
     * 和static::find()一样的效果
     * @return \yii\db\ActiveQuery
     */
    public function query(){
        return static::find();
    }

    /**
     * 设置默认字段
     *
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'created_time' => '创建时间',
            'updated_time' => '更新时间',
            'status' => '状态',
        ];
    }

    /**
     * 获取默认状态
     *
     * @param null $status
     * @return array|mixed
     */
    public static function getStatus($status = null)
    {
        $statuses = [
            static::STATUS_OK=>'有效',
            static::STATUS_NO=>'无效'
        ];

        return is_null($status) ? $statuses : ArrayHelper::getValue($statuses,$status,'');
    }

    public function formFieldName($field)
    {
        return HtmlHelper::getInputName($this,$field);
    }

    public function getFirstErrorMessage()
    {
        $firstErrorMessages = $this->getFirstErrors();
        return reset($firstErrorMessages);
    }
}