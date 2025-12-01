<tr class="changeTr">

    <td class="text-center">
        <?php
        echo TbHtml::hiddenField($this->getFieldName('tripId'),$this->record['tripId']);
        if($this->model->readonly()){
            if(intval($this->record['amtType'])===1&&!empty($this->record['tripId'])){
                echo TbHtml::button("已关联",array(
                    "class"=>"btn btn-primary look-trip",
                    "data-id"=>$this->record['tripId'],
                    "style"=>"padding:6px 5px",
                ));
            }
        }else{
            $btnStr= "关联";
            $btnClass= "btn btn-default btn-select-trip";
            if(intval($this->record['amtType'])===1){
                if(!empty($this->record['tripId'])){
                    $btnStr = "已关联";
                }
            }else{
                $btnClass.= " hide";
            }
            echo TbHtml::button($btnStr,array(
                "class"=>$btnClass,
                "data-id"=>$this->record['tripId'],
                "style"=>"padding:6px 5px",
            ));
        }
        ?>
    </td>
    <td>
        <?php echo TbHtml::dropDownList($this->getFieldName('setId'),  $this->record['setId'],ExpenseSetNameForm::getExpenseSetNameList($this->record['setId']),
            array('readonly'=>($this->model->readonly()||$this->model->tableDetail['local_bool']==1),'empty'=>'','class'=>'setId')
        ); ?>
    </td>
    <td>
        <?php echo TbHtml::textField($this->getFieldName('infoDate'),  $this->record['infoDate'],
            array('readonly'=>$this->model->readonly(),'autocomplete'=>'off','prepend'=>'<span class="fa fa-calendar"></span>','class'=>'info_date')
        ); ?>
    </td>
    <td>
        <?php echo TbHtml::dropDownList($this->getFieldName('amtType'),  $this->record['amtType'],ExpenseFun::getAmtTypeOne(),
            array('readonly'=>$this->model->readonly(),'empty'=>'','class'=>'changeAmtType')
        ); ?>
    </td>
    <td>
        <?php echo TbHtml::textArea($this->getFieldName('infoRemark'),  $this->record['infoRemark'],
            array('readonly'=>$this->model->readonly(),'rows'=>2,'class'=>'infoRemark')
        ); ?>
    </td>
    <td>
        <?php echo TbHtml::numberField($this->getFieldName('infoAmt'),  $this->record['infoAmt'],
            array('readonly'=>true,'class'=>'changeSumNumber')
        ); ?>
    </td>
    <td>
        <?php
        if($this->model->readonly()){
            echo "&nbsp;";
        }else{
            echo TbHtml::Button('删除',array('id'=>'btnDelRow','title'=>Yii::t('misc','Delete'),'class'=>'btn-warning'));
            //echo TbHtml::Button('删除',array('id'=>'btnDelRow','title'=>Yii::t('misc','Add'),'class'=>'btn-primary'));
        }
        ?>
        <?php echo CHtml::hiddenField($this->getFieldName('uflag'),$this->record['uflag']); ?>
        <?php echo CHtml::hiddenField($this->getFieldName('id'),$this->record['id']); ?>
        <?php echo CHtml::hiddenField($this->getFieldName('expId'),$this->record['expId']); ?>
        <?php echo CHtml::hiddenField($this->getFieldName('infoJson'),$this->record['infoJson']); ?>
    </td>

    <?php
    $tdJson = isset($this->record['infoJson'])?json_decode($this->record['infoJson'],true):array();
    $tdJson = is_array($tdJson)?$tdJson:array();
    $tdTwoList = ExpenseFun::getAmtTypeTwo();
    $html = "";
    foreach ($tdTwoList as $key=>$itemList){
        $readonly=$this->model->readonly();
        if(!$readonly){
            $readonly = $this->record['amtType']!==''&&$itemList["one_type"]==$this->record['amtType']?false:true;
        }
        $key="".$key;
        $value = key_exists($key,$tdJson)?$tdJson[$key]:"";
        $html.= "<td>";
        $html.=TbHtml::numberField($this->getFieldName($key),$value,array('readonly'=>$readonly,'data-type'=>$itemList["one_type"],'class'=>'changeNumber'));
        $html.= "</td>";
    }
    echo $html;
    ?>
</tr>
