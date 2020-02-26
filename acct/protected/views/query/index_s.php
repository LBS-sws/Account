<?php
$this->pageTitle=Yii::app()->name . ' - query Report';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'commission-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Sales Commission history'); ?></strong>
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
                                'submit'=>Yii::app()->createUrl('query/index')));
                            ?>
            </div>
        </div></div>
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
				'viewhdr'=>'//query/_listhdr',
				'viewdtl'=>'//query/_listdtl',
				'gridsize'=>'24',
				'height'=>'600',
				'search'=>$search,
                'hasNavBar'=>false,
                'hasPageBar'=>false,
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

