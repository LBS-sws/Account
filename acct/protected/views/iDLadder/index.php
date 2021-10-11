<?php
$this->pageTitle=Yii::app()->name . ' - Service Rate';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'code-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong>ID<?php echo Yii::t('service','Service Rate'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php 
			if (Yii::app()->user->validRWFunction('XS09'))
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
					'submit'=>Yii::app()->createUrl('IDLadder/new'),
				)); 
		?>
	</div>
	</div>
    </div>

    <div class="box">
        <div class="box-body">
            <div class="btn-group text-info" role="group">
                <p><b>注：</b>未设置的客户类型提成比例默认为0.15</p>
            </div>
        </div>
    </div>
	<?php 
		$search = array(
						'name',
						'city_name',
					);
		$this->widget('ext.layout.ListPageWidget', array(
			'title'=>"ID".Yii::t('service','Service Rate List'),
			'model'=>$model,
				'viewhdr'=>'//iDLadder/_listhdr',
				'viewdtl'=>'//iDLadder/_listdtl',
				'search'=>$search,
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


