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
    <div id="yw0" class="tabbable">
        <ul class="nav nav-tabs" role="menu">
            <li class="active">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/view',array('index'=>$index));?>" >总页</a>
            </li>
            <li  >
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/new',array('index'=>$index));?>">新生意额</a>
            </li>
            <li  class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/edit',array('index'=>$index));?>" >更改生意额</a>
            </li>
            <li  class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/end',array('index'=>$index));?>" >终止生意额</a>
            </li>
        </ul>
        <div class="box-info" style="height: 1000px;" >
            <div class="box-body" style="width: 400px;position: absolute;">
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 100px;">城市</label>
                    <div class="col-sm-7" >
                        <?php echo $form->textField($model, 'city',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 100px;">销售员</label>
                    <div class="col-sm-7">
                        <?php echo $form->textField($model, 'employee_name',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 100px;">提成月份</label>
                    <div class="col-sm-7">
                        <?php echo $form->textField($model, 'year',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
            </div>
            <div class="box-body" style="width: 400px;position: absolute;margin-left:500px;">
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;">新增生意额提成</label>
                    <div class="col-sm-7">
                        <?php echo $form->textField($model, 'new_amount',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;">更改生意额提成</label>
                    <div class="col-sm-7">
                        <?php echo $form->textField($model, 'edit_amount',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;">终止生意额提成</label>
                    <div class="col-sm-7">
                        <?php echo $form->textField($model, 'end_amount',
                            array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
                        ); ?>
                    </div>
                </div>
                <div class="form-group" style="width: 400px;">
                    <label class="col-sm-2 control-label" style="width: 150px;">总额</label>
                    <div class="col-sm-7">
                        <?php echo $form->textField($model, 'all_amount',
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

