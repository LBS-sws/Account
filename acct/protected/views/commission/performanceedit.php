<?php
$this->pageTitle=Yii::app()->name . ' - Month Report';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'tc-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
        <strong><?php echo Yii::t('app','Sales End Commission'); ?></strong>
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

<!--            --><?php //echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
//                'submit'=>Yii::app()->createUrl('commission/index_s')));
//            ?>
            <?php  echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save1'), array(
                    'submit'=>Yii::app()->createUrl('commission/performancesave',array('year'=>$year,'month'=>$month,'index'=>$index)))
            ); ?>

        </div>
    </div>
</div>

<section class="content" >
    <div class="box">
    <div id="yw0" class="tabbable">
        <ul class="nav nav-tabs" role="menu">
            <li class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/view',array('year'=>$year,'month'=>$month,'index'=>$index));?>" >总页</a>
            </li>
            <li  >
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/new',array('year'=>$year,'month'=>$month,'index'=>$index));?>" >新生意额</a>
            </li>
            <li  class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/edit',array('year'=>$year,'month'=>$month,'index'=>$index));?>" >更改生意额</a>
            </li>
            <li  class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/end',array('year'=>$year,'month'=>$month,'index'=>$index));?>" >终止生意额</a>
            </li>
            <li  class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/performance',array('year'=>$year,'month'=>$month,'index'=>$index));?>" >跨区新增生意额</a>
            </li>
            <li  class="active">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/performanceedit',array('year'=>$year,'month'=>$month,'index'=>$index));?>" >跨区更改生意额</a>
            </li>
            <li  class="">
                <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('commission/performanceend',array('year'=>$year,'month'=>$month,'index'=>$index));?>" >跨区终止生意额</a>
            </li>
        </ul>
        <div class="box-info" >
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
                    'title'=>Yii::t('app','sale commission'),
                    'model'=>$model,
                    'viewhdr'=>'//commission/t_listhdr',
                    'viewdtl'=>'//commission/t_listdtl',
                    'gridsize'=>'24',
                    'height'=>'600',
                    'hasNavBar'=>false,
                    'hasPageBar'=>false,
                    'hasSearchBar'=>false,
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
$js = <<<EOF

$(document).ready(function(){ 
       $("#chkboxAll").on('click',function() {     
       
              $("input[name='ReportXS01List[id][]']").prop("checked", this.checked);  
        });          
        $("input[name='ReportXS01List[id][]']").on('click',function() {  
              var subs = $("input[name='ReportXS01List[id][]']");  
              $("#chkboxAll").prop("checked" ,subs.length == subs.filter(":checked").length ? true :false);  
        });
});
EOF;
Yii::app()->clientScript->registerScript('starClick',$js,CClientScript::POS_HEAD);
$js = Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
