<?php
$this->pageTitle=Yii::app()->name . ' - commission Report';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'commission-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Sales Commission'); ?></strong>
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
    <input type="hidden" name="year" value="<?php echo $year;?>">
    <input type="hidden" name="month" value="<?php echo $month;?>">
	<?php
    $search = array(
        'employee_code',
        'employee_name',
        'city',
        'user_name',
    );
    $this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('app','sale commission man'),
			'model'=>$model,
				'viewhdr'=>'//commission/_listhdr',
				'viewdtl'=>'//commission/_listdtl',
				'gridsize'=>'24',
				'height'=>'600',
				'search'=>$search,
		));
    echo TBhtml::button('dummyButtin',array('style'=>'display:none','disabled'=>true,'submit'=>'#',))
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

