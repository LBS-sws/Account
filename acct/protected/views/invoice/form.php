<?php
$this->pageTitle=Yii::app()->name . ' - Invoice Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'invoice-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('invoice','Invoice Form'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('invoice/index')));
		?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('invoice/save')));
			?>
        <?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
                'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
            );
        ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>
            <?php echo $form->hiddenField($model, 'city'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'invoice_no',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
					<?php echo $form->textField($model, 'invoice_no',
						array('size'=>10,'maxlength'=>10,'readonly'=>'readonly')
					); ?>
				</div>

                <?php echo $form->labelEx($model,'invoice_dt',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <div class="input-group date">
                        <div class="input-group-addon">
                            <i class="fa fa-calendar"></i>
                        </div>
                        <?php echo $form->textField($model, 'invoice_dt',
                            array('class'=>'form-control pull-right','readonly'=>'readonly',));
                        ?>
                    </div>
                </div>
			</div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'customer_code',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->textField($model, 'customer_code',
                        array('size'=>40,'maxlength'=>250,'readonly'=>'readonly')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'payment_term',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->textField($model, 'payment_term',
                        array('size'=>40,'maxlength'=>250,'readonly'=>'')
                    ); ?>
                </div>
            </div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'sales_name',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-7">
                    <?php echo $form->textField($model, 'sales_name',
                        array('size'=>40,'maxlength'=>250,'readonly'=>'readonly')
                    ); ?>
                </div>
			</div>
<!--
            <div class="form-group">
                <?php echo $form->labelEx($model,'invoice_to_name',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-7">
                    <?php echo $form->textField($model, 'invoice_to_name',
                        array('size'=>40,'maxlength'=>250,'readonly'=>'')
                    ); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'invoice_to_addr',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-7">
                    <?php echo $form->textField($model, 'invoice_to_addr',
                        array('size'=>40,'maxlength'=>250,'readonly'=>'')
                    ); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'invoice_to_tel',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-7">
                    <?php echo $form->textField($model, 'invoice_to_tel',
                        array('size'=>40,'maxlength'=>250,'readonly'=>'')
                    ); ?>
                </div>
            </div>
-->

            <div class="form-group">
                <?php echo $form->labelEx($model,'name_zh',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-7">
                    <?php echo $form->textField($model, 'name_zh',
                        array('size'=>40,'maxlength'=>250,'readonly'=>'')
                    ); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'addr',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-7">
                    <?php echo $form->textField($model, 'addr',
                        array('size'=>40,'maxlength'=>250,'readonly'=>'')
                    ); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'tel',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-7">
                    <?php echo $form->textField($model, 'tel',
                        array('size'=>40,'maxlength'=>250,'readonly'=>'')
                    ); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'bowl',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'bowl',
                        array('readonly'=>'')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'baf',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'baf',
                        array('readonly'=>'')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'hand',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'hand',
                        array('readonly'=>'')
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'urinal',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'urinal',
                        array('readonly'=>'')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'hsd',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'hsd',
                        array('readonly'=>'')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'td',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'td',
                        array('readonly'=>'')
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'sink',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'sink',
                        array('readonly'=>'')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'abhsd',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'abhsd',
                        array('readonly'=>'')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'ptd',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'ptd',
                        array('readonly'=>'')
                    ); ?>
                </div>
            </div>
            <div class="form-group">
                <?php echo $form->labelEx($model,'ttl',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'ttl',
                        array('readonly'=>'')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'aerosal',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'aerosal',
                        array('readonly'=>'')
                    ); ?>
                </div>
                <?php echo $form->labelEx($model,'toiletRoom',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-2">
                    <?php echo $form->numberField($model, 'toiletRoom',
                        array('readonly'=>'')
                    ); ?>
                </div>
            </div>

           <?php $i=1;foreach ($model['type'] as $value){ ?>
               <div class="type_mian">
               <div class="form-group">
                   <label class="col-sm-2 control-label" for="InvoiceForm_disc"><?php echo Yii::t('invoice','Description').$i;?></label>
                   <div class="col-sm-3">
                       <input min="0" name="InvoiceForm[type][<?php echo $i;?>][product_name]" id="InvoiceForm_description<?php echo $i;?>" class="input-40 form-control" type="text" value="<?php echo $value['product_name'];?>">
                   </div>
                   <label class="col-sm-1 control-label" for="InvoiceForm_disc"><?php echo Yii::t('invoice','Quantity');?></label>
                   <div class="col-sm-3">
                       <input min="0" name="InvoiceForm[type][<?php echo $i;?>][qty]" id="InvoiceForm_quantity<?php echo $i;?>" class="input-40 form-control qty" type="number" value="<?php echo $value['qty'];?>">
                   </div>
               </div>
               <div class="form-group">
                   <label class="col-sm-2 control-label" for="InvoiceForm_disc"><?php echo Yii::t('invoice','Unit Price');?></label>
                   <div class="col-sm-3">
                       <input min="0" name="InvoiceForm[type][<?php echo $i;?>][unit_price]" id="InvoiceForm_unit_price<?php echo $i;?>" class="input-40 form-control unit_price" type="number" value="<?php echo floatval($value['unit_price']);?>">
                   </div>
                   <label class="col-sm-1 control-label" for="InvoiceForm_disc"><?php echo Yii::t('invoice','Amount');?></label>
                   <div class="col-sm-3">
                       <input min="0" name="InvoiceForm[type][<?php echo $i;?>][amount]" id="InvoiceForm_amount<?php echo $i;?>" class="input-40 form-control amount" type="number" readonly value="<?php echo floatval($value['amount']);?>">
                   </div>
               </div>
               <input min="0" name="InvoiceForm[type][<?php echo $i;?>][id]" id="InvoiceForm_amount" class="input-40 form-control" type="number" style="display:none" value="<?php echo $value['id'];?>">
               </div>
            <?php $i=$i+1;}?>


            <div class="form-group">
                <?php echo $form->labelEx($model,'invoice_amt',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->numberField($model, 'invoice_amt',
                        array('size'=>40,'min'=>0,'readonly'=>'readonly')
                    ); ?>
                </div>
            </div>

            <div class="form-group">
                <?php echo $form->labelEx($model,'remarks',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-6">
                    <?php echo $form->textArea($model, 'remarks',
                        array('rows'=>4,'readonly'=>'')
                    ); ?>
                </div>
            </div>

            <!-- 不需要顯示舊編號
            <div class="form-group">
                <?php echo $form->labelEx($model,'old_no',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-6">
                    <?php echo $form->textField($model, 'old_no',
                        array('size'=>40,'maxlength'=>250,'readonly'=>'readonly')
                    ); ?>
                </div>
            </div>
            -->
            <div class="form-group">
                <?php echo $form->labelEx($model,'generated_by',array('class'=>"col-sm-2 control-label")); ?>
                <div class="col-sm-3">
                    <?php echo $form->textField($model, 'generated_by',
                        array('size'=>40,'maxlength'=>250,'readonly'=>'readonly')
                    ); ?>
                </div>
            </div>
		</div>
	</div>
</section>


<?php $this->renderPartial('//site/removedialog'); ?>

<?php
$js = "
function IsNumeric(n) {
  return !isNaN(parseFloat(n)) && isFinite(n);
}
";
Yii::app()->clientScript->registerScript('calcFunction',$js,CClientScript::POS_READY);

$js = Script::genDeleteData(Yii::app()->createUrl('invoice/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

if ($model->scenario!='view') {
	$js = Script::genDatePicker(array(
			'InvoiceForm_sales_order_date',
		));
	Yii::app()->clientScript->registerScript('datePick',$js,CClientScript::POS_READY);
}

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

</div><!-- form -->

