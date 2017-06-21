<?php

class ExcelTool {
	protected $objPHPExcel;

	public function start() {
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		spl_autoload_unregister(array('YiiBase','autoload'));
		include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
	}
	
	public function end() {
		spl_autoload_register(array('YiiBase','autoload'));
	}
	
	public function newFile() {
		$this->objPHPExcel = new PHPExcel();
	}
	
	public function readFile($fname) {
		$this->objPHPExcel = file_exists($fname) ? PHPExcel_IOFactory::createReader('Excel2007')->load($fname) : null;
	}
	
	public function getColumn($index){
		$index++;
		$mod = $index % 26;
		$quo = ($index-$mod) / 26;
	
		if ($quo == 0) return chr($mod+64);
		if (($quo == 1) && ($mod == 0)) return 'Z';
		if (($quo > 1) && ($mod == 0)) return chr($quo+63).'Z';
		if ($mod > 0) return chr($quo+64).chr($mod+64);
	}

	public function createSheet() {
		$this->objPHPExcel->createSheet();
	}
	
	public function setActiveSheet($index) {
		$this->objPHPExcel->setActiveSheetIndex($index);
	}
	
	public function getActiveSheet() {
		return $this->objPHPExcel->getActiveSheet();
	}
	
	public function setCellValue($col, $row, $value) {
		$loc = $col.$row;
		$this->objPHPExcel->getActiveSheet()->setCellValue($loc, $value);
	}

	public function getCellValue($col, $row) {
		$loc = $col.$row;
		return $this->objPHPExcel->getActiveSheet()->getCell($loc)->getValue();
	}
	public function setReportDefaultFormat() {
		$this->objPHPExcel->getDefaultStyle()->getFont()
			->setSize(10);
		$this->objPHPExcel->getDefaultStyle()->getAlignment()
			->setWrapText(true);
		$this->objPHPExcel->getActiveSheet()->getDefaultRowDimension()
			->setRowHeight(-1);
	}
	
	public function getOutput() {
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: inline;filename="01simple.xlsx"');
		header('Cache-Control: max-age=0');
	
		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		ob_start();
		$objWriter->save('php://output');
		$output = ob_get_clean();
		
		return $output;
	}
	
	public function generateOutput($data, $sheetid) {
		if ($sheetid > 0) {
			$this->objPHPExcel->createSheet();
			$sheet = $this->objPHPExcel->setActiveSheetIndex($sheetid);
			$sheet->setTitle($this->header_title);
		} else {
			$this->objPHPExcel->setActiveSheetIndex(0)->setTitle($this->header_title);
		}
		$this->setReportFormat();
		$this->outHeader($sheetid);
		$this->outDetail($data);
	}
	
	public function writeReportTitle($title='', $subtitle='') {
		$this->objPHPExcel->getActiveSheet()
            ->setCellValueByColumnAndRow(0,1, $title)
			->setCellValueByColumnAndRow(0,2, $subtitle);
		$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,1)->getFont()
			->setSize(14)
			->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,1)->getAlignment()
			->setWrapText(false);		
		$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,2)->getFont()
			->setSize(12)
			->setBold(true)
			->setItalic(true);
		$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,2)->getAlignment()
			->setWrapText(false);		
	}

	public function setCellFont($col, $row, $definition=array()) {
		if (isset($definition['size']) && is_numeric($definition['size']))
			$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$row)->getFont()->setSize($definition['size']);
		if (isset($definition['italic']) && is_bool($definition['italic']))
			$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$row)->getFont()->setItalic($definition['italic']);
		if (isset($definition['bold']) && is_bool($definition['bold']))
			$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$row)->getFont()->setItalic($definition['bold']);
			
	}
	
	public function setColWidth($col, $width) {
		if ($width > 0)
			$this->objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setWidth($width);
	}
	
	public function setRangeStyle($cells,$bold,$italic,$halign,$valign,$border,$fill) {
		$styleArray = array(
			'font'=>array(
				'bold'=>$bold,
				'italic'=>$italic,
			),
			'alignment'=>array(
				'horizontal'=>($halign=='C' 
								? PHPExcel_Style_Alignment::HORIZONTAL_CENTER
								: ($halign=='R'
									? PHPExcel_Style_Alignment::HORIZONTAL_RIGHT
									: PHPExcel_Style_Alignment::HORIZONTAL_LEFT
								)
							),
				'vertical'=>($valign='C'
								? PHPExcel_Style_Alignment::VERTICAL_CENTER
								: ($valign=='B'
									? PHPExcel_Style_Alignment::VERTICAL_BOTTOM
									: PHPExcel_Style_Alignment::VERTICAL_TOP
								)
							),
			),
			'borders'=>array(
				'outline'=>array(
					'style'=>($border ? PHPExcel_Style_Border::BORDER_THIN : PHPExcel_Style_Border::BORDER_NONE),
				),
			),
		);
		switch ($border) {
			case 'outline':
				$styleArray['borders'] = array('outline'=>array('style'=>PHPExcel_Style_Border::BORDER_THIN),);
				break;
			case 'allborders':
				$styleArray['borders'] = array('allborders'=>array('style'=>PHPExcel_Style_Border::BORDER_THIN),);
				break;
			default:
				$styleArray['borders'] = array('allborders'=>array('style'=>PHPExcel_Style_Border::BORDER_NONE),);
		}
		if ($fill)
			$styleArray['fill'] = array(
						'type'=>PHPExcel_Style_Fill::FILL_SOLID,
						'startcolor'=>array(
							'argb'=>'AFECFF',
						),
					);

		$this->objPHPExcel->getActiveSheet()->getStyle($cells)
			->applyFromArray($styleArray);
	}
	
	public function setCellStyle($col, $row, $definition=array()) {
		if (!empty($definition)) {
			if (isset($definition['numberformat'])) {
				$format = $definition['numberformat'];
				$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row)
					->getNumberFormat()->setFormatCode($format);
			}
		}
	}
	
	public function writeCell($col, $row, $text, $definition=array()) {
		if (empty($definition)) {
			$align = PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
			$valign = PHPExcel_Style_Alignment::VERTICAL_TOP;
			$wraptext = true;
		} else {
			$align = isset($definition['align']) 
					? ($definition['align']=='C' 
						? PHPExcel_Style_Alignment::HORIZONTAL_CENTER 
						: ($definition['align']=='R' ? PHPExcel_Style_Alignment::HORIZONTAL_RIGHT : PHPExcel_Style_Alignment::HORIZONTAL_LEFT)
						)
					: PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
			$valign = isset($definition['valign']) 
					? ($definition['valign']=='C' 
						? PHPExcel_Style_Alignment::VERTICAL_CENTER 
						: ($definition['align']=='B' ? PHPExcel_Style_Alignment::VERTICAL_BOTTOM : PHPExcel_Style_Alignment::VERTICAL_TOP)
						)
					: PHPExcel_Style_Alignment::VERTICAL_TOP;
			$wraptext = isset($definition['wraptext']) && is_bool($definition['wraptext']) ? $definition['wraptext'] : true; 
		}
		
		$this->objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $text);
		$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col, $row)->getAlignment()
			->setHorizontal($align)
			->setVertical($valign)
			->setWrapText($wraptext);
	}
}
?>