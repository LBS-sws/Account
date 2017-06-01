<?php
$this->pageTitle=Yii::app()->name . ' - Report Manager';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'queue-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('queue','Report Manager'); ?></strong> <small><?php echo Yii::t('queue','** Records will be kept in the system for 14 days only.'); ?></small>
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
			if (Yii::app()->user->validRWFunction('XB01'))
				echo TbHtml::button('<span class="fa fa-refresh"></span> '.Yii::t('misc','Refresh'), array(
					'submit'=>Yii::app()->createUrl('queue/index'), 
				)); 
		?>
	</div>
	</div></div>
	<?php $this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('queue','Queue List'),
			'model'=>$model,
				'viewhdr'=>'//queue/_listhdr',
				'viewdtl'=>'//queue/_listdtl',
				'gridsize'=>'24',
				'height'=>'600',
				'search'=>array(
							'rpt_desc',
							'status',
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

