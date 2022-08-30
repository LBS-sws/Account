<?php
$this->pageTitle=Yii::app()->name . ' - ConsultApply';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'consultApply-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Consult Fee Apply'); ?></strong>
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
		<?php 
			if (Yii::app()->user->validRWFunction('CF01'))
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add'), array(
					'submit'=>Yii::app()->createUrl('consultApply/new'),
				)); 
		?>
	</div>
	</div></div>
    <?php $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('consult','Apply List'),
        'model'=>$model,
        'viewhdr'=>'//consultApply/_listhdr',
        'viewdtl'=>'//consultApply/_listdtl',
        'gridsize'=>'24',
        'height'=>'600',
        'search'=>array(
            'consult_code',
            //'customer_code',
            'apply_city',
            'audit_city',
        ),
    ));
    ?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
?>
<?php $this->endWidget(); ?>

<?php
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
