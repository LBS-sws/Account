<?php
class Calculation {
    public static function getLastMonthFigure($year, $month, $index) {
		$d = strtotime('-1 month', strtotime($year.'-'.$month.'-1'));
		$ly = date('Y', $d);
		$lm = date('m', $d);

		$rtn = array();
		$sql = "select a.city, b.data_value from swo_monthly_hdr a, swo_monthly_dtl b 
				where a.id=b.hdr_id and b.data_field='$index' and a.year_no=$ly and a.month_no=$lm 
				group by a.city
			";
		$rows = Yii::app()->db->createCommand($sql)->queryAll();
		if (count($rows) > 0) {
			foreach ($rows as $row) $rtn[$row['city']] = $row['data_value'];
		}
		return $rtn;
    }
}
?>