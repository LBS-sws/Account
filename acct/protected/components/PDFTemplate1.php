<?php
//Yii::import('ext.tcpdf.*');
require_once(dirname(__FILE__).'/../extensions/tcpdf/config/tcpdf_config.php');
require_once(dirname(__FILE__).'/../extensions/tcpdf/tcpdf.php');

class PDFTemplate1 extends PDFTemplateBase {
	protected $report_structure;

	public function SetReportStructure($invalue) {
		$this->report_structure = $invalue;
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

	protected function generateBlock($data, $item, $h, $lastkey) {
		$reseth = true;
		$autopadding = true;
		$cellpadding = '';
		$border = 1;

		$hx = $this->calcBlockHeight($data);
		$flag = ($h > $hx);
		
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
				if ($flag) $ht = $h - $hc;
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

	public function Content($data) {
        // Colors, line width and bold font
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(0.1);

        // Color and font restoration
		$this->SetFillColor(80, 80, 80);
        $this->SetTextColor(0);
        $this->SetFont('droidsansfallback', '', 8, '', false);

        // Data
// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
		$h = 6;
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

		if (!empty($data)) {
			$rstruct = array_values($this->report_structure);
			$lastkey = end($rstruct);

			foreach ($data as $row) {
			// Print Detail Line	
				$h = $this->getFieldHeight($row);
				
				foreach ($this->report_structure as $item) {
					if (is_array($item)) {
						$this->generateBlock($row['detail'], $item, $h, $lastkey);
					} else {
						$w = $this->line_def[$item]['width'];
						$halign = $this->line_def[$item]['align'];
						$ln = ($item==$lastkey) ? 1 : 0;
						$this->MultiCell($w, $h, $row[$item], $border, $halign, $fill, $ln, $x, $y, $reseth, 
							$stretch, $ishtml, $autopadding, $h, $valign, $fitcell);
					}
				}
				$this->Ln(2);

//				$fill=!$fill;
			}
		} else {
			$this->MultiCell(0, $h, '--'.Yii::t('report','Nil').'--', $border, 'C', $fill, 1, $x, $y, $reseth, 
				$stretch, $ishtml, $autopadding, $maxh, $valign, $fitcell);
		}
    }
}
?>