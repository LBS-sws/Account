<?php
$this->pageTitle=Yii::app()->name . ' - ConsultSearch Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'ConsultSearch-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Consult Fee Search'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('consultSearch/index')));
		?>
        <?php echo TbHtml::button('<span class="fa fa-reply-all"></span> '.Yii::t('consult','Back'), array(
                'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
        );
        ?>
	</div>

            <div class="btn-group pull-right" role="group">
                <?php
                $counter = ($model->no_of_attm['consu'] > 0) ? ' <span id="docconsu" class="label label-info">'.$model->no_of_attm['consu'].'</span>' : ' <span id="docconsu"></span>';
                echo TbHtml::button('<span class="fa  fa-file-text-o"></span> '.Yii::t('misc','Attachment').$counter, array(
                        'name'=>'btnFile','id'=>'btnFile','data-toggle'=>'modal','data-target'=>'#fileuploadconsu',)
                );
                ?>
            </div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
			<?php echo $form->hiddenField($model, 'status'); ?>
            <?php echo CHtml::hiddenField('dtltemplate'); ?>

            <?php $this->renderPartial('//site/consultForm',array("model"=>$model,"form"=>$form)); ?>



            <div class="box">
                <div class="box-body table-responsive">
                    <div class="col-lg-7 col-lg-offset-2">
                        <?php
                        $this->widget('ext.layout.TableView2Widget', array(
                            'model'=>$model,
                            'attribute'=>'info_list',
                            'viewhdr'=>'//consultApply/_formhdr',
                            'viewdtl'=>'//consultApply/_formdtl',
                        ));
                        ?>
                    </div>
                </div>
            </div>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/fileupload',array('model'=>$model,
    'form'=>$form,
    'doctype'=>'CONSU',
    'header'=>Yii::t('dialog','File Attachment'),
    'ronly'=>($model->isReady()),
));
?>
<?php $this->renderPartial('//site/removedialog',array(
    "header"=>Yii::t('consult','Back'),
    "content"=>"<p>".Yii::t('consult','Are you sure to back?')."</p>"
)); ?>

<?php
Script::genFileUpload($model,$form->id,'CONSU');

$js = Script::genDeleteData(Yii::app()->createUrl('consultSearch/back'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


