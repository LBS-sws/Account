<?php
$this->pageTitle=Yii::app()->name . ' - SellCompute Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'SellCompute-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>
    .table-responsive>table{ table-layout: fixed;}
    input[type="checkbox"].readonly{ opacity: 0.6;pointer-events: none;}
</style>

<section class="content-header">
	<h1>
        <strong><?php echo Yii::t('report','Sales Commission All'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('sellSearch/index')));
		?>
	</div>
	</div></div>

    <div class="box">
        <div id="yw0" class="tabbable">
            <?php
            echo $model->getMenuHtml("Search");
            ?>
            <div class="box-info" >
                <?php echo $form->hiddenField($model, 'id'); ?>

                <div class="box-body" >
                    <div class="form-group" >
                        <label class="col-sm-2 control-label"><?php echo Yii::t('commission','city'); ?></label>
                        <div class="col-sm-2" >
                            <?php echo $form->textField($model, 'city_name',
                                array('readonly'=>(true))
                            ); ?>
                        </div>
                        <label class="col-sm-1 control-label"><?php echo Yii::t('commission','saleyear'); ?></label>
                        <div class="col-sm-2">
                            <?php echo TbHtml::textField("year",$model->year."/".$model->month,array('readonly'=>true));?>

                        </div>
                        <label class="col-sm-1 control-label"><?php echo Yii::t('commission','employee_name'); ?></label>
                        <div class="col-sm-2" >
                            <?php echo $form->textField($model, 'employee_name',
                                array('readonly'=>(true))
                            ); ?>
                        </div>
                    </div>
                </div>
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title"><strong><?php echo Yii::t('app','sale commission');?></strong></h3>
                    </div>
                </div>
                <div class="box-body">
                    <div class="col-sm-12">
                        <div class="table-responsive">
                            <?php
                            echo $model->getListHtml();
                            ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

<?php

$js = "
$('#checkAll').change(function(){
    if($(this).is(':checked')){
        $('.checkOne').prop('checked',true);
    }else{
        $('.checkOne').prop('checked',false);
    }
});
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


