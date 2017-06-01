<?php

class MyExcel {
	protected $objPHPExcel;

	protected $current_row = 0;
	
	protected $line_def = array();
	
	protected $hdr_def = array();
	
	protected $group_def = array();
	
	protected $subline_def = array();
	
	protected $line_group_def = array();

	protected $report_structure = array();

	public function SetHeaderTitle($invalue) {
		$this->header_title = $invalue;
	}

	public function SetHeaderString($invalue) {
		$this->header_string = $invalue;
	}
	
	public function SetLineDefinition($inarray) {
		$this->line_def= $inarray;
	}
	
	public function SetLineGroupDefinition($inarray) {
		$this->line_group_def= $inarray;
	}

	public function SetHeaderDefinition($inarray) {
		$this->hdr_def= $inarray;
	}

	public function SetGroupDefinition($inarray) {
		$this->group_def = $inarray;
	}

	public function SetSublineDefinition($inarray) {
		$this->subline_def = $inarray;
	}

	public function SetReportStructure($invalue) {
		$this->report_structure = $invalue;
	}

	public function init() {
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		spl_autoload_unregister(array('YiiBase','autoload'));
		include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
		$this->objPHPExcel = new PHPExcel();
	}

	public function getOutput() {
//		header('Content-Type: application/vnd.ms-excel');
//		header('Content-Disposition: inline;filename="01simple.xlsx"');
//		header('Cache-Control: max-age=0');
	
		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		ob_start();
		$objWriter->save('php://output');
		$output = ob_get_clean();
		
		spl_autoload_register(array('YiiBase','autoload'));
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
	
	public function generate($data) {
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		spl_autoload_unregister(array('YiiBase','autoload'));
		include($phpExcelPath . DIRECTORY_SEPARATOR . 'PHPExcel.php');
		$this->objPHPExcel = new PHPExcel();
/*
		$this->objPHPExcel->getProperties()->setCreator("LBS")
			->setLastModifiedBy("LBS")
			->setTitle()
			->setSubject("PDF Test Document");
*/		
		$this->setReportFormat();

		$this->outHeader();

		$this->outDetail($data);
		
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: inline;filename="01simple.xlsx"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		ob_start();
		$objWriter->save('php://output');
		$output = ob_get_clean();
//		exit();
//		Yii::app()->end();
		
		spl_autoload_register(array('YiiBase','autoload'));
		return $output;
		
	}

	protected function setReportFormat() {
		$this->objPHPExcel->getDefaultStyle()->getFont()
			->setSize(10);
		$this->objPHPExcel->getDefaultStyle()->getAlignment()
			->setWrapText(true);
		$this->objPHPExcel->getActiveSheet()->getDefaultRowDimension()
			->setRowHeight(-1);
	}
	
	protected function getLabelLevel($item, $level) {
		$rtn = $level;
		foreach ($item as $def) {
			if (is_array($def)) {
				$sublevel = isset($def['child']) ? $level+1 : $level;
				$depth = $this->getLabelLevel($def, $sublevel);
				if ($depth > $rtn) $rtn = $depth;
			}
		}
		return ($rtn==0 ? 1 :$rtn);
	}
	
	protected function generateHeaderBlock($item, &$col, $level) {
		$startcol = $col;
		
		foreach ($item['child'] as $child) {
			if (is_array($child)) {
				$this->generateHeaderBlock($child, $col, $level+1);
			} else {
				$this->fillHeaderCell($col, $this->current_row+$level, $this->line_def[$child]['label'], $this->line_def[$child]['width']);
				$column = $this->getColumn($col);
				$cell = $column.($this->current_row+$level);
				$this->setHeaderStyle($cell);
				$col++;
			}
		}
		
		$this->fillHeaderCell($startcol, $this->current_row+$level-1, $item['label'], 0);

		$stcolumn = $this->getColumn($startcol);
		$edcolumn = $this->getColumn($col-1);
		$range = $stcolumn.($this->current_row+$level-1).':'.$edcolumn.($this->current_row+$level-1);
		$this->objPHPExcel->getActiveSheet()->mergeCells($range);
		$this->setHeaderStyle($range);
	}
	
	protected function outHeader($sheetid=0) {
		$this->objPHPExcel->setActiveSheetIndex($sheetid)
            ->setCellValueByColumnAndRow(0,1, $this->header_title)
			->setCellValueByColumnAndRow(0,2, $this->header_string);
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

		$this->current_row = 4;
		if (!empty($this->hdr_def)) {
			$level = $this->getLabelLevel($this->hdr_def, 1);

			$col = 0;
			foreach ($this->hdr_def as $item) {
				if (is_array($item)) {
					$startlvl = 1;
					$this->generateHeaderBlock($item, $col, $startlvl);
				} else {
					$this->fillHeaderCell($col, $this->current_row, $this->line_def[$item]['label'], $this->line_def[$item]['width']);

					$column = $this->getColumn($col);
					$range = $column.$this->current_row.':'.$column.($this->current_row+$level-1);
					$this->objPHPExcel->getActiveSheet()->mergeCells($range);
					
					$this->setHeaderStyle($range);

					$col++;
				}
			}
			$this->current_row += $level-1;
		} else {
			$col = 0;
			foreach ($this->line_def as $key=>$item) {
				$this->fillHeaderCell($col, $this->current_row, $item['label'], $item['width']);
				$column = $this->getColumn($col);
				$cell = $column.$this->current_row;
				$this->setHeaderStyle($cell);	
				$col++;
			}
		}
		$this->current_row++;
	}
	
	protected function outDetail($data) {
		if (!empty($data)) {
			$buffer_g = array();
			$buffer_l = array();
			$buffer_lg = array();

			foreach ($data as $rows) {
			// Print Group Header
				if (!empty($this->group_def)) {
					$change = false;
					foreach ($this->group_def as $idx=>$group) {
						$current = array();
						foreach ($group as $key=>$def) {
							$current[$key] =$rows[$key];
						}
						$diff = array_key_exists($idx,$buffer_g) ? array_diff($buffer_g[$idx], $current) : array();
						if ($change || !array_key_exists($idx,$buffer_g) || !empty($diff)) {
							$change = true;
							$buffer_g[$idx] = $current;
							$this->outGroupHeader($rows, $group, $idx);
						}
					}
				}
			// Print Detail Line	
				if (empty($this->subline_def)) {
					if (!empty($this->report_structure)) {
						$this->outLineStructure($rows);
					} elseif (empty($this->line_group_def)) {
						$this->outLine($rows, $this->line_def);
					} else {
						$current = array();
						foreach ($this->line_group_def as $key) {
								$current[$key] =$rows[$key];
						}
						$diff = array_diff($buffer_lg, $current);
						$repeat = (!empty($buffer_lg) && empty($diff));
						$this->outLineHiddenRepeat($rows, $this->line_def, $this->line_group_def, $repeat);
						$buffer_lg = $current;
					}
				} else {
					$current = array();
					foreach ($this->line_def as $key=>$def) {
						$current[$key] =$rows[$key];
					}
					$diff = array_diff($buffer_l, $current);
					if (empty($buffer_l) || !empty($diff)) {
						$buffer_l = $current;
						$this->outLine($rows, $this->line_def);
					}
				// Print Sub Line
					foreach ($this->subline_def as $idx=>$subline) {
						$this->outSubline($rows, $subline, $idx);
					}
				}
			}
		} 
    }
	
	protected function outGroupHeader($data, $definition, $level) {
		$col = 0;
		$totalcol = count($this->line_def);
		$column1 = $this->getColumn($col);
		$column2 = $this->getColumn($totalcol-1);
		
		$this->current_row++;
		foreach ($definition as $key=>$def)  {
			$text = str_repeat('*',$level).' '.$def['label'].': '.$data[$key];

			$this->objPHPExcel->getActiveSheet()
				->setCellValueByColumnAndRow(0, $this->current_row, $text);
			$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$this->current_row)->getFont()
				->setBold(true)
				->setItalic(true);
			$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$this->current_row)->getFill()
				->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
				->getStartColor()
				->setRGB('AFECFF');
			$this->objPHPExcel->getActiveSheet()->mergeCells($column1.$this->current_row.':'.$column2.$this->current_row);
			
			$this->current_row++;
		}
	}

	protected function outLine($data, $definition) {
		$col = 0;
		foreach ($definition as $key=>$def)  {
			$text = $data[$key];
			$align = ($def['align']=='C' ? PHPExcel_Style_Alignment::HORIZONTAL_CENTER :
						($def['align']=='R' ? PHPExcel_Style_Alignment::HORIZONTAL_RIGHT :
							PHPExcel_Style_Alignment::HORIZONTAL_LEFT
						)
					);
			$this->objPHPExcel->getActiveSheet()
				->setCellValueByColumnAndRow($col, $this->current_row, $text);
			$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$this->current_row)->getAlignment()
				->setHorizontal($align)
				->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
			$col++;
		}
		$this->current_row++;
	}

	protected function outLineStructure($data) {
		$col = 0;
		$crow = $this->current_row;
		foreach ($this->report_structure as $item) {
			if (is_array($item)) {
				foreach ($data['detail'] as $idx=>$row) {
					$ccol = $col;
					foreach ($item as $key) {
						$text = $row[$key];
						$align = ($this->line_def[$key]['align']=='C' ? PHPExcel_Style_Alignment::HORIZONTAL_CENTER :
									($this->line_def[$key]['align']=='R' ? PHPExcel_Style_Alignment::HORIZONTAL_RIGHT :
										PHPExcel_Style_Alignment::HORIZONTAL_LEFT
									)
								);
						$this->objPHPExcel->getActiveSheet()
							->setCellValueByColumnAndRow($ccol, $crow, $text);
						$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($ccol,$crow)->getAlignment()
							->setHorizontal($align)
							->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
						$ccol++;
					}
					$crow++;
				}
				$col = $ccol;
			} else {
				$text = $data[$item];
				$align = ($this->line_def[$item]['align']=='C' ? PHPExcel_Style_Alignment::HORIZONTAL_CENTER :
							($this->line_def[$item]['align']=='R' ? PHPExcel_Style_Alignment::HORIZONTAL_RIGHT :
								PHPExcel_Style_Alignment::HORIZONTAL_LEFT
							)
						);
				$this->objPHPExcel->getActiveSheet()
					->setCellValueByColumnAndRow($col, $this->current_row, $text);
				$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$this->current_row)->getAlignment()
					->setHorizontal($align)
					->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
				$col++;
			}
		}
		$this->current_row = $crow;
	}
	
	protected function outLineHiddenRepeat($data, $definition, $group_def, $repeat) {
		$col = 0;
		foreach ($definition as $key=>$def)  {
			$text = $data[$key];
			$align = ($def['align']=='C' ? PHPExcel_Style_Alignment::HORIZONTAL_CENTER :
						($def['align']=='R' ? PHPExcel_Style_Alignment::HORIZONTAL_RIGHT :
							PHPExcel_Style_Alignment::HORIZONTAL_LEFT
						)
					);

			if (!($repeat && in_array($key, $group_def))) {
				$this->objPHPExcel->getActiveSheet()
					->setCellValueByColumnAndRow($col, $this->current_row, $text);
				$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$this->current_row)->getAlignment()
					->setHorizontal($align)
					->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
			}
			$col++;
		}
		$this->current_row++;
	}

	protected function outSubline($data, $definition, $level) {
		$col = 1;
		foreach ($definition as $key=>$def)  {
			$label = $def['label'].': ';
			$text = $data[$key];
			$align = ($def['align']=='C' ? PHPExcel_Style_Alignment::HORIZONTAL_CENTER :
						($def['align']=='R' ? PHPExcel_Style_Alignment::HORIZONTAL_RIGHT :
							PHPExcel_Style_Alignment::HORIZONTAL_LEFT
						)
					);

			$this->objPHPExcel->getActiveSheet()
				->setCellValueByColumnAndRow($col, $this->current_row, $label);
			$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$this->current_row)->getFont()
				->setBold(true)
				->setItalic(true);
			$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$this->current_row)->getAlignment()
				->setHorizontal($align)
				->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);

			$this->objPHPExcel->getActiveSheet()
				->setCellValueByColumnAndRow($col+1, $this->current_row, $text);
			$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col+1,$this->current_row)->getAlignment()
				->setHorizontal($align)
				->setVertical(PHPExcel_Style_Alignment::VERTICAL_TOP);
			
			$this->current_row++;
		}
	}
	
	protected function fillHeaderCell($col, $row, $text, $width) {
		$this->objPHPExcel->getActiveSheet()
			->setCellValueByColumnAndRow($col, $row, $text);
		if ($width > 0)
			$this->objPHPExcel->getActiveSheet()->getColumnDimensionByColumn($col)->setWidth($width);
/*
		$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$row)->getFont()
			->setBold(true);
		$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$row)->getAlignment()
			->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
			->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		$this->objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($col,$row)->getFill()
			->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
			->getStartColor()
			->setRGB('AFECFF');
*/
	}

	protected function setHeaderStyle($cells) {
		$styleArray = array(
			'font'=>array(
				'bold'=>true,
			),
			'alignment'=>array(
				'horizontal'=>PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'=>PHPExcel_Style_Alignment::VERTICAL_CENTER,
			),
			'borders'=>array(
				'outline'=>array(
					'style'=>PHPExcel_Style_Border::BORDER_THIN,
				),
			),
			'fill'=>array(
				'type'=>PHPExcel_Style_Fill::FILL_SOLID,
				'startcolor'=>array(
					'argb'=>'AFECFF',
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

	public function readFile($fname) {
		$this->objPHPExcel = file_exists($fname) ? PHPExcel_IOFactory::createReader('Excel2007')->load($fname) : null;
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
}
?>