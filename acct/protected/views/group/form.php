<?php
$this->pageTitle=Yii::app()->name . ' - Template Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'template-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('group','Template Form'); ?></strong>
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
			if ($model->scenario!='new' && $model->scenario!='view') {
				echo TbHtml::button('<span class="fa fa-file-o"></span> '.Yii::t('misc','Add Another'), array(
					'submit'=>Yii::app()->createUrl('group/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('group/index'))); 
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('group/save'))); 
			?>
<?php endif ?>
<?php if ($model->scenario=='edit'): ?>
	<?php echo TbHtml::button('<span class="fa fa-remove"></span> '.Yii::t('misc','Delete'), array(
			'name'=>'btnDelete','id'=>'btnDelete','data-toggle'=>'modal','data-target'=>'#removedialog',)
		);
	?>
<?php endif ?>
	</div>
	</div></div>

	<div class="box box-info">
		<div class="box-body">
			<?php echo $form->hiddenField($model, 'scenario'); ?>
			<?php echo $form->hiddenField($model, 'temp_id'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'temp_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
					<?php echo $form->textField($model, 'temp_name', 
						array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))
					); ?>
				</div>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'system_id',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-4">
					<?php 
						$option = General::getInstalledSystemList();
						if (empty($model->system_id)) $model->system_id = key($option);
						echo $form->dropDownList($model, 'system_id', $option,
							array('disabled'=>($model->scenario!='new'))
						);
					?>
				</div>
			</div>

<?php
	$idx = 0;
	foreach($model->installedSystem() as $sid=>$sname) {
		$style = ($sid==$model->system_id) ? "" : "style='display:none'";
		echo "<div id='reg_$sid' $style>";
		foreach($model->installedSystemGroup($sid) as $gname) {
			$groupname = ($gname=='zzcontrol') ? Yii::t('app','Misc') : $model->functionLabels($gname);
			echo "<legend>".$groupname."</legend>";
			$cnt = 0;
			$out = '';
			foreach($model->installedSystemItems($sid, $gname) as $fid=>$fname) {
				$fieldid = get_class($model).'_rights_'.$idx.'_'.$fid;
				$fieldname = get_class($model).'[rights]['.$idx.']['.$fid.']';
				$fieldvalue = $model->rights[$idx][$fid];

				if ($cnt==0) echo "<div class='form-group'>";

				echo "<div class='col-sm-2'>";
				echo TbHtml::label($fname, $fieldid);
				echo "</div>";
				echo "<div class='col-sm-2'>";
				$option = ($gname=='zzcontrol') 
						? array('CN'=>Yii::t('misc','On'),
								'NA'=>Yii::t('misc','Off'),
							)
						: ((strpos($fid,'B')===false) 
						? array('RW'=>Yii::t('misc','Read-Write'),
								'RO'=>Yii::t('misc','Read-only'),
								'NA'=>Yii::t('misc','Off'),
							)
						: array('RW'=>Yii::t('misc','On'),
								'NA'=>Yii::t('misc','Off'),
							));
				echo TbHtml::dropDownList($fieldname, $fieldvalue, $option,
								array('disabled'=>($model->scenario=='view')));
				echo "</div>";
				$cnt++;

				if ($cnt==3) {
					echo "</div>";
					$cnt = 0;
				}
			}
			if ($cnt!=0) echo '</div>';
			$cnt = 0;
		}
		echo "</div>";
		$idx++;
	}
?>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
$js = "
$('#GroupForm_system_id').on('change', function() {
	$('[id^=\"reg_\"]').hide();
	var sys = $(this).val();
	$('#reg_'+sys).show();
});
";
Yii::app()->clientScript->registerScript('changeSystem',$js,CClientScript::POS_READY);

$js = Script::genDeleteData(Yii::app()->createUrl('group/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

