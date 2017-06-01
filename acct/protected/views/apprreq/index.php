<?php
$this->pageTitle=Yii::app()->name . ' - Payment Request Approval';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'request-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('trans','Request Approval'); ?></strong>
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
	<div id="yw0" class="tabbable">
		<ul class="nav nav-tabs" role="menu">
			<li <?php echo (($type=='P') ? 'class="active"' : ''); ?> role="menuitem">
				<a tabindex="-1" href="<?php echo Yii::app()->createUrl('apprreq/index', array('type'=>'P'));?>">
					<?php echo Yii::t('trans','Pending (Direct)'); ?>
				</a>
			</li>
			<li <?php echo (($type=='Q') ? 'class="active"' : ''); ?> role="menuitem">
				<a tabindex="-1" href="<?php echo Yii::app()->createUrl('apprreq/index', array('type'=>'Q'));?>">
					<?php echo Yii::t('trans','Pending (Related)'); ?>
				</a>
			</li>
		</ul>	
	<?php 
		$search = array(
						'req_dt',
						'trans_type_desc',
						'payee_name',
						'item_desc',
						'ref_no',
					);
		if (!Yii::app()->user->isSingleCity()) $search[] = 'city_name';
		
		$this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('trans','Request List'),
			'model'=>$model,
				'viewhdr'=>'//apprreq/_listhdr',
				'viewdtl'=>'//apprreq/_listdtl',
				'search'=>$search,
		));

	?>
	</div>
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


