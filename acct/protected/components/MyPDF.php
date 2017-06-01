<?php
//Yii::import('ext.tcpdf.*');
require_once(dirname(__FILE__).'/../extensions/tcpdf/config/tcpdf_config.php');
require_once(dirname(__FILE__).'/../extensions/tcpdf/tcpdf.php');

class MyPDF extends TCPDF {
	protected $column_header = array();
	
	protected $column_width = array();

	protected $column_align = array();

	public function SetHeaderTitle($invalue) {
		$this->header_title = $invalue;
	}

	public function SetHeaderString($invalue) {
		$this->header_string = $invalue;
	}
	
	public function SetColumnHeader($inarray) {
		$this->column_header = $inarray;
	}
	
	public function SetColumnWidth($inarray) {
		$this->column_width = $inarray;
	}

	public function SetColumnAlign($inarray) {
		$this->column_align = $inarray;
	}

	//Page header

	public function Header() {
		// Set font
		// Title
		$this->SetFont('msungstdlight', 'B', 16);
		$this->Cell(0, 8, $this->header_title, 0, false, 'C', 0, '', 0, false, 'M', 'M');
		$this->Ln(8);
		
		$this->SetFont('msungstdlight', 'B', 8);
		$this->Cell(0, 8, $this->header_string, 0, false, 'L', 0, '', 0, false, 'M', 'M');
		$this->Ln(8);
		
		$this->SetFont('msungstdlight', 'B', 8);
		$lastidx = count($this->column_header) - 1;
		foreach ($this->column_header as $idx=>$title) {
			$ln = ($idx==$lastidx) ? 1 : 0;
			$this->MultiCell($this->column_width[$idx], 8, $title, 0, $this->column_align[$idx], 
					false, $ln, '', '', true, 0, false, true, 0, 'B');
		}

		$style = array('width' => 0.3, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0));
		$this->Line(5, 26, 292, 26, $style);
	}

	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY(-15);
		// Set font
		$this->SetFont('msungstdlight', 'I', 8);
		// Page number
		$this->Cell(0, 10, Yii::t('report','Page').' '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
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
        $this->SetDrawColor(128, 0, 0);
        $this->SetLineWidth(0.3);
 //       $this->SetFont('', 'B');
        // Header
 //       $w = array(40, 35, 40, 45);
  //      $num_headers = count($header);
   //     for($i = 0; $i < $num_headers; ++$i) {
 //           $this->Cell($w[$i], 7, $header[$i], 1, 0, 'C', 1);
 //       }
  //      $this->Ln();
        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('msungstdlight', '', 8);
        // Data
// MultiCell($w, $h, $txt, $border=0, $align='J', $fill=0, $ln=1, $x='', $y='', $reseth=true, $stretch=0, $ishtml=false, $autopadding=true, $maxh=0)
		$h = 6;
		$border = 0;
        $fill = 0;
		$ln = 1;
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
			foreach ($data as $rows) {
				$lastidx = count($rows) - 1;
				$idx = -1;
				foreach ($rows as $key=>$value) {
					$idx++;

					$w = $this->column_width[$idx];
					$txt = $value;
					$align = $this->column_align[$idx];
					$ln = ($idx==$lastidx) ? 1 : 0;

					$this->MultiCell($w, $h, $txt, $border, $align, $fill, $ln, $x, $y, $reseth, 
						$stretch, $ishtml, $autopadding, $maxh, $valign, $fitcell);
				}
				$fill=!$fill;
			}
		} else {
			$this->MultiCell(0, $h, '--	'.Yii::t('report','Nil').'	--', $border, 'C', $fill, $ln, $x, $y, $reseth, 
				$stretch, $ishtml, $autopadding, $maxh, $valign, $fitcell);
		}
    }
}
?>