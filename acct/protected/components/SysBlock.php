<?php
class SysBlock {
    protected $checkItems;

    public function __construct() {
        $this->checkItems = require(Yii::app()->basePath.'/config/sysblock.php');
    }

    public function blockNRoute($controllerId, $functionId) {
        $session = Yii::app()->session;
        $sysblock =isset($session['sysblock']) ? $session['sysblock'] : array();

        $sysId = Yii::app()->params['systemId'];

        foreach ($this->checkItems as $key=>$value) {
            if (!isset($sysblock[$key]) || $sysblock[$key]==false) {
                $result = call_user_func('self::'.$value['validation']);
                $sysblock[$key] = $result;
                $session['sysblock'] = $sysblock;

                if (!$result) {
                    $url = '';
                    $systems = General::systemMapping();
                    if ($sysId==$value['system']) {
                        if ($controllerId!='site' && $functionId!=$value['function']) $url = $systems[$value['system']]['webroot'];
                    } else {
                        $url = $systems[$value['system']]['webroot'];
                    }
                    return ($url=='' ? false : $url);
                }
            }
        }

        return false;
    }

    public function getBlockMessage($systemId) {
        $session = Yii::app()->session;
        if (isset($session['sysblock'])) {
            foreach ($session['sysblock'] as $key=>$value) {
                if (!$value) {
                    if ($this->checkItems[$key]['system']==$systemId) return $this->checkItems[$key]['message'];
                }
            }
        }
        return false;
    }

    /**
     * 驗證管理員是否有未考核的員工.
     * @param string $uid 需要被驗證的管理員..
     * @return bool true(無未考核員工)  false(有未考核員工).
     */
    public function validateReviewLongTime(){
        $uid = Yii::app()->user->id;
        $suffix = Yii::app()->params['envSuffix'];
        $row = Yii::app()->db->createCommand()->select("b.id")->from("hr$suffix.hr_binding a")
            ->leftJoin("hr$suffix.hr_employee b","a.employee_id=b.id")
            ->leftJoin("security$suffix.sec_user_access e","a.user_id=e.username")
            ->where("a.user_id=:user_id and a_read_write like'%RE02%'",array(":user_id"=>$uid))->queryRow();
        if($row){ //賬號有綁定的員工且有考核權限
            $year = date("Y");
            $day = date("m-d");
            if($day>="11-01"){
                $dateSql = " and ((b.year<=".($year-1).") or (b.year = $year and year_type = 1))";
            }elseif ($day>="05-01"){
                $dateSql = " and b.year<=".($year-1);
            }else{
                $dateSql = " and ((b.year<=".($year-2).") or (b.year = ".($year-1)." and year_type = 1))";
            }
            $count = Yii::app()->db->createCommand()->select("a.id")->from("hr$suffix.hr_review_h a")
                ->leftJoin("hr$suffix.hr_review b","a.review_id=b.id")
                ->where("a.status_type!=3 and a.handle_id=:handle_id $dateSql",
                    array(":handle_id"=>$row['id'])
                )->queryRow();
            if($count){ //存在未考核的員工
                return false;
            }
        }
        return true;
    }

    public function test() {
        return false;
    }
}
?>