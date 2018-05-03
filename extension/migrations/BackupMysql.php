<?php
namespace yuanshuai\yscomponents\extension\migrations;
use yii\base\Component;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yuanshuai\yscomponents\extension\db\Connection;
use yuanshuai\yscomponents\extension\FileHelper;

/**
 * Class BackupMysql
 * @package yuanshuai\yscomponents\extension\CreateMigrate
 */
class BackupMysql extends Component
{
    /**
     * @var string
     */
    public $alias;
    /**
     * @var Connection
     */
    public $db;
    public function init()
    {
        if (!$this->alias) {
            $this->alias = "@app/migrate";
        }
        if (!$this->db) {
            $this->db = \Yii::$app->get("db");
        }

        $this->alias = \Yii::getAlias($this->alias);
    }

    public function backup($table)
    {
        try{
            $tableArray = [];
            $tableArray["tableName"] = $table;
            $tableArray["tableSql"] = $this->db->getCreateTable($table);
            $tableArray["columns"] = $this->db->getTableSchema($table)->getColumnNames();

            $date = date("Ymd",time());
            $fileName = "{$table}_{$date}.json";
            $pathdir = "{$this->alias}/{$table}";
            if (!is_dir($pathdir))
            {
                FileHelper::createDirectory($pathdir);
            }
            $path = "{$pathdir}/{$fileName}";
            file_put_contents($path,Json::encode($tableArray,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
            $query = new Query();
            $form = $query->from($table);
            $data = new ActiveDataProvider([
                "query"=>$form,
                "pagination"=>[
                    "pageSize"=>1000,
                    "totalCount"=>$form->count(),
                ],
            ]);

            $totalPage = $data->getPagination()->getPageCount();
            for ($page = 1;$page <= $totalPage;$page++){
                $dataFile = "{$pathdir}/{$table}_{$date}_{$page}.data";
                $data->getPagination()->setPage($page);
                $data->setPagination($data->getPagination());
                $data->refresh();
                $models = ArrayHelper::toArray($data->getModels());
                if (!empty($models)) {
                    $rows = [];
                    foreach ($models as $model) {
                        $row = [];
                        foreach ($model as $key=>$value) {
                            $row[] = $value;
                        }
                        $rows[] = $row;
                    }
                    file_put_contents($dataFile,serialize($rows));
                }
            }
            return true;
        }catch (\Exception $exception) {
            \Yii::warning("备份数据库失败:" . $exception->getMessage());
            return false;
        }
    }

    public function getTables()
    {
        $dirs = FileHelper::getChilds($this->alias,"dir");
        return $dirs;
    }

    public function getDateFiles($table)
    {
        if (is_dir("{$this->alias}/{$table}"));
        $files = FileHelper::getChilds("{$this->alias}/{$table}","file");
        $dates = [];
        foreach ($files as $file){
            $fileNameMap = explode("_",$file);
            if (isset($fileNameMap[1]) && !in_array($fileNameMap[1],$dates)) {
                $dates[] = $fileNameMap[1];
            }
        }

        return $dates;
    }

    /**
     * @param string $table
     * @param string $date
     */
    public function reduction($table,$date)
    {
        $tablePath = "{$this->alias}/{$table}";
        if (!is_dir($tablePath)) {
            return false;
        }

        $tableJson = "{$tablePath}/{$table}_{$date}.json";
        if (!is_dir($tableJson)) {
            return false;
        }
        $transaction = $this->db->beginTransaction();
        try{
            $tableArray = file_get_contents($tableJson);
            //删除原表
            $this->db->createCommand()->dropTable($table);
            $this->db->createCommand($tableArray["tableSql"])->execute();
            $index = 1;
            while (is_file("{$this->alias}/{$table}/{$table}_{$date}_{$index}.data")){
                $data = file_get_contents("{$this->alias}/{$table}/{$table}_{$date}_{$index}.data");
                $data = unserialize($data);
                $this->db->createCommand()->batchInsert($table,$tableArray["columns"],$data);
            }
            $transaction->commit();
            return true;
        }catch (\Exception $exception) {
            $transaction->rollBack();
            \Yii::warning("数据库{$table}恢复失败:".$exception->getMessage());
            return false;
        }
    }

    public function delDir($table)
    {
        $tablePath = "{$this->alias}/{$table}";
        return FileHelper::removeDirectory($tablePath);
    }

    public function delTableDates($table,$date) {
        $datePath = "{$this->alias}/{$table}/{$date}";
        return FileHelper::removeDirectory($datePath);
    }
}