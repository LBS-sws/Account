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
		<strong><?php echo Yii::t('report','Sales Commission All'); ?></strong>
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

            <?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                'submit'=>Yii::app()->createUrl('commission/index_s')));
            ?>

        </div>
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
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/view',array('year'=>$year,'month'=>$month,'index'=>$index));?>" ><?php echo Yii::t('commission','ALL'); ?></a>
            </li>
            <li  >
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/new',array('year'=>$year,'month'=>$month,'index'=>$index));?>" ><?php echo Yii::t('commission','New'); ?></a>
            </li>
            <li  class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/edit',array('year'=>$year,'month'=>$month,'index'=>$index));?>" ><?php echo Yii::t('commission','Edit'); ?></a>
            </li>
            <li  class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/end',array('year'=>$year,'month'=>$month,'index'=>$index));?>" ><?php echo Yii::t('commission','END'); ?></a>
            </li>
            <li  class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/performance',array('year'=>$year,'month'=>$month,'index'=>$index));?>" ><?php echo Yii::t('commission','Performance'); ?></a>
            </li>
            <li  class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/performanceedit',array('year'=>$year,'month'=>$month,'index'=>$index));?>" ><?php echo Yii::t('commission','PerformanceEdit'); ?></a>
            </li>
            <li  class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/performanceend',array('year'=>$year,'month'=>$month,'index'=>$index));?>" ><?php echo Yii::t('commission','PerformanceEnd'); ?></a>
            </li>
            <li  class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/renewal',array('year'=>$year,'month'=>$month,'index'=>$index));?>" ><?php echo Yii::t('commission','Renewal'); ?></a>
            </li>
            <li  class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/renewalend',array('year'=>$year,'month'=>$month,'index'=>$index));?>" ><?php echo Yii::t('commission','RenewalEnd'); ?></a>
            </li>
            <li  class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/product',array('year'=>$year,'month'=>$month,'index'=>$index));?>" ><?php echo Yii::t('commission','Prodcct'); ?></a>
            </li>
        </ul>
        <div class="box-info" style="height: 1000px;position: relative;" >
            <div class="box-body" style="width: 400px;position: absolute;">
                			<?php echo $form->hiddenField($model, 'year'); ?>
                			<?php echo $form->hiddenField($model, 'month'); ?>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','city'); ?></label>
                    <div class="col-sm-7" >
                        <?php echo $form->textField($model, 'city',
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
                        <?php echo $form->textField($model, 'group_type',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','saleyear'); ?></label>
                    <div class="col-sm-7">
                        <?php echo $form->textField($model, 'saleyear',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','new_calc'); ?></label>
                    <div class="col-sm-7">
                        <?php echo $form->textField($model, 'new_calc',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','performance'); ?></label>
                    <div class="col-sm-7">
                        <?php echo $form->textField($model, 'performance',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','point'); ?></label>
                    <div class="col-sm-7">
                        <?php echo $form->textField($model, 'point',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;"><?php echo ReportXS01Form::getServiceStr($model->year,$model->month); ?></label>
                    <div class="col-sm-7">
                        <?php echo $form->textField($model, 'service_reward',
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
                    <label class="col-sm-2 control-label" style="width: 130px;"><?php echo Yii::t('commission','end_amount'); ?></label>
                    <div class="col-sm-7">
                        <?php echo $form->textField($model, 'end_amount',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 800px;">
                    <label class="col-sm-2 control-label" style="width: 130px;"><?php echo Yii::t('commission','performance_amount'); ?></label>
                    <div class="col-sm-3">
                        <?php echo $form->textField($model, 'performance_amount',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 800px;">
                    <label class="col-sm-2 control-label" style="width: 130px;"><?php echo Yii::t('commission','performanceedit_amount'); ?></label>
                    <div class="col-sm-3">
                        <?php echo $form->textField($model, 'performanceedit_amount',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 800px;">
                    <label class="col-sm-2 control-label" style="width: 130px;"><?php echo Yii::t('commission','performanceend_amount'); ?></label>
                    <div class="col-sm-3">
                        <?php echo $form->textField($model, 'performanceend_amount',
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
                    <label class="col-sm-2 control-label" style="width: 130px;"><?php echo Yii::t('commission','renewalend_amount'); ?></label>
                    <div class="col-sm-7">
                        <?php echo $form->textField($model, 'renewalend_amount',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 800px;">
                    <label class="col-sm-2 control-label" style="width: 130px;"><?php echo Yii::t('commission','product_amount'); ?></label>
                    <div class="col-sm-7">
                        <?php echo $form->textField($model, 'product_amount',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 800px;">
                    <label class="col-sm-2 control-label" style="width: 130px;"><?php echo Yii::t('commission','all_amount'); ?></label>
                    <div class="col-sm-7">
                        <?php echo $form->textField($model, 'all_amount',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
            </div>
            <div class="box-body" style="width: 400px;position: absolute;left:60%;">
                <?php echo $form->hiddenField($model, 'year'); ?>
                <?php echo $form->hiddenField($model, 'month'); ?>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','new_money'); ?></label>
                    <div class="col-sm-3">
                        <?php echo $form->textField($model, 'new_money',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','edit_money'); ?></label>
                    <div class="col-sm-3">
                        <?php echo $form->textField($model, 'edit_money',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;">&nbsp;</label>
                    <div class="col-sm-3">
                        <div style="height: 34px;width: 196px"></div>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','out_money'); ?></label>
                    <div class="col-sm-3">
                        <?php echo $form->textField($model, 'out_money',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','performanceedit_money'); ?></label>
                    <div class="col-sm-3">
                        <?php echo $form->textField($model, 'performanceedit_money',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;">&nbsp;</label>
                    <div class="col-sm-3">
                        <div style="height: 34px;width: 196px"></div>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;"><?php echo Yii::t('commission','renewal_money'); ?></label>
                    <div class="col-sm-3">
                        <?php echo $form->textField($model, 'renewal_money',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;">&nbsp;</label>
                    <div class="col-sm-3">
                        <div style="height: 34px;width: 196px"></div>
                    </div>
                </div>


<!--                <div class="form-group" style="width: 400px;">-->
<!--                    <label class="col-sm-2 control-label" style="width: 130px;">xxxxx</label>-->
<!--                    <div class="col-sm-7">-->
<!--                        --><?php //echo $form->textField($model, 'new_calc',
//                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
//                        ); ?>
<!--                    </div>-->
<!--                </div>-->
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

