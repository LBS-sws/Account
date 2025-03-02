<?php
$this->pageTitle=Yii::app()->name . ' - Report';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'report-form',
'action'=>Yii::app()->createUrl('report/generate'),
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('report',$model->name); ?></strong>
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
		<?php echo TbHtml::button(Yii::t('misc','Submit'), array(
				'submit'=>Yii::app()->createUrl('report/generate'))); 
		?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'name'); ?>
			<?php echo $form->hiddenField($model, 'fields'); ?>
			<?php echo $form->hiddenField($model, 'target_dt'); ?>
			<?php echo $form->hiddenField($model, 'email'); ?>
			<?php echo $form->hiddenField($model, 'emailcc'); ?>
			<?php echo $form->hiddenField($model, 'form'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'start_dt',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<?php echo $form->textField($model, 'start_dt', 
							array('class'=>'form-control pull-right','readonly'=>($model->scenario=='view'),)); 
						?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'end_dt',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<div class="input-group date">
						<div class="input-group-addon">
							<i class="fa fa-calendar"></i>
						</div>
						<?php echo $form->textField($model, 'end_dt', 
							array('class'=>'form-control pull-right','readonly'=>($model->scenario=='view'),)); 
						?>
					</div>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'format',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php 
						$item = array('EXCEL'=>'Excel','PDF'=>'PDF');
						if ($model->showField('format_e')) $item = array('EXCEL'=>'Excel','PDF'=>'PDF','EMAIL'=>Yii::t('report','Email'));
						echo $form->dropDownList($model, 'format', 
							$item, array('disabled'=>($model->scenario=='view'))
						); 
					?>
				</div>
			</div>

            <div class="form-group">
                <?php
                echo TbHtml::label("快捷操作","",array('class'=>"col-sm-2 control-label"));
                ?>
                <div class="col-sm-10">
                    <?php
                    echo TbHtml::checkBox("0",false,array('label'=>"全部","class"=>"fastChange",'data-city'=>"",'labelOptions'=>array("class"=>"checkbox-inline")));
                    $fastCityList = General::getCityListForArea();
                    foreach ($fastCityList as $row){
                        echo TbHtml::checkBox($row["code"],false,array('label'=>$row["name"],"class"=>"fastChange",'data-city'=>$row["city"],'labelOptions'=>array("class"=>"checkbox-inline")));
                    }
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'city',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-10" id="report_look_city">
                    <?php
                    $item = General::getCityListWithCityAllow(Yii::app()->user->city_allow());
                    if (empty($model->city)) {
                        $model->city = array();
                        foreach ($item as $key=>$value) {$model->city[] = $key;}
                    }
                    echo $form->inlineCheckBoxList($model,'city', $item,
                        array('id'=>'look_city'));
                    ?>
                </div>
            </div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'type',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php 
						$item = General::getFeedbackCatList();
						if (empty($model->type)) {
							$model->type = array();
							foreach ($item as $key=>$value) {$model->type[] = $key;}
						}
						echo $form->listbox($model, 'type', $item, 
							array('size'=>6,'multiple'=>'multiple')
						); 
					?>
				</div>
			</div>
		</div>
	</div>
</section>

<?php
$js = Script::genDatePicker(array(
			'ReportForm_start_dt',
			'ReportForm_end_dt',
		));
Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);

?>

<?php
$js="
$('.fastChange').change(function(){
    var cityStr = ','+$(this).data('city')+',';
    console.log(cityStr);
    var checkBool = $(this).is(':checked')?true:false;
    $('#report_look_city').find('input[type=\"checkbox\"]').each(function(){
        var city = ','+$(this).val()+',';
        if(cityStr==',,'||cityStr.indexOf(city)>-1){
            $(this).prop('checked',checkBool);
        }
    });
});
";
Yii::app()->clientScript->registerScript('fastChange',$js,CClientScript::POS_READY);
?>
<?php $this->endWidget(); ?>

</div><!-- form -->

