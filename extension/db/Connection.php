<?php
namespace yuanshuai\yscomponents\extension\db;
use yii\db\Connection as YiiConnection;

/**
 * Class Connection
 * @package yuanshuai\yscomponents\extension\db
 */
class Connection extends YiiConnection
{
    public $commandClass = 'yuanshuai\yscomponents\extension\db\Command';

    public $schemaMap = [
        'pgsql' => 'yii\db\pgsql\Schema', // PostgreSQL
        'mysqli' => 'yii\db\mysql\Schema', // MySQL
        'mysql' => 'yii\db\mysql\Schema', // MySQL
        'sqlite' => 'yii\db\sqlite\Schema', // sqlite 3
        'sqlite2' => 'yii\db\sqlite\Schema', // sqlite 2
        'sqlsrv' => 'yii\db\mssql\Schema', // newer MSSQL driver on MS Windows hosts
        'oci' => 'yii\db\oci\Schema', // Oracle driver
        'mssql' => 'yii\db\mssql\Schema', // older MSSQL driver on MS Windows hosts
        'dblib' => 'yii\db\mssql\Schema', // dblib drivers on GNU/Linux (and maybe other OSes) hosts
        'cubrid' => 'yii\db\cubrid\Schema', // CUBRID
    ];

    public function getCreateTable($tableName)
    {
        try{
            $row = $this->createCommand('SHOW CREATE TABLE ' . $tableName)->queryOne();
            if (isset($row['Create Table'])) {
                $sql = $row['Create Table'];
            } else {
                $row = array_values($row);
                $sql = $row[1];
            }

            return $sql;
        }catch (\PDOException $exception) {
            return false;
        }
    }

    /**
     * @param null $sql
     * @param array $params
     * @return Command command
     */
    public function createCommand($sql = null, $params = [])
    {
        return parent::createCommand($sql,$params);
    }

    /**
     * @return Schema schema
     */
    public function getSchema()
    {
        return parent::getSchema();
    }

    public function getDsn()
    {
        $dsn = explode(":",$this->dsn);
        $dsnArray = explode(";",$dsn[1]);
        $data = [];
        foreach ($dsnArray as $item) {
            $itemArray = explode("=",$item);
            $data[$itemArray[0]] = $itemArray[1];
        }
        return $data;
    }
}