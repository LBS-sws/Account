<?php
$this->pageTitle=Yii::app()->name . ' - PlaneAllot';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'planeAllot-list',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>
<style>
    .pt-7{ padding-top: 7px;text-align: right;}
</style>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('app','Plane Allot'); ?></strong>
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
		<?php 
			if (Yii::app()->user->validRWFunction('PS02'))
				echo TbHtml::button(Yii::t('plane','More Allot'), array(
					'submit'=>Yii::app()->createUrl('planeAllot/allotMore'),
				)); 
		?>
	</div>
	</div></div>
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
        'viewhdr'=>'//planeAllot/_listhdr',
        'viewdtl'=>'//planeAllot/_listdtl',
        'gridsize'=>'24',
        'height'=>'600',
        'search_add_html'=>$search_add_html,
        'search'=>array(
            'code',
            'name',
            'department',
            'position',
        ),
    ));
    //echo TbHtml::button("test",array("submit"=>"#","class"=>"hide"));
	?>
</section>
<?php
	echo $form->hiddenField($model,'pageNum');
	echo $form->hiddenField($model,'totalRow');
	echo $form->hiddenField($model,'orderField');
	echo $form->hiddenField($model,'orderType');
?>
<!-- Modal -->
<div class="modal fade" id="allotModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo Yii::t("plane","Allot Plane Reward");?></h4>
            </div>
            <div class="modal-body">
                <?php echo TbHtml::hiddenField("allotOne[id]",'',array('id'=>"allotOneId")); ?>
                <div class="form-group" style="width: 100%;margin-bottom: 15px;">
                    <?php echo TbHtml::label(Yii::t("plane","employee name"),'',array('class'=>"col-lg-3 control-label pt-7")); ?>
                    <div class="col-lg-6">
                        <p class="form-control-static" id="allotOneName"></p>
                    </div>
                </div>
                <div class="form-group" style="width: 100%;margin-bottom: 15px;">
                    <?php echo TbHtml::label(Yii::t("plane","Job Leave"),'',array('class'=>"col-lg-3 control-label pt-7")); ?>
                    <div class="col-lg-6">
                        <?php
                        echo "<select id='allotOneJob' name='allotOne[job_id]' class='form-control' style='width: 100%'>";
                        if(!empty($model->jobList)){
                            foreach ($model->jobList as $row){
                                echo "<option value='{$row['id']}'>{$row['name']}</option>";
                            }
                        }
                        echo "</select>"
                        ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo Yii::t('dialog','Close');?></button>
                <button type="button" class="btn btn-primary" id="allotOneBtn"><?php echo Yii::t('plane','Allot');?></button>
            </div>
        </div>
    </div>
</div>

<?php $this->endWidget(); ?>

<?php
$allotOneUrl = Yii::app()->createUrl('planeAllot/allotOne');
$js = "
$('.submitBtn').change(function(){
    $('form:first').submit();
});

$('.allot_btn').click(function(){
    var id = $(this).data('id');
    var name = $(this).closest('tr').children('td.name').text();
    $('#allotOneId').val(id);
    $('#allotOneName').text(name);
    $('#allotModal').modal('show');
});

$('#allotOneBtn').click(function(){
    jQuery.yii.submitForm(this,'{$allotOneUrl}',{});
    return false;
});
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);
	$js = Script::genTableRowClick();
	Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>
