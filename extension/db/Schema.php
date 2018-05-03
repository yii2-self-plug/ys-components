<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/22/17
 * Time: 3:33 PM
 */

namespace yuanshuai\yscomponents\extension\db;
use yii\db\mysql\Schema as YiiSchema;
/**
 * Class Schema
 * @package yuanshuai\yscomponents\extension\db
 */
class Schema extends YiiSchema
{
    /**
     * @return QueryBuilder query
     */
    public function createQueryBuilder(){
        return new QueryBuilder($this->db);
    }
}