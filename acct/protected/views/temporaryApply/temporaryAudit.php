<?php
$auditList = ExpenseFun::getAuditListForID($model->id);
if(!empty($auditList)){
    $html="<legend>".Yii::t("give","Audit Detail")."</legend>";
    foreach ($auditList as $audit){
        $html.='<div class="form-group">';
        $html.=TbHtml::label($audit["audit_str"],'',array('class'=>"col-sm-2 control-label"));
        $html.='<div class="col-sm-2">';
        $html.=TbHtml::textField("audit_user",$audit["audit_user"],array('readonly'=>true));
        $html.='</div>';
        $html.=TbHtml::label(Yii::t("give","audit date"),'',array('class'=>"col-sm-2 control-label"));
        $html.='<div class="col-sm-2">';
        $html.=TbHtml::textField("lcd",$audit["lcd"],array('readonly'=>true));
        $html.='</div>';
        $html.='</div>';
    }
    echo $html;
}
?>
