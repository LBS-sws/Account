<?php
if(in_array($model->status_type,array(8,9))){//待扣款：8 完成：9
    $html="<legend>".Yii::t("give","Payment Detail")."</legend>";

    $html.='<div class="form-group">';
    $html.=$form->labelEx($model,'city',array('class'=>"col-sm-2 control-label"));
    $html.='<div class="col-sm-2">';
    $html.=$form->hiddenField($model,"payment_id");
    $html.=$form->hiddenField($model,"city");
    $html.=TbHtml::textField("city",General::getCityName($model->city),array('readonly'=>true));
    $html.='</div>';
    $html.='</div>';

    $html.='<div class="form-group">';
    $html.=$form->labelEx($model,'payment_date',array('class'=>"col-sm-2 control-label"));
    $html.='<div class="col-sm-2">';
    $html.=$form->textField($model,"payment_date",array('readonly'=>$model->getReadyForAcc(),'autocomplete'=>'off','prepend'=>'<span class="fa fa-calendar"></span>'));
    $html.='</div>';
    $html.='</div>';

    $html.='<div class="form-group">';
    $html.=$form->labelEx($model,'payment_type',array('class'=>"col-sm-2 control-label"));
    $html.='<div class="col-sm-3">';
    if(!$model->getReadyForAcc()){
        $html.=$form->dropDownList($model,"payment_type",ExpenseApplyForm::getTransTypeList(),array('readonly'=>$model->getReadyForAcc(),'empty'=>''));
    }else{
        $html.=$form->hiddenField($model,"payment_type");
        $html.=TbHtml::textField("payment_type",ExpenseApplyForm::getTransStrForCode($model->payment_type),array('readonly'=>$model->getReadyForAcc()));
    }
    $html.='</div>';
    $html.='</div>';

    $html.='<div class="form-group">';
    $html.=$form->labelEx($model,'acc_id',array('class'=>"col-sm-2 control-label"));
    $html.='<div class="col-sm-6">';
    if(!$model->getReadyForAcc()){
        $html.=$form->dropDownList($model,"acc_id",ExpenseApplyForm::getAccountListForCity($model->city),array('readonly'=>$model->getReadyForAcc(),'empty'=>''));
    }else{
        $html.=$form->hiddenField($model,"acc_id");
        $html.=TbHtml::textField("acc_id",ExpenseApplyForm::getAccountStrForID($model->acc_id),array('readonly'=>$model->getReadyForAcc()));
    }
    $html.='</div>';
    $html.='</div>';

    echo $html;
}
?>
