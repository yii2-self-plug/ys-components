<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/8/17
 * Time: 2:36 PM
 */

namespace yuanshuai\yscomponents\extension;


use worker\models\ModelDelete;
use worker\models\ModelSave;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\redis\ActiveRecord;

/**
 * Class RedisActiveRecord
 * @package yuanshuai\yscomponents\extension
 * @property integer created_time
 * @property integer updated_time
 */
class RedisActiveRecord extends ActiveRecord
{
    /**
     * 获取表名
     * @return mixed
     */
    public static function getTableName()
    {
        return Inflector::camel2id(StringHelper::basename(static::className()),"_");
    }

    /**
     * 设置字段
     * @return array|mixed
     */
    public function attributes()
    {
        return $this->setAttributeKeys();
    }

    /**
     * 设置对应的数据库模型
     * @return string
     */
    public static function getDbAR()
    {
        return DbActiveRecord::className();
    }

    /**
     * 根据字段获取一个数据
     * @param null $id
     * @return bool|static
     */
    public function get($id = null)
    {
        $model = static::findOne($id);
        if (!$model) {
            /**
             * @var DbActiveRecord $dbModel
             */
            $dbModel = \Yii::createObject(["class"=>static::getDbAR()]);
            $dbModel->query()->where([$dbModel->primaryKey=>$id])->one();
            if ($dbModel) {
                $this->setAttributes($dbModel->getAttributes());
                $this->save();
                $model = $this;
            }
        }
        return $model;
    }

    public function beforeSave($insert)
    {
        if ($this->hasAttribute("created_time") && $this->hasAttribute("updated_time")) {
            $this->setAttribute("updated_time",time());
            if ($insert) {
                $this->setAttribute("created_time",time());
            }
        }
        if ($insert) {
            if (!($this->hasAttribute(static::primaryKey()[0]) && $this->getAttribute(static::primaryKey()[0]))){
                $this->setAttribute(static::primaryKey()[0],$this->getGeneratorId());
            }
        }

        return parent::beforeSave($insert);
    }

    public function getGeneratorId()
    {
        $key = strtr(Consts::ID_GENERATOR_PREFIX,["{:name}"=>static::getTableName()]);
        return static::getDb()->incrby($key,1);
    }

    private function setAttributeKeys()
    {
        $key = strtr(Consts::ATTRIBUTES_KEYS,["{:name}"=>static::getTableName()]);
        $attrbutes = static::getDb()->get($key);
        if (!$attrbutes) {
            $dbar = \Yii::createObject(["class"=>static::getDbAR()]);
            if ($dbar instanceof DbActiveRecord){
                $dbar->setAttributeKeys();
                $attrbutes = array_keys($dbar->getAttributes());
            }
        }else{
            $attrbutes = Json::decode($attrbutes);
        }

        return $attrbutes;
    }

    /**
     * 使用消息队列保存到数据库
     * @param bool $insert
     * @param array $changedAttributes
     */
    public function afterSave($insert, $changedAttributes)
    {
        /**
         * @var DbActiveRecord $model
         */
        $model = \Yii::createObject(["class"=>static::getDbAR()]);
        $model = $model->query()->where([$model->primaryKey=>$this->getAttribute(static::primaryKey()[0])])->one();
        if (!$model || array_diff($this->getAttributes(),$model->getAttributes())){
            $model->setAttributes($this->getAttributes());
            $model->save();
        }
    }

    /**
     * 使用消息队列删除数据库
     */
    public function afterDelete()
    {
        /**
         * @var DbActiveRecord $model
         */
        $model = \Yii::createObject(["class"=>static::getDbAR()]);
        $model = $model->get($this->getAttribute($this->primaryKey));
        if ($model) {
            $model->delete();
        }
    }

    /**
     * @return \yii\redis\ActiveQuery
     */
    public function query()
    {
        return static::find();
    }
}