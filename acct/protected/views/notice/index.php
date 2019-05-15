<?php
$this->pageTitle=Yii::app()->name . ' - Notification';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'notice-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('queue','System Notification'); ?></strong> <small><?php echo Yii::t('queue','** Records will be kept in the system for 30 days only.'); ?></small>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			echo TbHtml::button('<span class="fa fa-refresh"></span> '.Yii::t('misc','Refresh'), array(
				'submit'=>Yii::app()->createUrl('notice/index'), 
			)); 
		?>
	</div>
	</div></div>
	<?php $this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('queue','Notification List'),
			'model'=>$model,
				'viewhdr'=>'//notice/_listhdr',
				'viewdtl'=>'//notice/_listdtl',
				'search'=>array(
							'note_dt',
							'note_type',
							'subject',
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

<?php
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
