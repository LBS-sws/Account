<?php
$this->pageTitle=Yii::app()->name . ' - User';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'user-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('user','User'); ?></strong>
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
			if (Yii::app()->user->validRWFunction('D01'))
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
					'submit'=>Yii::app()->createUrl('user/new'), 
				)); 
		?>
	</div>
	</div></div>
	<?php $this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('user','User List'),
			'model'=>$model,
			'viewhdr'=>'//user/_listhdr',
			'viewdtl'=>'//user/_listdtl',
			'gridsize'=>'24',
			'height'=>'600',
			'search'=>array(
						'username',
						'disp_name',
						'group_name',
						'city',
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
	$js = "
$(function() {
	$('#tblData').DataTable({
		'paging':false,
		'lengthChange':true,
		'searching':false,
		'ordering':false,
		'info':false,
		'autoWidth':true
	});
});
	";
//	Yii::app()->clientScript->registerScript('datatable',$js,CClientScript::POS_READY);

	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
