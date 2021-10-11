<?php
$this->pageTitle=Yii::app()->name . ' - Report';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'report-form',
'action'=>Yii::app()->createUrl('query/index_s'),
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>

</style>

<section class="content-header">
	<h1>
		<strong>
            <?php
            if($this->type == 1){
                echo "ID ".Yii::t('report','Sales Commission');
            }else{
                echo "ID ".Yii::t('report','Sales Query');
            }
            ?>
        </strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php echo TbHtml::button(Yii::t('misc','Submit'), array(
				'submit'=>Yii::app()->createUrl('/IDCommission/index_s',array("type"=>$this->type))));
		?>
	</div>
	</div></div>
	<div class="box box-info">
		<div class="box-body">
            <div class="form-group">
                <?php echo $form->labelEx($model,'city',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php
                    $list = General::getCityListWithNoDescendant(Yii::app()->user->city_allow());
                    echo $form->dropDownList($model,"city", $list,array());
                    ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'year',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->textField($model, 'year',
                        array());
                    ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'month',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->textField($model, 'month',
                        array());
                    ?>
                </div>
            </div>


		</div>
	</div>
</section>

<?php $this->endWidget(); ?>

</div><!-- form -->

