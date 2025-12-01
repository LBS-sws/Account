<?php
$this->pageTitle=Yii::app()->name . ' - Notification Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'notice-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('queue','Notification Form'); ?></strong>
	</h1>
</section>

<section class="content">
	<div class="box"><div class="box-body">
	<div class="btn-group" role="group">
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('notice/index'))); 
		?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body no-padding">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'id'); ?>

			<div class="mailbox-read-info">
				<h3>
                    <?php
                    if(!empty($model->pc_url)){
                        echo "<a href='{$model->pc_url}'>{$model->flow_title}</a>";
                    }else{
                        echo "<b>{$model->flow_title}</b>";
                    }
                    ?>
                </h3>
				<h5><b><?php echo Yii::t('queue','Status').'</b>: '.NoticeList::getStsName($model->note_type,$model->ready_bool,$model->update_bool);?>
					<span class="mailbox-read-time pull-right"><?php echo $model->lcd; ?></span></h5>
				<h5><b><?php echo Yii::t('queue','Description').'</b>: '.$model->flow_desc; ?></h5>
			</div>
			
			<div class="mailbox-read-message with-border">
				<?php echo $model->flow_message; ?>
			</div>
		</div>
	</div>
</section>

<?php $this->endWidget(); ?>


