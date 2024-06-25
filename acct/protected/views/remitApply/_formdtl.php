<tr class="changeTr">
    <td>
        <?php echo TbHtml::dropDownList($this->getFieldName('setId'),  $this->record['setId'],ExpenseSetNameForm::getExpenseSetNameList($this->record['setId']),
            array('readonly'=>$this->model->readonly(),'empty'=>'')
        ); ?>
    </td>
    <td>
        <?php echo TbHtml::textField($this->getFieldName('infoDate'),  $this->record['infoDate'],
            array('readonly'=>$this->model->readonly(),'autocomplete'=>'off','prepend'=>'<span class="fa fa-calendar"></span>','class'=>'info_date')
        ); ?>
    </td>
    <td>
        <?php echo TbHtml::textField($this->getFieldName('amtType'),  $this->record['amtType'],
            array('readonly'=>$this->model->readonly())
        ); ?>
    </td>
    <td>
        <?php echo TbHtml::textArea($this->getFieldName('infoRemark'),  $this->record['infoRemark'],
            array('readonly'=>$this->model->readonly(),'rows'=>1)
        ); ?>
    </td>
    <td>
        <?php echo TbHtml::numberField($this->getFieldName('infoAmt'),  $this->record['infoAmt'],
            array('readonly'=>$this->model->readonly(),'class'=>'changeSumNumber')
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
</tr>
