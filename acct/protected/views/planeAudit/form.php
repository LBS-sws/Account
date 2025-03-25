<?php
$this->pageTitle=Yii::app()->name . ' - PlaneAudit Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'PlaneAudit-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Audit for plane'); ?></strong>
	</h1>
<!--
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Layout</a></li>
		<li class="active">Top Navigation</li>
	</ol>
-->
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('planeAudit/index')));
		?>
        <?php if ($model->plane_status==1): ?>
            <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Audit'), array(
                'submit'=>Yii::app()->createUrl('planeAudit/finish')));
            ?>
            <?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Deny'), array(
                'id'=>'btnDeny', 'name'=>'btnDeny'));
            ?>
        <?php endif ?>
	</div>
            <?php if (Yii::app()->user->validRWFunction('PS07')&&$model->plane_status==2): ?>
                <div class="btn-group pull-right" role="group">
                    <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('plane','revoke'), array(
                        'submit'=>Yii::app()->createUrl('planeAudit/revoke')));
                    ?>
                </div>
            <?php endif ?>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
            <?php echo CHtml::hiddenField('dtltemplate1'); ?>
            <?php echo CHtml::hiddenField('dtltemplate2'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'employee_id'); ?>
			<?php echo $form->hiddenField($model, 'plane_date'); ?>
			<?php echo $form->hiddenField($model, 'plane_year'); ?>
			<?php echo $form->hiddenField($model, 'plane_month'); ?>
			<?php echo $form->hiddenField($model, 'plane_status'); ?>
			<?php echo $form->hiddenField($model, 'city'); ?>

            <?php if ($model->plane_status==3): ?>
                <div class="form-group has-error">
                    <?php echo $form->labelEx($model,'reject_txt',array('class'=>"col-lg-2 control-label")); ?>
                    <div class="col-lg-8 ">
                        <?php echo $form->textArea($model, 'reject_txt',
                            array('readonly'=>(true),'rows'=>3)
                        ); ?>
                    </div>
                </div>
            <?php endif ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'employee_code',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'employee_code',
					array('readonly'=>(true))
				); ?>
				</div>
				<?php echo $form->labelEx($model,'employee_name',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'employee_name',
					array('readonly'=>(true))
				); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'city_name',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'city_name',
					array('readonly'=>(true))
				); ?>
				</div>
                <?php echo $form->labelEx($model,'entry_time',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'entry_time',
                        array('readonly'=>(true))
                    ); ?>
                </div>
			</div>

			<div class="form-group">
                <?php echo $form->labelEx($model,'old_money_value',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'old_money_value',
                        array('readonly'=>(true))
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'show_date',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'show_date',
                        array('readonly'=>(true))
                    ); ?>
                </div>
			</div>
            <div class="form-group">
                <?php echo $form->hiddenField($model, 'money_id'); ?>
                <?php echo $form->labelEx($model,'money_value',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->numberField($model, 'money_value',
                        array('readonly'=>($model->isReadOnly()))
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'money_num',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'money_num',
                        array('readonly'=>(true))
                    ); ?>
                </div>
            </div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'job_id',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'job_id',
					array('readonly'=>(true))
				); ?>
				</div>
				<?php echo $form->labelEx($model,'job_num',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'job_num',
					array('readonly'=>(true))
				); ?>
				</div>
			</div>
			<div class="form-group">
                <?php echo $form->hiddenField($model, 'year_id'); ?>
                <?php echo $form->labelEx($model,'year_month',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'year_month',
                        array('readonly'=>(true),'append'=>'年')
                    ); ?>
                </div>
				<?php echo $form->labelEx($model,'year_num',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'year_num',
					array('readonly'=>(true))
				); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'old_take_amt',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'old_take_amt',
					array('readonly'=>(true))
				); ?>
				</div>
				<?php echo $form->labelEx($model,'other_sum',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'other_sum',
					array('readonly'=>(true))
				); ?>
				</div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'take_amt',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->textField($model, 'take_amt',
					array('readonly'=>(true))
				); ?>
				</div>
                <?php echo $form->labelEx($model,'plane_sum',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php echo $form->textField($model, 'plane_sum',
                        array('readonly'=>(true))
                    ); ?>
                </div>
			</div>
			<div class="form-group">
				<?php echo $form->labelEx($model,'old_pay_wage',array('class'=>"col-lg-2 control-label")); ?>
				<div class="col-lg-2">
				<?php echo $form->numberField($model, 'old_pay_wage',
					array('readonly'=>($model->isReadOnly()))
				); ?>
				</div>
                <?php echo $form->labelEx($model,'plane_status',array('class'=>"col-lg-2 control-label")); ?>
                <div class="col-lg-2">
                    <?php
                    echo TbHtml::textField("plane_status",PlaneAwardList::getPlaneStatusList($model->plane_status)["str"],
                        array('readonly'=>(true))
                    );
                    ?>
                </div>
			</div>


            <div class="box">
                <div class="box-body table-responsive">
                    <div class="col-lg-6 col-lg-offset-3">
                        <?php
                        $this->widget('ext.layout.TableView2Widget', array(
                            'model'=>$model,
                            'tableidx'=>1,
                            'attribute'=>'info_list',
                            'viewhdr'=>'//planeAward/_formhdr',
                            'viewdtl'=>'//planeAward/_formdtl',
                        ));
                        ?>
                    </div>
                </div>
            </div>


            <div class="box">
                <div class="box-body table-responsive">
                    <div class="col-lg-8 col-lg-offset-2">
                        <?php
                        $this->widget('ext.layout.TableView2Widget', array(
                            'model'=>$model,
                            'tableidx'=>2,
                            'attribute'=>'infoDetail',
                            'viewhdr'=>'//planeAward/t_formhdr',
                            'viewdtl'=>'//planeAward/t_formdtl',
                        ));
                        ?>
                    </div>
                </div>
            </div>
		</div>
	</div>
</section>
<?php $this->renderPartial('//planeAudit/reject',array('model'=>$model,'form'=>$form)); ?>

<?php
$js = "
";
Yii::app()->clientScript->registerScript('setFlag',$js,CClientScript::POS_READY);


$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
$js=<<<EOF
$('#btnDeny').on('click',function(){
	$('#rmkdialog').modal('show');
});
EOF;
Yii::app()->clientScript->registerScript('denyPopup',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


