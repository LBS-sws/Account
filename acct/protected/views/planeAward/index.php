<?php
$this->pageTitle=Yii::app()->name . ' - PlaneAward';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'planeAward-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>
<style>
    .pt-7{ padding-top: 7px;text-align: right;}
</style>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Plane Award'); ?></strong>
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
    <div class="box">
        <div class="box-body">
            <div class="btn-group" role="group">
                <?php
                echo TbHtml::button('<span class="fa fa-paste"></span> '.Yii::t('plane','plane paste'), array(
                    'submit'=>Yii::app()->createUrl('planeAward/paste'),
                ));
                ?>
            </div>
            <div class="btn-group pull-right" role="group">
                <?php
                echo TbHtml::button('<span class="fa fa-download"></span> '.Yii::t('dialog','Download'), array(
                    'submit'=>Yii::app()->createUrl('planeAward/down'),
                ));
                ?>
            </div>
        </div>
    </div>
	<?php
    $modelClass=get_class($model);
    $search_add_html="";
    $search_add_html .= TbHtml::dropDownList("{$modelClass}[year]",$model->year,PlaneAllotList::getYearList(),
        array("class"=>"form-control submitBtn"));
    $search_add_html.="<span>&nbsp;&nbsp;-&nbsp;&nbsp;</span>";
    $search_add_html .= TbHtml::dropDownList("{$modelClass}[month]",$model->month,PlaneAllotList::getMonthList(),
        array("class"=>"form-control submitBtn"));

    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('plane','Plane Allot List'),
        'model'=>$model,
        'viewhdr'=>'//planeAward/_listhdr',
        'viewdtl'=>'//planeAward/_listdtl',
        'gridsize'=>'24',
        'height'=>'600',
        'search_add_html'=>$search_add_html,
        'search'=>array(
            'code',
            'name',
            'city_name'
        ),
    ));
    echo TbHtml::button("test",array("submit"=>"#","class"=>"hide"));
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
$allotOneUrl = Yii::app()->createUrl('planeAward/allotOne');
$js = "
$('.submitBtn').change(function(){
    $('form:first').submit();
});

";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
