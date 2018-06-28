<?php
class RptReimbursement extends CReport {

	public function genReport() {
		$this->retrieveData();
		return $this->printReport();
	}
		public function retrieveData() {
		$start_dt = $this->criteria['START_DT'];
		$end_dt = $this->criteria['END_DT'];
		$ref_no = $this->criteria['REF_NO'];
		$city = $this->criteria['CITY'];
		$currcode = City::getCurrency($city);
		$currname = Currency::getName($currcode); 

		
		$payee_type = array(
						'C'=>Yii::t('trans','Client'),
						'S'=>Yii::t('trans','Supplier'),
						'F'=>Yii::t('trans','Staff'),
						'A'=>Yii::t('trans','Company A/C'),
						'O'=>Yii::t('trans','Others')
					);

		$suffix = Yii::app()->params['envSuffix'];
		
		if (empty($ref_no)) {
			$sql = "select a.*, 
						workflow$suffix.RequestStatusDate('PAYMENT',a.id,a.req_dt,'SI') as sts_dt,
						workflow$suffix.ActionPerson('PAYMENT',a.id,a.req_dt,'PC') as acctstaff,
						workflow$suffix.ActionPerson('PAYMENT',a.id,a.req_dt,'PS') as approver
					from acc_request a
					where a.city='$city' and a.status<>'V'
					and workflow$suffix.RequestStatusDate('PAYMENT',a.id,a.req_dt,'SI')>='".General::toDate($start_dt)." 00:00:00' 
					and workflow$suffix.RequestStatusDate('PAYMENT',a.id,a.req_dt,'SI')<='".General::toDate($end_dt)." 23:59:59'
				";
		} else {
			$sql = "select a.*, workflow$suffix.RequestStatusDate('PAYMENT',a.id,a.req_dt,'SI') as sts_dt,
						workflow$suffix.ActionPerson('PAYMENT',a.id,a.req_dt,'PC') as acctstaff,
						workflow$suffix.ActionPerson('PAYMENT',a.id,a.req_dt,'PS') as approver
					from acc_request a 
					inner join acc_request_info b on a.id=b.req_id and b.field_id='REF_NO' 
					where a.city='$city' and a.status<>'V' 
				";
			$sql .= (strpos($ref_no,'%')===false) ? " and b.field_value='$ref_no'" : " and b.field_value like '$ref_no'";
		}	
		$sql .= " order by a.id ";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		var_dump($sql);
		if (count($rows) > 0) {
			$transtypelist = General::getTransTypeList('OUT');
			$acctcodelist = General::getAcctCodeList();
			$acctlist = General::getAccountList($city);
			foreach ($rows as $row) {
				$req_id = $row['id'];

				$temp = array();
				$temp['req_id'] = $req_id;
				$temp['sts_dt'] = General::toDate($row['sts_dt']);
				$temp['item_desc'] = $row['item_desc'];
				$temp['amount'] = $row['amount'];
				$temp['trans_type'] = $transtypelist[$row['trans_type_code']];
				$temp['payee'] = $payee_type[$row['payee_type']].' - '.$row['payee_name'];

				$sql = "select field_id, field_value
						from acc_request_info
						where req_id=$req_id
					";
				$drows = Yii::app()->db->createCommand($sql)->queryAll();
				if (count($drows) > 0) {
					foreach ($drows as $drow) {
						$temp[strtolower($drow['field_id'])] = $drow['field_value'];
					}
				}

//				$temp['detail_info'] = ((empty($temp['acct_code'])) ? '' 
//								: $temp['acct_code'].' '.$acctcodelist[$temp['acct_code']])
//								."\n".Yii::t('trans','Payee').": "
//								. $temp['payee']
//								."\n".Yii::t('trans','Account').": "
//								. $acctlist[$temp['acct_id']]
//								."\n\n".Yii::t('report','Remarks').": "
//								. ((empty($temp['item_desc'])) ? '' : $temp['item_desc'])
//								;
				$temp['detail_info'] = Yii::t('trans','Payee').": "
								. $temp['payee']
								."\n".Yii::t('trans','Account').": "
								. $acctlist[$temp['acct_id']]
								;
				
				
				$cuser = User::model()->find('LOWER(username)=?',array($row['req_user']));
				$temp['cashier_img'] = $cuser->getUserInfoImage('signature');
				$type = $cuser->getUserInfo('signature_file_type');
				$temp['cashier_img_type'] = $this->getImageType($type);
				
				if (!empty($row['acctstaff'])) {
					$auser = User::model()->find('LOWER(username)=?',array($row['acctstaff']));
					$temp['account_img'] = $auser->getUserInfoImage('signature');
					$type = $auser->getUserInfo('signature_file_type');
					$temp['account_img_type'] = $this->getImageType($type);
				} else {
					$temp['account_img'] = '';
					$temp['account_img_type'] = '';
				}

				$muser = User::model()->find('LOWER(username)=?',array($row['approver']));
				$temp['manager_img'] = $muser->getUserInfoImage('signature');
				$type = $muser->getUserInfoImage('signature_file_type');
				$temp['manager_img_type'] = $this->getImageType($type);
				$temp['curr_name'] = $currname;
				
				$this->data[] = $temp;
			}
		}
//	var_dump($this->data);			
		return true;	}

	protected function getImageType($type) {
		switch ($type) {
			case 'image/jpeg' : return 'JPEG';
			case 'image/png' : return 'PNG';
		}
		return '';
	}
	
	public function getReportName() {
		$city_name = isset($this->criteria) ? ' - '.General::getCityName($this->criteria['CITY']) : '';
		return (isset($this->criteria) ? Yii::t('report',$this->criteria['RPT_NAME']) : Yii::t('report','Nil')).$city_name;
	}
	
	public function printReport() {
		ob_start();
		$pdf = new PDFTool('P', 'mm', $this->criteria['PAPER_SZ'], true, 'UTF-8', false);
		
		$pdf->setPageOrientation('P',true,3);
		
		// set PDF File basic info
		$pdf->SetTitle($this->getReportName());

		// set header and footer fonts
		//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		// set margins
		$t_margin= 10; 
		$r_margin=20;
		$l_margin=20;
		$pdf->SetMargins($l_margin, $t_margin, $r_margin);
		
//		$h_margin=5;
//		$pdf->SetHeaderMargin($h_margin);
		
//		$f_margin=5;
//		$pdf->SetFooterMargin($f_margin);

		// set auto page breaks
//		$b_margin=15;
//		$pdf->SetAutoPageBreak(TRUE, $b_margin);
		$logo = Yii::app()->basePath.'/../images/company.jpg';

		$cnt = 1;
		foreach ($this->data as $record) {
			// add a page
			if ($cnt==1) {
				$pdf->AddPage();
				$y = 0;
			}

			$pdf->Image($logo, 22, $y+3, 22, 18, 'JPEG', '', '', false, 150, '', false, false, 0, false, false, false, false);
			
			$pdf->SetFont('droidsansfallback', 'B', 16, '', false);
			$pdf->Cell(0, 10, Yii::t('report','Reimbursement Form').' ('.$this->criteria['CITY_NAME'].')', 0, 1, 'C', false, '', 0, true, 'C', 'C');
			$y += 10;
			
			$pdf->SetFont('droidsansfallback', 'B', 10, '', false);
			$pdf->MultiCell(17, 6, Yii::t('report','Ref. No.'), 1, 'C', false, 0, 75, '', true, 0, false, true, 6, 'M');
			$pdf->MultiCell(40, 6, $record['ref_no'], 1, 'C', false, 0, '', '', true, 0, false, true, 6, 'M');
			$pdf->MultiCell(5, 6, '', 0, 'L', false, 0, '', '', false, 0, false, true, 6, 'M');
			$pdf->MultiCell(13, 6, Yii::t('report','Date').':', 0, 'C', false, 0, '', '', true, 0, false, true, 6, 'M');
			$pdf->MultiCell(40, 6, $record['sts_dt'], 'B', 'C', false, 1, '', '', true, 0, false, true, 6, 'M');
			$y += 6;
			
			$pdf->Cell(0, 6, ' ', 0, 1, 'C', false, '', 0, true, 'C', 'C');
			$y += 6;
			
			$pdf->MultiCell(120, 10, Yii::t('report','Detail'), 1, 'C', false, 0, '', '', true, 0, false, true, 10, 'M');
			$pdf->MultiCell(50, 10, Yii::t('report','Amount'), 1, 'C', false, 1, '', '', true, 0, false, true, 10, 'M');
			$y += 10;

			$pdf->SetFont('droidsansfallback', 'B', 10, '', false);
			$pdf->MultiCell(120, 35, $record['detail_info'], 1, 'L', false, 0, '', '', true, 0, false, true, 33, 'T');
			$pdf->MultiCell(50, 35, $record['amount'], 1, 'C', false, 1, '', '', true, 0, false, true, 33, 'T');
			$y += 35;

			$dollar = General::dollarToChinese($record['amount']);
			
			$pdf->SetFont('droidsansfallback', 'B', 10, '', false);
			$pdf->MultiCell(120, 10, Yii::t('report','Total').' '.$record['curr_name'].' '.$dollar, 1, 'L', false, 0, '', '', true, 0, false, true, 10, 'M');
			$pdf->MultiCell(50, 10, $record['amount'], 1, 'C', false, 1, '', '', true, 0, false, true, 10, 'M');
			$y += 10;
			
			$pdf->SetFont('droidsansfallback', 'B', 9, '', false);
			$pdf->MultiCell(43, 25, Yii::t('report','Manager'), 0, 'L', false, 0, '', '', true, 0, false, true, 20, 'M');
			$pdf->MultiCell(42, 25, Yii::t('report','Account'), 0, 'L', false, 0, '', '', true, 0, false, true, 20, 'M');
			$pdf->MultiCell(43, 25, Yii::t('report','Cashier'), 0, 'L', false, 0, '', '', true, 0, false, true, 20, 'M');
			$pdf->MultiCell(42, 25, Yii::t('report','Payee'), 0, 'L', false, 1, '', '', true, 0, false, true, 20, 'M');

			if (!empty($record['manager_img'])) {
				//$type = $this->examImageType($record['manager_img']); //$record['manager_img_type']
				$pdf->Image('@'.$record['manager_img'], 35, $y+3, 28, 15, $record['manager_img_type'], '', '', false, 150, '', false, false, 0, false, false, false, false);
			}
			if (!empty($record['account_img'])) {
				//$type = $this->examImageType($record['account_img']); //$record['account_img_type']
				$pdf->Image('@'.$record['account_img'], 75, $y+3, 28, 15, $record['account_img_type'], '', '', false, 150, '', false, false, 0, false, false, false, false);
			}
			if (!empty($record['cashier_img'])) {
				//$type = $this->examImageType($record['cashier_img']); //$record['cashier_img_type']
				$pdf->Image('@'.$record['cashier_img'], 120, $y+3, 28, 15, $record['cashier_img_type'], '', '', false, 150, '', false, false, 0, false, false, false, false);
			}

			$y += 17;
			
			//			$this->MultiCell(0, $h, '--'.Yii::t('report','Nil').'--', $border, 'C', $fill, 1, $x, $y, $reseth, 
//				$stretch, $ishtml, $autopadding, $maxh, $valign, $fitcell);
			
			$cnt++;
			if ($cnt > 3) $cnt = 1;
		}

		//Close and output PDF document
		ob_end_clean();
		$outstring = $pdf->Output('', 'S');
		return $outstring;
	}
	
	protected function examImageType($image) {
		$path = tempnam(sys_get_temp_dir(), 'IMG');
		$temp = fopen($path, 'w');
		fwrite($temp, $image);
		fclose($temp);
		$file_dimensions = getimagesize($path);
		$image_type = strtolower($file_dimensions['mime']);
		unlink($path);
		var_dump($image_type);
		return $image_type;
	}
}
?>