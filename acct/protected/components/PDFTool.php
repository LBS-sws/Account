<?php
//Yii::import('ext.tcpdf.*');
require_once(dirname(__FILE__).'/../extensions/tcpdf/config/tcpdf_config.php');
require_once(dirname(__FILE__).'/../extensions/tcpdf/tcpdf.php');

class PDFTool extends TCPDF {
	public function Header() {
	}
	
	public function Footer() {
	}
}
?>