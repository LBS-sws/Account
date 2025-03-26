<?php
$this->pageTitle=Yii::app()->name . ' - PerformanceBonus';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'PerformanceBonus-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Quarterly performance bonus'); ?></strong>
	</h1>
</section>

<section class="content">
    <div class="box"><div class="box-body">
            <div class="btn-group" role="group">
                <?php echo TbHtml::button('<span class="fa fa-save"></span> '.Yii::t('service','batch fixed'), array(
                    'submit'=>Yii::app()->createUrl('performanceBonus/batchSave')));
                ?>
            </div>
            <div class="btn-group pull-right" role="group">
                <?php echo TbHtml::button('<span class="fa fa-download"></span> '.Yii::t('service','down fixed'), array(
                    'submit'=>Yii::app()->createUrl('performanceBonus/downFixed')));
                ?>
            </div>
        </div></div>
	<?php
    echo TbHtml::button("test", array(
        'submit'=>Yii::app()->createUrl('test/new'),
        'class'=>'hide'
    ));
    $modelClass=get_class($model);
    $search_add_html="";
    $search_add_html .= TbHtml::dropDownList("{$modelClass}[year_no]",$model->year_no,PerformanceBonusList::getYearList(),
        array("class"=>"form-control submitBtn","id"=>"selectYear"));
    $search_add_html.="<span>&nbsp;&nbsp;-&nbsp;&nbsp;</span>";
    $search_add_html .= TbHtml::dropDownList("{$modelClass}[month_no]",$model->month_no,PerformanceBonusList::getMonthList(),
        array("class"=>"form-control submitBtn","id"=>"selectMonth"));

    $search = array(
        'code',
        'name',
        'city_name',
        'dept_name',
    );
    $this->widget('ext.layout.ListPageWidget', array(
        'title'=>Yii::t('app','Quarterly performance bonus'),
        'model'=>$model,
        'viewhdr'=>'//performanceBonus/_listhdr',
        'viewdtl'=>'//performanceBonus/_listdtl',
        'search'=>$search,
        'search_add_html'=>$search_add_html,
    ));
    ?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
    echo TbHtml::hiddenField('checkList','',array("id"=>"attrStr"));
?>
<?php $this->endWidget(); ?>

<?php
$js = "
$('.submitBtn').change(function(){
    $('form:first').submit();
});

$('.che').on('click', function(e){
    e.stopPropagation();
});

$('body').on('click','#all',function() {
	var val = $(this).prop('checked');
	$('.che').children('input[type=checkbox]').prop('checked',val);
});

$('#PerformanceBonus-list').submit(function(){
    var list = [];
    $('input[type=checkbox]:checked').each(function(){
        var id = $(this).val();
        if(id!=''&&list.indexOf(id)==-1&&$(this).parent('td.che').length==1){
            list.push(id);
        }
    });
    list = list.join(',');
    $('#attrStr').val(list);
});
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>


