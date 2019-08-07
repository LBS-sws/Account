<?php
$this->pageTitle=Yii::app()->name . ' - commission Report';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'tc-list',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
        <strong><?php echo Yii::t('app','Sales New Commission'); ?></strong>
	</h1>
<!--
	<ol class="breadcrumb">
		<li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
		<li><a href="#">Layout</a></li>
		<li class="active">Top Navigation</li>
	</ol>
-->
</section>
<div class="box"><div class="box-body">
        <div class="btn-group" role="group">

            <?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
                'submit'=>Yii::app()->createUrl('commission/index_s')));
            ?>
            <?php
                echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Record'), array(
                    'submit'=>Yii::app()->createUrl('commission/add',array('index'=>$index)),
                ));
            ?>
            <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
                    'submit'=>Yii::app()->createUrl('commission/newsave',array('index'=>$index)))
            ); ?>
        </div>
    </div>
</div>
<section class="content" >
    <div class="box">
    <div id="yw0" class="tabbable">
        <ul class="nav nav-tabs" role="menu">
            <li>
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/view',array('index'=>$index));?>" >总页</a>
            </li>
            <li class="active">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/new',array('index'=>$index));?>">新生意额</a>
            </li>
            <li  class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/edit',array('index'=>$index));?>" >更改生意额</a>
            </li>
            <li  class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/end',array('index'=>$index));?>" >终止生意额</a>
            </li>
        </ul>
        <div class="box-info" style="height: 1000px;" >
            <div class="box-body" >
                <?php
                $search = array(
                    'city_name',
                    'first_dt',
                    'sign_dt',
                    'company_name',
                    'service',
                    'type_desc',
                    'amt_install'
                );
                $this->widget('ext.layout.ListPageWidget', array(
                    'title'=>Yii::t('app','sale commission man'),
                    'model'=>$model,
                    'viewhdr'=>'//commission/s_listhdr',
                    'viewdtl'=>'//commission/s_listdtl',
                    'gridsize'=>'24',
                    'height'=>'600',
                    'search'=>$search,
                ));
                echo TBhtml::button('dummyButtin',array('style'=>'display:none','disabled'=>true,'submit'=>'#',))
                ?>
            </div>
        </div>
    </div>
    </div>
</section>

<?php
//	echo $form->hiddenField($model,'pageNum');
//	echo $form->hiddenField($model,'totalRow');
//	echo $form->hiddenField($model,'orderField');
//	echo $form->hiddenField($model,'orderType');
//?>
<?php $this->endWidget(); ?>

<?php
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>

