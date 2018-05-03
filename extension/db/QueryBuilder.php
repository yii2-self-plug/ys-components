<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 6/22/17
 * Time: 3:35 PM
 */

namespace yuanshuai\yscomponents\extension\db;
use yii\db\mysql\QueryBuilder as YiiQueryBuilder;
class QueryBuilder extends YiiQueryBuilder
{
    /**
     * @param string $table
     * @param string $column
     * @param string $type
     * @param string $after
     */
    public function afterColumn($table,$column,$type,$after)
    {
        return 'ALTER TABLE ' . $this->db->quoteTableName($table)
            . ' ADD ' . $this->db->quoteColumnName($column) . ' '
            . $this->getColumnType($type) . ' AFTER ' . $this->db->quoteColumnName($after);
    }
}