<?php
class Export {
	public $dataModel;
	
	public function exportExcel($filename) {
		$file = $this->dataModel->genReport();
		
		if (empty($filename)) $filename = 'report.xlsx';
//		header("Content-type: application/vnd.ms-excel");
		header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
		header('Content-Disposition: attachment; filename="'.$filename.'"'); 
		header('Content-Length: ' . strlen($file));
		header('Cache-Control: max-age=0');
		echo $file;
		Yii::app()->end();
	}
}
?>