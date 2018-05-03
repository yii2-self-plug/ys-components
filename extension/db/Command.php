<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/22/17
 * Time: 3:53 PM
 */

namespace yuanshuai\yscomponents\extension\db;
use yii\db\Command as YiiCommand;

class Command extends YiiCommand
{
    /**
     * 插入字段
     * @param $table
     * @param $column
     * @param $type
     * @param $after
     * @return $this
     */
    public function afterColumn($table,$column,$type,$after)
    {
        $sql = $this->db->getQueryBuilder()->afterColumn($table,$column,$type,$after);
        return $this->setSql($sql)->requireTableSchemaRefresh($table);
    }
}