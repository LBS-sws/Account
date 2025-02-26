<?php
class ImpCustomer {
	public function getDbFields() {
		return array(
				'code'=>Yii::t('import','Code'),
				'name'=>Yii::t('import','Name'),
				'full_name'=>Yii::t('import','Full Name'),
				'cont_name'=>Yii::t('import','Contact Name'),
				'cont_phone'=>Yii::t('import','Contact Phone'),
				'address'=>Yii::t('import','Address'),
				'tax_reg_no'=>Yii::t('import','Taxpayer No.'),
			);
	}
	
	public function getDefaultMapping() {
	//	Db Field Name => Excel Column No.
		return array(
				'code'=>0,
				'name'=>1,
				'full_name'=>5,
				'cont_name'=>2,
				'cont_phone'=>3,
				'address'=>4,
				'tax_reg_no'=>6,
			);
	}
	
	public function validateData($data) {
		$rtn = !empty($data['code'])
				? (strlen($data['code'])>20 ? Yii::t('import','Code').' '.Yii::t('import','is too long').' /' : '') 
				: Yii::t('import','Code').' '.Yii::t('import','cannot be blank').' /';
		$rtn .= !empty($data['name']) 
				? (strlen($data['name'])>1000 ? Yii::t('import','Name').' '.Yii::t('import','is too long').' /' : '') 
				: Yii::t('import','Name').' '.Yii::t('import','cannot be blank').' /';
		$rtn .= !empty($data['full_name']) && strlen($data['full_name'])>1000 ? Yii::t('import','Full Name').' '.Yii::t('import','is too long').' /' : '';
		$rtn .= !empty($data['cont_name']) && strlen($data['cont_name'])>100 ? Yii::t('import','Contact Name').' '.Yii::t('import','is too long').' /' : '';
		$rtn .= !empty($data['cont_phone']) && strlen($data['cont_phone'])>30 ? Yii::t('import','Contact Phone').' '.Yii::t('import','is too long').' /' : '';
		$rtn .= !empty($data['address']) && strlen($data['address'])>1000 ? Yii::t('import','Address').' '.Yii::t('import','is too long').' /' : '';
		$rtn .= !empty($data['tax_reg_no']) && strlen($data['tax_reg_no'])>100 ? Yii::t('import','Taxpayer No.').' '.Yii::t('import','is too long').' /' : '';
		$rtn .= !empty($data['city']) ? '' : Yii::t('import','City').' '.Yii::t('import','cannot be blank').' /';
		return empty($rtn) ? '' : Yii::t('import','ERROR').'- /'.Yii::t('import','Row No.').': '.$data['excel_row'].' /'.$rtn;
	}
	
	public function importData(&$connection, $data) {
		$suffix = Yii::app()->params['envSuffix'];
		$suffix = $suffix=='dev' ? '_w' : $suffix;
		
		$sql = "select id from swoper$suffix.swo_company where code=:code and city=:city";
		$command=$connection->createCommand($sql);
		$command->bindParam(':code',$data['code'],PDO::PARAM_STR);
		$command->bindParam(':city',$data['city'],PDO::PARAM_STR);
		$row = $command->queryRow();
		
		$action = ($row===false) ? Yii::t('import','INSERT') : Yii::t('import','UPDATE');
		if ($row===false) {
			$sql = "insert into swoper$suffix.swo_company 
						(code, name, full_name, cont_name, cont_phone, address, tax_reg_no, city, lcu, luu)
					values
						(:code, :name, :full_name, :cont_name, :cont_phone, :address, :tax_reg_no, :city, :uid, :uid)
					";
		} else {
			$sql = "update swoper$suffix.swo_company set ";
			if (!empty($data['name'])) $sql .= "name = :name, ";
			if (!empty($data['full_name'])) $sql .= "full_name = :full_name, ";
			if (!empty($data['cont_name'])) $sql .= "cont_name = :cont_name, ";
			if (!empty($data['cont_phone'])) $sql .= "cont_phone = :cont_phone, ";
			if (!empty($data['address'])) $sql .= "address = :address, ";
			if (!empty($data['tax_reg_no'])) $sql .= "tax_reg_no = :tax_reg_no, ";
			$sql .=	"lcu = :uid, luu = :uid
					where code = :code and city = :city 
				";
		}
		$command=$connection->createCommand($sql);
        $data['code'] = str_replace(' ','',$data['code']);
		if (strpos($sql,':code')!==false)
			$command->bindParam(':code',$data['code'],PDO::PARAM_STR);
		if (strpos($sql,':name')!==false)
			$command->bindParam(':name',$data['name'],PDO::PARAM_STR);
		if (strpos($sql,':full_name')!==false)
			$command->bindParam(':full_name',$data['full_name'],PDO::PARAM_STR);
		if (strpos($sql,':cont_name')!==false)
			$command->bindParam(':cont_name',$data['cont_name'],PDO::PARAM_STR);
		if (strpos($sql,':cont_phone')!==false)
			$command->bindParam(':cont_phone',$data['cont_phone'],PDO::PARAM_STR);
		if (strpos($sql,':address')!==false)
			$command->bindParam(':address',$data['address'],PDO::PARAM_STR);
		if (strpos($sql,':tax_reg_no')!==false)
			$command->bindParam(':tax_reg_no',$data['tax_reg_no'],PDO::PARAM_STR);
		if (strpos($sql,':uid')!==false)
			$command->bindParam(':uid',$data['uid'],PDO::PARAM_STR);
		if (strpos($sql,':city')!==false)
			$command->bindParam(':city',$data['city'],PDO::PARAM_LOB);
		$command->execute();
		$this->sendCurlForJD($connection,$data);
		$id = Yii::app()->db->getLastInsertID();
		return $action.'- /'.Yii::t('import','Row No.').': '.$data['excel_row']
			.' /'.Yii::t('import','Code').': '.$data['code']
			.' /'.Yii::t('import','Name').': '.$data['name']
			.' /'.Yii::t('import','City').': '.$data['city']
			.' /'.Yii::t('import','User').': '.$data['uid']
			;
	}

	protected function sendCurlForJD($connection,$data){
	    if(isset($data['code'])&&isset($data['city'])){
            $suffix = Yii::app()->params['envSuffix'];
            $row = Yii::app()->db->createCommand()->select("*")->from("swoper$suffix.swo_company")
                ->where('code=:code and city=:city',array(':code'=>$data['code'],':city'=>$data['city']))
                ->queryRow();
            if($row){
                $curlData=self::getDataForCustomerRow($row);
                $curlData = array("data"=>$curlData);
                $data = array(
                    "status_type"=>"P",
                    "info_type"=>"customer",
                    "info_url"=>"/kapi/v2/lbs/basedata/bd_customer/save",
                    "min_url"=>"/kapi/v2/lbs/basedata/bd_customer/save",
                    "data_content"=>json_encode($curlData),
                    "out_content"=>"",
                    "message"=>"等待执行",
                    "lcu"=>"admin_acct",
                    "lcd"=>date_format(date_create(),"Y-m-d H:i:s"),
                );
                $connection->createCommand()->insert("operation{$suffix}.opr_api_curl",$data);
            }
        }
    }

    //去除客户编号的尾缀
    private static function getDataForCustomerRow($row){
        $curlData=array(
            "lbs_apikey"=>$row["id"],
            "number"=>$row["code"]."-".$row["city"],//编码
            "name"=>$row["name"],//名称
            "status"=>"C",//数据状态 [A:暂存, B:已提交, C:已审核]
            "enable"=>$row["status"]==2?0:1,//使用状态 [0:禁用, 1:可用]
            "simplename"=>$row["full_name"],//简称

            "bizfunction"=>"1,2,3,4",//业务职能 [1:销售, 2:结算, 3:付款, 4:收货]
            "type"=>"1",//伙伴类型 [1:法人企业, 2:非法人企业, 3:非企业单位, 4:个人, 5:个体户]

            "createorg_number"=>"LBSGL",//创建组织.编码
            //"internal_company_number"=>"",//内部业务单元.编码
            //"societycreditcode"=>"",//统一社会信用代码
            //"tx_register_no"=>$row["tax_reg_no"],//纳税人识别号 (2024年9月23日删除)
            //"linkman"=>$row["cont_name"],//联系人 (2024年9月23日删除)
            //"bizpartner_phone"=>$row["cont_phone"],//联系电话 (2024年9月23日删除)
            //"postal_code"=>$row["email"],//电子邮箱 (2024年9月23日删除)
            "address"=>$row["address"],//电子邮箱(金蝶接口未有)
            "group_code"=>$row["group_id"],//集团编号(金蝶接口未有)
            "group_name"=>$row["group_name"],//集团名称(金蝶接口未有)

            "entry_groupstandard"=>array(//分类标准
                "groupid_number"=>"E",//分类.编码
                "standardid_number"=>"JBFLBZ",//分类标准.编码
            ),//分类标准
        );
        return $curlData;
    }
}
?>