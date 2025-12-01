 <?php
class MonthlyCommand extends CConsoleCommand {
	protected $webroot;

	protected $year;
	protected $month;
	
	public function actionInitRecord($year='', $month='') {
		$this->year = (empty($year)) ? date('Y') : $year;
		$this->month = (empty($month)) ? date('m') : $month;
		echo "YEAR: ".$this->year."\tMONTH: ".$this->month."\n";

		$suffix = Yii::app()->params['envSuffix'];
		/*
		$sql = "select a.code
				from security$suffix.sec_city a left outer join security$suffix.sec_city b on a.code=b.region 
				where b.code is null 
				order by a.code
			";
		*/
		//修改成拥有“营业报告”权限的城市
        $sql = "select code
				from security$suffix.sec_city_info
				where field_id='OPERA' and field_value='1' 
				GROUP by code
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
            $rows[]=array("code"=>"RN");
            $rows[]=array("code"=>"KA");
			foreach ($rows as $row) {
				$city = $row['code'];
				echo "CITY: $city\n";
				$sql = "select count(id) from acc_account_file_hdr 
						where city='$city' and year_no=".$this->year." and month_no=".$this->month
					;
				$rc = Yii::app()->db->createCommand($sql)->queryScalar();
				if ($rc!==false && $rc==0) {
					echo "RECORD INIT...\n";
					$connection = Yii::app()->db;
					$transaction=$connection->beginTransaction();
				
					try {
						$hid = $this->addHeader($connection, $city);
						$transaction->commit();
					} catch(Exception $e) {
						$transaction->rollback();
						echo "EXCEPTION ERROR: ".$e->getMessage()."\n";
						Yii::app()->end();
					}
				}
			}
		}
	}
	
	// Add monthly header records
	protected function addHeader(&$connection, $city) {
		$sql = "insert into acc_account_file_hdr(city, year_no, month_no, status, lcu, luu) 
				values(:city, :year, :month, 'Y', :uid, :uid)
			";
		$uid = 'admin';
		$command=$connection->createCommand($sql);
		if (strpos($sql,':city')!==false) $command->bindParam(':city',$city,PDO::PARAM_STR);
		if (strpos($sql,':year')!==false) $command->bindParam(':year',$this->year,PDO::PARAM_INT);
		if (strpos($sql,':month')!==false) $command->bindParam(':month',$this->month,PDO::PARAM_INT);
		if (strpos($sql,':uid')!==false) $command->bindParam(':uid',$uid,PDO::PARAM_STR);
		$command->execute();
		return Yii::app()->db->getLastInsertID();
	}

	public function actionInitPayroll($year='', $month='') {
		$this->year = (empty($year)) ? date('Y') : $year;
		$this->month = (empty($month)) ? date('m') : $month;
		echo "YEAR: ".$this->year."\tMONTH: ".$this->month."\n";

		$suffix = Yii::app()->params['envSuffix'];
        /*
        $sql = "select a.code
                from security$suffix.sec_city a left outer join security$suffix.sec_city b on a.code=b.region
                where b.code is null
                order by a.code
            ";
        */
        //修改成拥有“营业报告”权限的城市
		$sql = "select code
				from security$suffix.sec_city_info
				where field_id='OPERA' and field_value='1' 
				GROUP by code
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
            $rows[]=array("code"=>"RN");
            $rows[]=array("code"=>"KA");
			foreach ($rows as $row) {
				$city = $row['code'];
				echo "CITY: $city\n";
				$sql = "select count(id) from acc_payroll_file_hdr 
						where city='$city' and year_no=".$this->year." and month_no=".$this->month
					;
				$rc = Yii::app()->db->createCommand($sql)->queryScalar();
				if ($rc!==false && $rc==0) {
					echo "RECORD INIT...\n";
					$connection = Yii::app()->db;
					$transaction=$connection->beginTransaction();
				
					try {
						$hid = $this->addPayrollHeader($connection, $city);
						$transaction->commit();
					} catch(Exception $e) {
						$transaction->rollback();
						echo "EXCEPTION ERROR: ".$e->getMessage()."\n";
						Yii::app()->end();
					}
				}
			}
		}
	}
	
	// Add monthly header records
	protected function addPayrollHeader(&$connection, $city) {
		$sql = "insert into acc_payroll_file_hdr(city, year_no, month_no, status, lcu, luu) 
				values(:city, :year, :month, 'Y', :uid, :uid)
			";
		$uid = 'admin';
		$command=$connection->createCommand($sql);
		if (strpos($sql,':city')!==false) $command->bindParam(':city',$city,PDO::PARAM_STR);
		if (strpos($sql,':year')!==false) $command->bindParam(':year',$this->year,PDO::PARAM_INT);
		if (strpos($sql,':month')!==false) $command->bindParam(':month',$this->month,PDO::PARAM_INT);
		if (strpos($sql,':uid')!==false) $command->bindParam(':uid',$uid,PDO::PARAM_STR);
		$command->execute();
		return Yii::app()->db->getLastInsertID();
	}
	
}
?>