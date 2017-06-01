<?php
class CReport {
	public $criteria;
	
	public $data = array();
	
	public function genReport() {
		return;
	}

	protected function sendEmail(&$connection, $record=array()) {
		$suffix = Yii::app()->params['envSuffix'];
		$suffix1 = ($suffix=='dev') ? '_w' : $suffix;
		$sql = "insert into swoper$suffix1.swo_email_queue
					(from_addr, to_addr, cc_addr, subject, description, message, status, lcu)
				values
					(:from_addr, :to_addr, :cc_addr, :subject, :description, :message, 'P', 'admin')
			";
		$command = $connection->createCommand($sql);
		if (strpos($sql,':from_addr')!==false)
			$command->bindParam(':from_addr',$record['from_addr'],PDO::PARAM_STR);
		if (strpos($sql,':to_addr')!==false)
			$command->bindParam(':to_addr',$record['to_addr'],PDO::PARAM_STR);
		if (strpos($sql,':cc_addr')!==false)
			$command->bindParam(':cc_addr',$record['cc_addr'],PDO::PARAM_STR);
		if (strpos($sql,':subject')!==false)
			$command->bindParam(':subject',$record['subject'],PDO::PARAM_STR);
		if (strpos($sql,':description')!==false)
			$command->bindParam(':description',$record['description'],PDO::PARAM_STR);
		if (strpos($sql,':message')!==false)
			$command->bindParam(':message',$record['message'],PDO::PARAM_STR);
		$command->execute();
	}
	
}
?>