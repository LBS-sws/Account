 <?php
class TestCommand extends CConsoleCommand {
	protected $rptId;
	protected $rptName;
	protected $reqUser;
	protected $format;
	protected $data = array();
	protected $multiuser = false;
	protected $users = array();
	
	public function actionTest() {
		$sql = "select * from acc_request where id=172";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			
			$value = (float)$row['amount'];
			
			$y = $value * 100;
			
			$z = (int)$y;
			
		$remain = $y % 100;
		
		$dollar = $value - ($remain / 100);
		$cent = $remain % 10;
		$tencent = ($remain - $cent) / 10;
		$x = (string)$row['amount'];
		var_dump($value);
		var_dump($z);
		var_dump($y);
		var_dump($remain);
		var_dump($dollar);
		var_dump($cent);
		var_dump($tencent);
		var_dump($x);
		
			$dollar = General::dollarToChinese($row['amount']);
			var_dump($row['amount']);
			var_dump($dollar);
		}
	}
	
	public function actionTest2() {
		$sql = "select * from acc_request where id=172";
		$row = Yii::app()->db->createCommand($sql)->queryRow();
		if ($row!==false) {
			var_dump($row['amount']);

			list($dollar, $remain) = split("\.",$row['amount']);
			$y = (int)$dollar;
			var_dump($y);
			
			$cent = $remain % 10;
			var_dump($cent);
		
			$tencent = ($remain - $cent) / 10;
			var_dump($tencent);
		
		}
	}
}

?>