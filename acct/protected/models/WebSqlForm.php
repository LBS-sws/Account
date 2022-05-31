<?php

class WebSqlForm extends CFormModel
{
	/* User Fields */
    private $dataURl;

    //type:table、column、update
    private $mysqlFile=array(
        array('date'=>'2022-05-31','fileName'=>'2022-05-31(直升機機制相關的數據表).sql','table_name'=>'acc_plane','type'=>'table','column'=>'','username'=>'shenchao','remark'=>'测试'),
    );

    public function init(){
        $this->dataURl = Yii::app()->basePath."/data/";
    }

    public function validateTableAll(){
        foreach ($this->mysqlFile as $row){
            $this->validateRow($row);
        }
    }

    public function validateTableForDate($date=""){
        $date=empty($date)?date("Y-m-d"):$date;
        foreach ($this->mysqlFile as $row){
            if($row['date']>=$date){
                $this->validateRow($row);
            }
        }
    }

    private function validateRow($row){
        $tableBool = self::searchMysqlTables($row["table_name"]);
        $insertSql = $this->readFile($row["fileName"]);
        if(empty($insertSql)){
            return;
        }
        switch ($row["type"]){
            case "column"://给已有的数据表新增字段
                $columnBool = self::searchMysqlColumns($row["table_name"],$row["column"]);
                if(!$tableBool&&$columnBool){ //数据表存在字段不存在
                    Yii::app()->db->createCommand($insertSql)->execute();
                }
                break;
            case "table":
                if($tableBool){ //数据表不存在
                    Yii::app()->db->createCommand($insertSql)->execute();
                }
                break;
            case "update":
                if(!$tableBool){ //数据表存在
                    Yii::app()->db->createCommand($insertSql)->execute();
                }
                break;
        }
    }

    private function readFile($fileName){
        $fileName = iconv('UTF-8','GBK',$fileName);
        $fileUrl = $this->dataURl.$fileName;
        $content="";
        if(file_exists($fileUrl)){
            $content=file_get_contents($fileUrl);
        }else{
            echo "not find file:{$fileName}<br/>";
        }
        return $content;
    }

    public static function searchMysqlColumns($table_name,$column){
        $sql="SELECT count(*) FROM information_schema.COLUMNS WHERE table_name = '{$table_name}'";
        $sql.=" and column_name = '{$column}'";
        $count = Yii::app()->db->createCommand($sql)->queryScalar();
        if(empty($count)){
            return true;
        }
        return false;
    }

    public static function searchMysqlTables($table_name){
        $sql="SELECT count(*) FROM information_schema.tables WHERE table_name = '{$table_name}'";
        $count = Yii::app()->db->createCommand($sql)->queryScalar();
        if(empty($count)){
            return true;
        }
        return false;
    }
}