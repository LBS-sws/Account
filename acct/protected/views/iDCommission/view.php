<?php
$this->pageTitle=Yii::app()->name . ' - Month Report';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'tc-list',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
    <h1>
        <strong>ID <?php echo Yii::t('report','Sales Commission All'); ?></strong>
    </h1>
    <!--
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="#">Layout</a></li>
            <li class="active">Top Navigation</li>
        </ol>
    -->
</section>
<div class="box"><div class="box-body">
        <div class="btn-group" role="group">
            <?php echo TbHtml::link('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'),
                Yii::app()->createUrl('iDCommission/index_s',array('year'=>$model->year,'month'=>$model->month,'city'=>$model->city,'type'=>$this->type)), array(
                'class'=>'btn btn-default'));
            ?>
        </div>
        <!--
<?php if ($this->type==0): ?>
        <div class="btn-group pull-right" role="group">
            <?php echo TbHtml::button('<span class="fa fa-download"></span> '.Yii::t('misc','Down'), array(
            'submit'=>Yii::app()->createUrl('iDCommission/downs',array('index'=>$model->id)),)); ?>

        </div>
<?php endif ?>
        -->
    </div>
</div>
<section class="content" >
    <div class="box">
        <div class="box-body">
            <div class="btn-group text-info" role="group">
                <p><b>注：</b></p>
                <p style="text-indent: 15px;"><?php echo Yii::t('commission','Zhu1'); ?></p>
                <p style="text-indent: 15px;"><?php echo Yii::t('commission','Zhu2'); ?></p>
            </div>
        </div>
    </div>
    <div class="box">
        <div id="yw0" class="tabbable">
            <ul class="nav nav-tabs" role="menu">
                <li class="active">
                    <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('iDCommission/view',array('index'=>$model->id,'type'=>$this->type,'year'=>$model->year,'month'=>$model->month));?>" ><?php echo Yii::t('commission','ALL'); ?></a>
                </li>
                <li>
                    <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('iDCommission/new',array('index'=>$model->id,'type'=>$this->type,'year'=>$model->year,'month'=>$model->month));?>" ><?php echo Yii::t('commission','New'); ?></a>
                </li>
                <li  class="">
                    <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('iDCommission/amend',array('index'=>$model->id,'type'=>$this->type,'year'=>$model->year,'month'=>$model->month));?>" ><?php echo Yii::t('commission','Edit'); ?></a>
                </li>
                <li  class="">
                    <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('iDCommission/renew',array('index'=>$model->id,'type'=>$this->type,'year'=>$model->year,'month'=>$model->month));?>" ><?php echo Yii::t('commission','Renewal'); ?></a>
                </li>
            </ul>
            <div class="box-info" style="height: 1000px;position: relative;" >
                <div class="box-body" style="width: 400px;position: absolute;">
                    <div class="form-group" style="width: 400px;">
                        <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','city'); ?></label>
                        <div class="col-sm-7" >
                            <?php echo TbHtml::textField('city', General::getCityName($model->city),
                                array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                            ); ?>
                        </div>
                    </div>
                    <div class="form-group" style="width: 400px;">
                        <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','employee_name'); ?></label>
                        <div class="col-sm-7">
                            <?php echo $form->textField($model, 'employee_name',
                                array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                            ); ?>
                        </div>
                    </div>
                    <div class="form-group" style="width: 400px;">
                        <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','group_type'); ?></label>
                        <div class="col-sm-7">
                            <?php echo TbHtml::textField('group_type', IDCommissionForm::getGroupType($model->group_type,true),
                                array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                            ); ?>
                        </div>
                    </div>
                    <div class="form-group" style="width: 400px;">
                        <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','saleyear'); ?></label>
                        <div class="col-sm-7">
                            <?php echo TbHtml::textField('saleyear', $model->year."/".$model->month,
                                array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                            ); ?>
                        </div>
                    </div>
                </div>
                <div class="box-body" style="width: 800px;position: absolute;left:30%;">
                    <div class="form-group" style="width: 800px;">
                        <label class="col-sm-2 control-label" style="width: 130px;"><?php echo Yii::t('commission','new_amount'); ?></label>
                        <div class="col-sm-3">
                            <?php echo $form->textField($model, 'new_amount',
                                array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                            ); ?>
                        </div>
                    </div>
                    <div class="form-group" style="width: 800px;">
                        <label class="col-sm-2 control-label" style="width: 130px;"><?php echo Yii::t('commission','edit_amount'); ?></label>

                        <div class="col-sm-3">
                            <?php echo $form->textField($model, 'edit_amount',
                                array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                            ); ?>
                        </div>
                    </div>
                    <div class="form-group" style="width: 800px;">
                        <label class="col-sm-2 control-label" style="width: 130px;"><?php echo Yii::t('commission','renewal_amount'); ?></label>
                        <div class="col-sm-7">
                            <?php echo $form->textField($model, 'renewal_amount',
                                array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                            ); ?>
                        </div>
                    </div>
                    <div class="form-group" style="width: 800px;">
                        <label class="col-sm-2 control-label" style="width: 130px;"><?php echo Yii::t('commission','all_amount'); ?></label>
                        <div class="col-sm-7">
                            <?php echo $form->textField($model, 'sum_amount',
                                array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                            ); ?>
                        </div>
                    </div>
                </div>
                <div class="box-body" style="width: 400px;position: absolute;left:60%;">
                    <div class="form-group" style="width: 400px;">
                        <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','new_money_back'); ?></label>
                        <div class="col-sm-3">
                            <?php echo $form->textField($model, 'new_money',
                                array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                            ); ?>
                        </div>
                    </div>
                    <div class="form-group" style="width: 400px;">
                        <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','edit_money_back'); ?></label>
                        <div class="col-sm-3">
                            <?php echo $form->textField($model, 'edit_money',
                                array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                            ); ?>
                        </div>
                    </div>
                    <div class="form-group" style="width: 400px;">
                        <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','renewal_money_back'); ?></label>
                        <div class="col-sm-3">
                            <?php echo $form->textField($model, 'renewal_money',
                                array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                            ); ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</section>

<?php
//	echo $form->hiddenField($model,'pageNum');
//	echo $form->hiddenField($model,'totalRow');
//	echo $form->hiddenField($model,'orderField');
//	echo $form->hiddenField($model,'orderType');
//?>
<?php $this->endWidget(); ?>

<?php
$js = Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>

