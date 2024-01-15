<?php
$this->pageTitle=Yii::app()->name . ' - Approver Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('code','Approver Form'); ?></strong>
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
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('approver/save'))); 
			?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'city'); ?>

<?php
            $maxList=array(
                'regionSuper'=>500,
                'regionMgrA'=>500,
                'regionMgr'=>1000,
                'regionHeight'=>2000,//高级总经理
                'regionDirectorA'=>3000,
                'regionDirector'=>6000,
                'regionHead'=>999999,
            );
	foreach ($model->dynfields as $field) {
        $text = key_exists($field,$maxList)?$maxList[$field]:"error";
        $text = $text==999999?"所有付款申请":"付款申请 ".$text." 以内";
		echo '<div class="form-group">';
		echo $form->labelEx($model,$field,array('class'=>"col-sm-2 control-label"));
		echo '<div class="col-sm-6">';
		$optionList = General::getEmailListboxData();
		if($field=="regionHeight"){
            echo $form->dropDownList($model, $field, $optionList,
                array('disabled'=>($model->scenario=='view'),'empty'=>''));
        }else{
            echo $form->dropDownList($model, $field, $optionList,
                array('disabled'=>($model->scenario=='view')));
        }
		echo '</div>';
		echo '<div class="col-sm-4">';
		echo "<p class='form-control-static text-danger'>{$text}</p>";
		echo '</div>';
		echo '</div>';
	}
?>
		</div>
	</div>
</section>

<?php
$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


