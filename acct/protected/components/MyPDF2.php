<?php
//Yii::import('ext.tcpdf.*');
require_once(dirname(__FILE__).'/../extensions/tcpdf/config/tcpdf_config.php');
require_once(dirname(__FILE__).'/../extensions/tcpdf/tcpdf.php');

class MyPDF2 extends TCPDF {
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

	//Page header

	public function getHeaderHeight() {
		if (empty($this->hdr_def)) {
			return $this->getLabelHeight();
		} else {
			return $this->getLabelHeightEx();
		}
	}

	protected function getLabelHeight() {
		$rtn = 1;
		foreach ($this->line_def as $key=>$def) {
			$w = $def['width'];
			$txt = $def['label'];
			$reseth = true;
			$autopadding = true;
			$cellpadding = '';
			$border = 1;
			
			$h = $this->getStringHeight($w, $txt, $reseth, $autopadding, $cellpadding, $border);
			if ($h > $rtn) $rtn = $h;
		}
		return ($rtn);
	}

	protected function getLabelHeightEx() {
		$rtn = 1;
		$reseth = true;
		$autopadding = true;
		$cellpadding = '';
		$border = 1;

		foreach ($this->hdr_def as $def) {
			if (is_array($def)) {
				$h = $this->calcHeaderBlockHeight($def);
			} else {
				$w = $this->line_def[$def]['width'];
				$txt = $this->line_def[$def]['label'];
			
				$h = $this->getStringHeight($w, $txt, $reseth, $autopadding, $cellpadding, $border);
			}
			if ($h > $rtn) $rtn = $h;
		}
		return ($rtn);
	}

	protected function calcHeaderBlockHeight($item) {
		$rtn = 0;
		$reseth = true;
		$autopadding = true;
		$cellpadding = '';
		$border = 1;

		$w = $this->calcHeaderBlockWidth($item);
		$rtn += $this->getStringHeight($w, $item['label'], $reseth, $autopadding, $cellpadding, $border);

		$h = 0;
		foreach ($item['child'] as $child) {
			if (is_array($child)) {
				$tmp = $this->calcHeaderBlockHeight($child);
			} else {
				$tmp = $this->getStringHeight($w, $this->line_def[$child]['label'], $reseth, $autopadding, $cellpadding, $border);
			}
			if ($tmp > $h) $h = $tmp;
		}
		
		return $rtn + $h;
	}

	protected function calcHeaderBlockWidth($item) {
		$rtn = 0;
		foreach ($item['child'] as $child) {
			if (is_array($child)) {
				$rtn += $this->calcHeaderBlockWidth($child);
			} else {
				$rtn += $this->line_def[$child]['width'];
			}
		}
		return $rtn;
	}
	
	public function Header() {
		// Set font
		// Title
		$this->SetFont('droidsansfallback', 'B', 16, '', false);
		$this->Cell(0, 8, $this->header_title, 0, false, 'C', 0, '', 0, true, 'C', 'M');
		$this->Ln(4);
		
		$this->SetFont('droidsansfallback', 'B', 8, '', false);
		$this->Cell(0, 8, $this->header_string, 0, false, 'L', 0, '', 0, true, 'T', 'M');
		$this->Ln(8);
		
		$this->SetFillColor(160, 160, 160);
		$this->SetFont('droidsansfallback', 'B', 8, '', false);
		end($this->line_def);
		$lastkey = key($this->line_def);
		if (!empty($this->hdr_def)) {
			$h = $this->getLabelHeightEx($this->hdr_def, $this->line_def);
			$x = '';
			foreach ($this->hdr_def as $item) {
				if (is_array($item)) {
					$this->generateHeaderBlock($item, $this->line_def, $h, $x, $lastkey);
				} else {
					$ln = ($item==$lastkey) ? 1 : 0;
					$this->MultiCell($this->line_def[$item]['width'], $h, $this->line_def[$item]['label'], 1, 'C', 
							true, $ln, $x, '', true, 0, false, true, $h, 'B');
				}
				$x = $this->GetX();
			}
		} else {
			$h = $this->getLabelHeight($this->line_def);
			foreach ($this->line_def as $key=>$item) {
				$ln = ($key==$lastkey) ? 1 : 0;
				$this->MultiCell($item['width'], $h, $item['label'], 1, 'C', 
						true, $ln, '', '', true, 0, false, true, $h, 'B');
			}
		}

//		$style = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
//		$this->Line(5, 27, 292, 27, $style);
	}

	protected function generateHeaderBlock($item, $definition, $h, $x, $lastkey) {
		$reseth = true;
		$autopadding = true;
		$cellpadding = '';
		$border = 1;

		$y = $this->GetY();
		$w = $this->calcHeaderBlockWidth($item, $definition);
		$ht = $this->getStringHeight($w, $item['label'], $reseth, $autopadding, $cellpadding, $border);
		$this->MultiCell($w, 0, $item['label'], 1, 'C', 
				true, 2, $x, '', true, 0, false, true, 0, 'B');
		$xt = $x;
		foreach ($item['child'] as $child) {
			if (is_array($child)) {
				$this->generateHeaderBlock($child, $definition, $h-$ht, $xt, $lastkey);
			} else {
				$ln = ($child==$lastkey) ? 1 : 0;
				$this->MultiCell($definition[$child]['width'], $h-$ht, $definition[$child]['label'], 1, 'C', 
						true, $ln, $xt, '', true, 0, false, true, $h-$ht, 'B');
			}
			$xt = $this->GetX();
		}
		$this->SetY($y,false);
	}
	
	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('droidsansfallback', 'I', 8, '', false);
		// Page number
		$this->Cell(0, 10, Yii::t('report','Page').' '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
	}

	protected function getFieldHeight($data) {
		$rtn = 1;
		$reseth = true;
		$autopadding = true;
		$cellpadding = '';
		$border = 1;

		foreach ($data as $key=>$value) {
			if (is_array($value)) {
				$h = $this->calcBlockHeight($value, $this->line_def);
			} else {
				$w = $this->line_def[$key]['width'];
				$txt = $value;
			
				$h = $this->getStringHeight($w, $txt, $reseth, $autopadding, $cellpadding, $border);
			}
			if ($h > $rtn) $rtn = $h;
		}
		return ($rtn);
	}

	protected function calcBlockHeight($rows) {
		$rtn = 0;
		$reseth = true;
		$autopadding = true;
		$cellpadding = '';
		$border = 1;

		foreach ($rows as $row) {
			$ht = 0;
			foreach ($row as $key=>$value) {
				$w = $this->line_def[$key]['width'];
				$h = $this->getStringHeight($w, $value, $reseth, $autopadding, $cellpadding, $border);
				if ($h > $ht) $ht = $h;
			}
			$rtn += $ht;
		}
		
		return $rtn;
	}

	protected function generateBlock($data, $item, $height, $lastkey) {
		$reseth = true;
		$autopadding = true;
		$cellpadding = '';
		$border = 1;

		$hx = $this->calcBlockHeight($data);
		$flag = ($height > $hx);
		
		$y = $this->GetY();
		$x = $this->GetX();
		$hc = 0;

		$yt = $y;
		foreach ($data as $idx=>$row) {
			$ht = 0;
			foreach ($item as $key) {
				$w = $this->line_def[$key]['width'];
				$h = $this->getStringHeight($w, $row[$key], $reseth, $autopadding, $cellpadding, $border);
				if ($h > $ht) $ht = $h;
			}
			if ($idx==count($data)-1) {
				if ($flag) $ht = $height - $hc;
			} else {
				$hc += $ht;
			}
			
			$xt = $x;
			foreach ($item as $key) {
				$w = $this->line_def[$key]['width'];
				$ln = ($key==$lastkey) ? 1 : 0;
				$this->MultiCell($w, $ht, $row[$key], 1, $this->line_def[$key]['align'], 
						false, $ln, $xt, $yt, true, 0, false, true, $ht, 'T');
				$xt = $this->GetX();
			}
			$yt += $ht;
		}
		$this->SetY($y,false);
	}

/*
TCPDF::MultiCell	(	 	$w,
 	$h,
 	$txt,
 	$border = 0,
 	$align = 'J',
 	$fill = false,
 	$ln = 1,
 	$x = '',
 	$y = '',
 	$reseth = true,
 	$stretch = 0,
 	$ishtml = false,
 	$autopadding = true,
 	$maxh = 0,
 	$valign = 'T',
 	$fitcell = false 
)		
*/	
	public function Content($data) {
        // Colors, line width and bold font
        $this->SetFillColor(255, 0, 0);
        $this->SetTextColor(255);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.1);

        // Color and font restoration
		$this->SetFillColor(80, 80, 80);
        $this->SetTextColor(0);
        $this->SetFont('droidsansfallback', '', 8, '', false);
 //       $this->SetFont('msungstdlight', '', 8, '', false);

        // Data
// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
		$h = 6;
		$border = 0;
        $fill = 0;
		$x = '';
		$y = '';
		$reseth = true;
		$stretch = 0;
		$ishtml = false;
		$autopadding = true;
		$maxh = 0;
		$valign = 'T';
		$fitcell = false;

		if (!empty($data)) {
			$buffer_g = array();
			$buffer_l = array();
			$buffer_lg = array();

			if (!empty($this->report_structure)) {
				$rstruct = array_values($this->report_structure);
				$lastkey = end($rstruct);
			}
			
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
						$h = $this->getFieldHeight($rows);
				
						foreach ($this->report_structure as $item) {
							if (is_array($item)) {
								$this->generateBlock($rows['detail'], $item, $h, $lastkey);
							} else {
								$w = $this->line_def[$item]['width'];
								$halign = $this->line_def[$item]['align'];
								$ln = ($item==$lastkey) ? 1 : 0;
								$this->MultiCell($w, $h, $rows[$item], 1, $halign, $fill, $ln, $x, $y, $reseth, 
									$stretch, $ishtml, $autopadding, $h, $valign, $fitcell);
							}
						}

						$this->Ln(2);
					} elseif (empty($this->line_group_def)) {
						$this->outLine($rows, $this->line_def, $fill);
					} else {
						$current = array();
						foreach ($this->line_group_def as $key) {
								$current[$key] =$rows[$key];
						}
						$diff = array_diff($buffer_lg, $current);
						$repeat = (!empty($buffer_lg) && empty($diff));
						$this->outLineHiddenRepeat($rows, $this->line_def, $this->line_group_def, $fill, $repeat);
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
						$this->outLine($rows, $this->line_def, $fill);
					}
				// Print Sub Line
					foreach ($this->subline_def as $idx=>$subline) {
						$this->outSubline($rows, $subline, $idx);
					}
				}

				if (empty($this->report_structure)) $fill=!$fill;
			}
		} else {
			$this->MultiCell(0, $h, '--'.Yii::t('report','Nil').'--', $border, 'C', $fill, 1, $x, $y, $reseth, 
				$stretch, $ishtml, $autopadding, $maxh, $valign, $fitcell);
		}
    }
	
	protected function getLineHeight($data, $definition) {
		$rtn = 6;
		foreach ($definition as $key=>$def) {
			$w = $def['width'];
			$txt = $data[$key];
			$reseth = true;
			$autopadding = true;
			$cellpadding = '';
			$border = 1;
			
			$h = $this->getStringHeight($w, $txt, $reseth, $autopadding, $cellpadding, $border);
			if ($h > $rtn) $rtn = $h;
		}
		return $rtn;
	}
	
	protected function outGroupHeader($data, $definition, $level) {
		end($definition);
		$lastkey = key($definition);
		$h = $this->getLineHeight($data, $definition);
	
		foreach ($definition as $key=>$def)  {
			$w = $def['width'];
			$txt = str_repeat('*',$level).' '.$def['label'].': '.$data[$key];
			$align = $def['align'];
			$ln = ($key==$lastkey) ? 1 : 0;

			$this->printText($w, $h, $txt, $align, 0, $ln);
		}
	}

	protected function outLine($data, $definition, $fill) {
		end($definition);
		$lastkey = key($definition);
		$h = $this->getLineHeight($data, $definition);
		
		foreach ($definition as $key=>$def)  {
			$w = $def['width'];
			$txt = $data[$key];
			$align = $def['align'];
			$ln = ($key==$lastkey) ? 1 : 0;

			$this->printText($w, $h, $txt, $align, $fill, $ln);
		}
	}

	protected function outLineHiddenRepeat($data, $definition, $group_def, $fill, $repeat) {
		if ($repeat) {
			$buffer = array();
			foreach ($definition as $key=>$def) {
				$buffer[] = $key;
			}
			$diff = array_diff($buffer, $group_def);
			$lastkey = end(array_values($diff));
		} else {
			end($definition);
			$lastkey = key($definition);
		}
		$h = $this->getLineHeight($data, $definition);
		
		foreach ($definition as $key=>$def)  {
			$w = $def['width'];
			$txt = $data[$key];
			$align = $def['align'];
			$ln = ($key==$lastkey) ? 1 : 0;

			$x = $this->GetX();
			if ($repeat && in_array($key, $group_def)) {
				$this->SetX($x+$w);
			} else {
				$this->printText($w, $h, $txt, $align, $fill, $ln);
			}
			if ($lastkey==$key) break;
		}
	}

	protected function outSubline($data, $definition, $level) {
		end($definition);
		$lastkey = key($definition);
		$h = $this->getLineHeight($data, $definition);
	
		$first = true;
		foreach ($definition as $key=>$def)  {
			$w = $def['width'] + ($first ? 10 : 0);
			$txt = $def['label'].': '.$data[$key];
			$align = $def['align'];
			$ln = ($key==$lastkey) ? 1 : 0;

			$this->printText($w, $h, $txt, $align, 0, $ln);
			$first = false;
		}
	}
	
	protected function printText($w, $h, $txt, $align, $fill, $ln) {
//		$h = 6;
		$border = 1;
        $fill = 0;
		$x = '';
		$y = '';
		$reseth = true;
		$stretch = 0;
		$ishtml = false;
		$autopadding = true;
		$maxh = 0;
		$valign = 'T';
		$fitcell = false;
		$this->MultiCell($w, $h, $txt, $border, $align, $fill, $ln, $x, $y, $reseth, 
				$stretch, $ishtml, $autopadding, $maxh, $valign, $fitcell);
	}
}
?>