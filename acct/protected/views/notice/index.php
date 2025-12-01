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
	<div class="btn-group pull-right" role="group">
		<?php 
			echo TbHtml::button('<span class="fa fa-flag"></span> '.Yii::t('misc','Mark Read'), array(
				'name'=>'btnMark','id'=>'btnMark','data-toggle'=>'modal','data-target'=>'#markreaddialog',)
			);
		?>
	</div>
	</div></div>
	<?php $this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('queue','Notification List'),
			'model'=>$model,
				'viewhdr'=>'//notice/_listhdr',
				'viewdtl'=>'//notice/_listdtl',
				'search'=>array(
							'lcd',
							'flow_title',
							'ready_bool',
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

<?php $this->renderPartial('//notice/markreaddialog'); ?>

<?php $this->endWidget(); ?>

<?php
	$link = Yii::app()->createUrl('notice/markread');
	$js = <<<EOF
$('#btnMarkRead').on('click',function() {
	$('#markreaddialog').modal('hide');
	markread();
});

function markread() {
	var elm=$('#btnMarkRead');
	jQuery.yii.submitForm(elm,'$link',{});
}
EOF;
	Yii::app()->clientScript->registerScript('noticeMarkRead',$js,CClientScript::POS_READY);

	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
