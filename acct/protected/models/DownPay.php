<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2023/3/14 0014
 * Time: 11:57
 */
class DownPay{

    protected $objPHPExcel;

    protected $current_row = 0;
    protected $header_title;
    protected $header_string;
    protected $sheet_id=0;
    public $colTwo=2;
    public $th_num=0;

    public function SetHeaderTitle($invalue) {
        $this->header_title = $invalue;
    }

    public function SetHeaderString($invalue) {
        $this->header_string = $invalue;
    }

    public function init() {
        //Yii::$enableIncludePath = false;
        $phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
        spl_autoload_unregister(array('YiiBase','autoload'));
        include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
        $this->objPHPExcel = new PHPExcel();
        $this->setReportFormat();
        $this->outHeader();
    }

    public function setSummaryHeader($headerArr,$bool=false){
        if(!empty($headerArr)){
            for ($i=0;$i<$this->colTwo;$i++){
                $startStr = $this->getColumn($i);
                $this->objPHPExcel->getActiveSheet()->mergeCells($startStr.$this->current_row.':'.$startStr.($this->current_row+1));
            }
            $colOne = 0;
            $colTwo = $this->colTwo;
            foreach ($headerArr as $list){
                $startStr = $this->getColumn($colOne);
                $colspan = key_exists("colspan",$list)?count($list["colspan"])-1:0;
                $this->objPHPExcel->getActiveSheet()
                    ->setCellValueByColumnAndRow($colOne, $this->current_row, $list["name"]);
                $colOne+=$colspan;
                $colOne++;
                $endStr = $this->getColumn($colOne-1);
                if(!empty($colspan)){
                    $this->objPHPExcel->getActiveSheet()->mergeCells($startStr.$this->current_row.':'.$endStr.$this->current_row);
                }
                $background="000000";
                $color="ffffff";
                if(key_exists("background",$list)){
                    $background = $list["background"];
                    $background = explode("#",$background);
                    $background = end($background);
                    $color = key_exists("color",$list)?$list["color"]:"#000000";
                    $color = explode("#",$color);
                    $color = end($color);
                    $endRow = $bool?$this->current_row+1:$this->current_row;
                    $this->setHeaderStyleTwo("{$startStr}{$this->current_row}:{$endStr}{$endRow}",$background,$color);
                }
                if(isset($list["colspan"])){
                    foreach ($list["colspan"] as $item){
                        $this->objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($colTwo, $this->current_row+1, $item["name"]);
                        $colTwo++;
                        $this->th_num++;
                    }
                }else{
                    $this->th_num++;
                }
            }
            $endStr = $this->getColumn($this->th_num-1);
            $this->objPHPExcel->getActiveSheet()->getStyle("A{$this->current_row}:{$endStr}".($this->current_row+1))->applyFromArray(
                array(
                    'font'=>array(
                        'bold'=>true,
                        'color'=>array('rgb'=>$bool?'ffffff':'000000')
                    ),
                    'alignment'=>array(
                        'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    ),
                    'borders'=>array(
                        'allborders'=>array(
                            'style'=>PHPExcel_Style_Border::BORDER_THIN,
                        ),
                    )
                )
            );

            $this->current_row+=2;
        }
        $this->setSummaryWidth();
    }

    public function setUServiceHeader($headerArr){
        if(!empty($headerArr)){
            $endStr = $this->getColumn(count($headerArr)-1);
            $this->objPHPExcel->getActiveSheet()->getStyle("A{$this->current_row}:{$endStr}".($this->current_row))->applyFromArray(
                array(
                    'font'=>array(
                        'bold'=>true,
                    ),
                    'alignment'=>array(
                        'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                        'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER,
                    ),
                    'borders'=>array(
                        'allborders'=>array(
                            'style'=>PHPExcel_Style_Border::BORDER_THIN,
                        ),
                    )
                )
            );
            $colOne = 0;
            foreach ($headerArr as $list){
                $startStr = $this->getColumn($colOne);
                $this->objPHPExcel->getActiveSheet()
                    ->setCellValueByColumnAndRow($colOne, $this->current_row, $list["name"]);

                if(key_exists("background",$list)){
                    $background = $list["background"];
                    $background = explode("#",$background);
                    $background = end($background);
                    $color = key_exists("color",$list)?$list["color"]:"#000000";
                    $color = explode("#",$color);
                    $color = end($color);
                    $this->setHeaderStyleTwo("{$startStr}{$this->current_row}",$background,$color);
                }
                $colOne++;
                $this->th_num++;
            }
            $this->current_row++;
        }
        $this->setSummaryWidth();
    }

    private function setSummaryWidth(){
        for ($col=0;$col<$this->th_num;$col++){
            $width = 13;
            $this->objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setWidth($width);
        }
    }

    public function setSummaryData($data){
        if(key_exists("MO",$data)){//是否有澳門地區的數據
            $moData=$data["MO"];
            unset($data["MO"]);
        }else{
            $moData = array();
        }
        if(!empty($data)){
            foreach ($data as $region=>$regionList){
                if(isset($regionList["list"])&&!empty($regionList["list"])){
                    foreach ($regionList["list"] as $city=>$cityList){
                        $col = 0;
                        foreach ($cityList as $keyStr=>$text){
                            $this->setCellValueForSummary($col, $this->current_row, $text,$keyStr);
                            $col++;
                        }
                        $this->current_row++;
                    }
                }
                //合计
                $col = 0;
                if(isset($regionList["count"])){
                    foreach ($regionList["count"] as $keyStr=>$text){
                        $this->setCellValueForSummary($col, $this->current_row, $text,$keyStr);
                        $col++;
                    }
                }
                $thEndStr = $this->getColumn($this->th_num-1);
                $this->objPHPExcel->getActiveSheet()
                    ->getStyle("A{$this->current_row}:{$thEndStr}{$this->current_row}")
                    ->applyFromArray(
                        array(
                            'font'=>array(
                                'bold'=>true,
                            ),
                            'borders' => array(
                                'top' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                            )
                        )
                    );
                $this->current_row++;
                $this->current_row++;
            }
        }

        if(!empty($moData)){
            $col = 0;
            foreach ($moData as $keyStr=>$text){
                $this->setCellValueForSummary($col, $this->current_row, $text,$keyStr);
                $col++;
            }
        }
    }

    public function setUServiceData($data){
        if(!empty($data)){
            $endStr = $this->getColumn($this->th_num-1);
            foreach ($data as $keyStr=>$list){
                if(count($list)==3){//汇总
                    $this->objPHPExcel->getActiveSheet()
                        ->setCellValueByColumnAndRow(0, $this->current_row, $list["region"]);
                    $this->objPHPExcel->getActiveSheet()
                        ->setCellValueByColumnAndRow(4, $this->current_row, $list["entry_month"]);
                    $this->objPHPExcel->getActiveSheet()
                        ->setCellValueByColumnAndRow(5, $this->current_row, $list["amt"]);
                    $this->objPHPExcel->getActiveSheet()->mergeCells("A".$this->current_row.':D'.$this->current_row);
                    $this->objPHPExcel->getActiveSheet()
                        ->getStyle("A{$this->current_row}:{$endStr}{$this->current_row}")
                        ->applyFromArray(
                            array(
                                'font'=>array(
                                    'bold'=>true,
                                    'color'=>array('rgb'=>strpos($keyStr,'average_')!==false?'FF0000':'000000')
                                ),
                                'borders' => array(
                                    'top' => array(
                                        'style' => PHPExcel_Style_Border::BORDER_THIN
                                    )
                                )
                            )
                        );
                    if (strpos($keyStr,'average_')!==false){
                        $this->current_row++;
                    }
                }else{
                    $col = 0;
                    foreach ($list as $item){
                        $this->objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($col, $this->current_row, $item);
                        $col++;
                    }
                }
                $this->current_row++;
            }
        }
    }

    public function setSalesAnalysisData($data){
        if(!empty($data)){
            $endStr = $this->getColumn($this->th_num-1);
            foreach ($data as $region){
                foreach ($region as $keyStr=>$list){
                    $col = 0;
                    foreach ($list as $item){
                        $this->objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($col, $this->current_row, $item);
                        $col++;
                    }
                    if($keyStr=='count'){//汇总
                        $this->objPHPExcel->getActiveSheet()
                            ->getStyle("A{$this->current_row}:{$endStr}{$this->current_row}")
                            ->applyFromArray(
                                array(
                                    'font'=>array(
                                        'bold'=>true,
                                        'color'=>array('rgb'=>strpos($keyStr,'average_')!==false?'FF0000':'000000')
                                    ),
                                    'borders' => array(
                                        'top' => array(
                                            'style' => PHPExcel_Style_Border::BORDER_THIN
                                        )
                                    )
                                )
                            );
                        $this->current_row++;
                    }
                    $this->current_row++;
                }
            }
        }
    }

    public function setCapacityData($data){
        if(!empty($data)){
            $endStr = $this->getColumn($this->th_num-1);
            foreach ($data as $region){
                $startRow = $this->current_row;
                foreach ($region as $keyStr=>$list){
                    $col = 0;
                    foreach ($list as $item){
                        $this->objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($col, $this->current_row, $item);
                        if($keyStr=='arrKey'){//标题
                            $background = $col<=12?"000000":"334e9b";
                            $nowStr = $this->getColumn($col);
                            $this->objPHPExcel->getActiveSheet()
                                ->getStyle($nowStr.$this->current_row)
                                ->applyFromArray(
                                    array(
                                        'font'=>array(
                                            'bold'=>true,
                                            'color'=>array('rgb'=>'FFFFFF')
                                        ),
                                        'fill'=>array(
                                            'type'=>PHPExcel_Style_Fill::FILL_SOLID,
                                            'startcolor'=>array(
                                                'rgb'=>$background,
                                            ),
                                        ),
                                    )
                                );
                        }
                        $col++;
                    }
                    $this->current_row++;
                }
                if($startRow!=$this->current_row){
                    $endRow = $this->current_row-1;
                    $this->objPHPExcel->getActiveSheet()
                        ->getStyle("A{$startRow}:{$endStr}{$endRow}")
                        ->applyFromArray(
                            array(
                                'borders' => array(
                                    'allborders'=>array(
                                        'style'=>PHPExcel_Style_Border::BORDER_THIN,
                                    ),
                                ),
                            )
                        );
                }
                $this->current_row++;
            }
        }
    }

    public function setSalesAreaData($data,$headArr){
        if(!empty($data)){
            foreach ($data as $region=>$regionList){
                $this->th_num=0;
                $headArr[0]["name"]=$region;
                $this->setSummaryHeader($headArr,true);
                $endStr = $this->getColumn($this->th_num-1);
                $startNum = $this->current_row;
                foreach ($regionList as $list){
                    $col = 0;
                    foreach ($list as $item){
                        $this->objPHPExcel->getActiveSheet()
                            ->setCellValueByColumnAndRow($col, $this->current_row, $item);
                        $col++;
                    }
                    $this->current_row++;
                }
                $this->objPHPExcel->getActiveSheet()->getStyle("A{$startNum}:{$endStr}".($this->current_row-1))->applyFromArray(
                    array(
                        'borders'=>array(
                            'allborders'=>array(
                                'style'=>PHPExcel_Style_Border::BORDER_THIN,
                            ),
                        )
                    )
                );
                $this->current_row++;
                $this->current_row++;
            }
        }
    }

    public function setSalesProdData($data,$keyList){
        if(key_exists("MO",$data)){//是否有澳門地區的數據
            $moData=$data["MO"];
            unset($data["MO"]);
        }else{
            $moData = array();
        }
        if(!empty($data)){
            foreach ($data as $region=>$regionList){
                if(isset($regionList["list"])&&!empty($regionList["list"])){
                    foreach ($regionList["list"] as $city=>$cityList){
                        $col = 0;
                        foreach ($keyList as $keyStr){
                            $text = key_exists($keyStr,$cityList)?$cityList[$keyStr]:"";
                            $this->setCellValueForSummary($col, $this->current_row, $text,$keyStr);
                            $col++;
                        }
                        $this->current_row++;
                    }
                }
                //合计
                $col = 0;
                if(isset($regionList["count"])){
                    foreach ($keyList as $keyStr){
                        $text = key_exists($keyStr,$regionList["count"])?$regionList["count"][$keyStr]:"";
                        $this->setCellValueForSummary($col, $this->current_row, $text,$keyStr);
                        $col++;
                    }
                }
                $thEndStr = $this->getColumn($this->th_num-1);
                $this->objPHPExcel->getActiveSheet()
                    ->getStyle("A{$this->current_row}:{$thEndStr}{$this->current_row}")
                    ->applyFromArray(
                        array(
                            'font'=>array(
                                'bold'=>true,
                            ),
                            'borders' => array(
                                'top' => array(
                                    'style' => PHPExcel_Style_Border::BORDER_THIN
                                )
                            )
                        )
                    );
                $this->current_row++;
                $this->current_row++;
            }
        }

        if(!empty($moData)){
            $col = 0;
            foreach ($keyList as $keyStr){
                $text = key_exists($keyStr,$moData)?$moData[$keyStr]:"";
                $this->setCellValueForSummary($col, $this->current_row, $text,$keyStr);
                $col++;
            }
        }
    }

    public function setListData($data){
        if(!empty($data)){
            $thEndStr = $this->getColumn($this->th_num-1);
            $startRow = $this->current_row;
            foreach ($data as $cityList){
                $col = 0;
                foreach ($cityList as $keyStr=>$text){
                    $this->setCellValueForSummary($col, $this->current_row, $text,$keyStr);
                    $col++;
                }
                $this->current_row++;
            }

            $this->objPHPExcel->getActiveSheet()
                ->getStyle("A{$startRow}:{$thEndStr}".($this->current_row-1))
                ->applyFromArray(
                    array(
                        'borders' => array(
                            'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN
                            )
                        )
                    )
                );
        }
    }

    private function setCellValueForSummary($col,$row,$text,$keyStr=""){
        $this->objPHPExcel->getActiveSheet()
            ->setCellValueByColumnAndRow($col, $row, $text);
        $rgb="000000";
        if(strpos($text,'%')!==false){
            if(!in_array($keyStr,array("new_rate","stop_rate","net_rate"))){
                $rgb =floatval($text)<=60?"a94442":$rgb;
            }
            $rgb =floatval($text)>=100?"00a65a":$rgb;
        }elseif (strpos($keyStr,'net')!==false){ //所有淨增長為0時特殊處理
            if(Yii::t("summary","completed")==$text){
                $rgb="00a65a";
            }elseif (Yii::t("summary","incomplete")==$text){
                $rgb="a94442";
            }
        }
        $str = $this->getColumn($col);
        $this->objPHPExcel->getActiveSheet()
            ->getStyle($str.$row)->applyFromArray(
                array(
                    'font'=>array(
                        'color'=>array('rgb'=>$rgb)
                    )
                )
            );
    }

    protected function setReportFormat() {
        $this->objPHPExcel->getDefaultStyle()->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $this->objPHPExcel->getDefaultStyle()->getFont()
            ->setSize(10);
        $this->objPHPExcel->getDefaultStyle()->getAlignment()
            ->setWrapText(true);
        $this->objPHPExcel->getActiveSheet()->getDefaultRowDimension()
            ->setRowHeight(-1);
    }

    public function outHeader($sheetid=0){
        $this->objPHPExcel->setActiveSheetIndex($sheetid)
            ->setCellValueByColumnAndRow(0, 1, $this->header_title)
            ->setCellValueByColumnAndRow(0, 2, $this->header_string);
        $this->objPHPExcel->getActiveSheet()->getRowDimension(1)->setRowHeight(20);
        $height = $this->colTwo==2?20:50;
        $this->objPHPExcel->getActiveSheet()->getRowDimension(2)->setRowHeight($height);
        $this->objPHPExcel->getActiveSheet()->mergeCells("A1:C1");
        $this->objPHPExcel->getActiveSheet()->mergeCells("A2:C2");
        $this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getFont()
            ->setSize(14)
            ->setBold(true);
        $this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 1)->getAlignment()
            ->setWrapText(false);
        $this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getFont()
            ->setSize(12)
            ->setBold(true)
            ->setItalic(true);
        $this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0, 2)->getAlignment()
            ->setWrapText(true);

        $this->current_row = 4;
    }

    public function outExcel($name="summary"){
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
        ob_start();
        $objWriter->save('php://output');
        $output = ob_get_clean();
        spl_autoload_register(array('YiiBase','autoload'));
        $filename= iconv('utf-8','gbk//ignore',$name);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");;
        header('Content-Disposition:attachment;filename="'.$filename.'.xlsx"');
        header("Content-Transfer-Encoding:binary");
        echo $output;
    }

    protected function setHeaderStyleTwo($cells,$bg="AFECFF",$color="000000") {
        $styleArray = array(
            'font'=>array(
                'bold'=>true,
                'color'=>array(
                    'argb'=>$color,
                ),
            ),
            'alignment'=>array(
                'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
                'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER,
            ),
            'borders'=>array(
                'allborders'=>array(
                    'style'=>PHPExcel_Style_Border::BORDER_THIN,
                ),
            ),
            'fill'=>array(
                'type'=>PHPExcel_Style_Fill::FILL_SOLID,
                'startcolor'=>array(
                    'argb'=>$bg,
                ),
            ),
        );
        $this->objPHPExcel->getActiveSheet()->getStyle($cells)
            ->applyFromArray($styleArray);
    }
    protected function getColumn($index){
        $index++;
        $mod = $index % 26;
        $quo = ($index-$mod) / 26;

        if ($quo == 0) return chr($mod+64);
        if (($quo == 1) && ($mod == 0)) return 'Z';
        if (($quo > 1) && ($mod == 0)) return chr($quo+63).'Z';
        if ($mod > 0) return chr($quo+64).chr($mod+64);
    }

    public function addSheet($sheet_name){
        $this->current_row=0;
        $this->th_num=0;
        $this->sheet_id++;
        $this->objPHPExcel->createSheet(); //插入工作表
        $this->objPHPExcel->setActiveSheetIndex($this->sheet_id); //切换到新创建的工作表
        $this->setSheetName($sheet_name);
    }

    public function setSheetName($sheet_name){
        $this->objPHPExcel->getActiveSheet()->setTitle($sheet_name);
    }
}