<?php
$this->pageTitle=Yii::app()->name . ' - Transaction Type Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('code','Transaction Type Form'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('transtype/index'))); 
		?>
		<?php 
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('transtype/new')));
			}
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('transtype/save'))); 
			?>
<?php endif ?>
<?php if ($model->scenario=='edit'): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'trans_type_code',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->textField($model, 'trans_type_code', 
						array('size'=>10,'maxlength'=>10,'readonly'=>($model->scenario!='new'))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'trans_type_desc',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textField($model, 'trans_type_desc', 
						array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'trans_cat',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->dropDownList($model, 'trans_cat', 
						array('IN'=>Yii::t('code','In'),'OUT'=>Yii::t('code','Out')),
						array('disabled'=>($model->scenario=='view'))
					) ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'adj_type',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
					<?php echo $form->dropDownList($model, 'adj_type', 
						array('N'=>Yii::t('misc','No'),'Y'=>Yii::t('misc','Yes')),
						array('disabled'=>($model->scenario=='view'))
					) ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'counter_type',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php 
						$list = array_merge(array(''=>Yii::t('misc','-- None --')),General::getTransTypeList('IN'),General::getTransTypeList('OUT'));
						echo $form->dropDownList($model, 'counter_type', $list,array('disabled'=>($model->scenario=='view'))); 
					?>
				</div>
			</div>

            <legend><?php echo Yii::t("give","JD System Curl");?></legend>
            <?php
            $html = "";
            $className = get_class($model);
            foreach (TransTypeForm::$jd_set_list as $num=>$item){
                $field_value = key_exists($item["field_id"],$model->jd_set)?$model->jd_set[$item["field_id"]]:null;
                if($num%2==0){
                    $html.='<div class="form-group">';
                }
                $html.=TbHtml::label(Yii::t("give",$item["field_name"]),'',array('class'=>"col-sm-2 control-label"));
                $html.='<div class="col-lg-3">';
                $html.=TbHtml::textField("{$className}[jd_set][{$item["field_id"]}]",$field_value,array('readonly'=>($model->scenario=='view')));
                $html.="</div>";
                if($num%2==1){
                    $html.='</div>';
                }
            }
            if(count(TransTypeForm::$jd_set_list)%2==0){
                $html.='</div>';
            }
            echo $html;
            ?>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
$js = Script::genDeleteData(Yii::app()->createUrl('transtype/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


