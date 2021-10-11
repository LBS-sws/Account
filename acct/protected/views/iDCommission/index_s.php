<?php
$this->pageTitle=Yii::app()->name . ' - query Report';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'commission-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
'action'=>Yii::app()->createUrl('IDCommission/index_s',array('type'=>$model->type,'year'=>$model->year,'month'=>$model->month,'city'=>$model->city)),
)); ?>

<section class="content-header">
	<h1>
		<strong>
            <?php
            if($this->type == 1){
                echo "ID ".Yii::t('app','Sales Commission');
            }else{
                echo "ID ".Yii::t('app','Sales Commission history');
            }
            ?>
        </strong>
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
                                'submit'=>Yii::app()->createUrl('IDCommission/index')));
                            ?>
            </div>
        </div></div>
	<?php
    $search = array(
        'employee_code',
        'employee_name',
        'city',
    );
    $this->widget('ext.layout.ListPageWidget', array(
			'title'=>Yii::t('app','sale commission man'),
			'model'=>$model,
				'viewhdr'=>'//iDCommission/_listhdr',
				'viewdtl'=>'//iDCommission/_listdtl',
				'gridsize'=>'24',
				'height'=>'600',
				'search'=>$search,
                'hasNavBar'=>false,
                'hasPageBar'=>false,
                'searchlinkparam'=>array(
                        "year"=>$model->year,
                        "month"=>$model->month,
                        "city"=>$model->city,
                        "type"=>$this->type,
                ),
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

