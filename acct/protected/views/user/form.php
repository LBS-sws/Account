<?php
$this->pageTitle=Yii::app()->name . ' - User Form';
?>
<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'user-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>

<section class="content-header">
	<h1>
		<strong><?php echo Yii::t('user','User Form'); ?></strong>
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
					'submit'=>Yii::app()->createUrl('user/new')));
			}
		?>
		<?php echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
				'submit'=>Yii::app()->createUrl('user/index'))); 
		?>
<?php if ($model->scenario!='view'): ?>
			<?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save'), array(
				'submit'=>Yii::app()->createUrl('user/save'))); 
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
			<?php echo $form->hiddenField($model, 'fail_count'); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'username',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-5">
				<?php echo $form->textField($model, 'username', 
					array('size'=>15,'maxlength'=>15,'readonly'=>($model->scenario!='new'),)); 
				?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'disp_name',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-7">
				<?php echo $form->textField($model, 'disp_name', 
					array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view')));
				?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'password',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
				<?php echo $form->passwordField($model,'password',
					array('size'=>15,'maxlength'=>15,'readonly'=>($model->scenario=='view'))); 
				?>
				</div>
			</div>
			
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'city',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
				<?php echo $form->dropDownList($model, 'city', General::getCityList(),
					array('disabled'=>($model->scenario=='view'))); 
				?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'email',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-5">
				<?php echo $form->textField($model, 'email', 
					array('size'=>50,'maxlength'=>100,'readonly'=>($model->scenario=='view'))); 
				?>
				</div>
			</div>
			
			<div class="form-group">
				<?php echo $form->labelEx($model,'status',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
				<?php echo $form->dropDownList($model, 'status', 
					array('A'=>Yii::t('misc','Active'),'I'=>Yii::t('misc','Inactive')),
					array('disabled'=>($model->scenario=='view'))); 
				?>
				</div>
			</div>

<?php if (Yii::app()->params['noOfLoginRetry']>0) : ?>
			<div class="form-group">
				<?php echo $form->labelEx($model,'lock',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-2">
				<?php echo $form->textField($model, 'lock', array('size'=>5,'readonly'=>true)); ?>
				<?php 
					if ($model->scenario!='new' && $model->scenario!='view'
						&& $model->lock==Yii::t('misc','Yes')) {
						echo CHtml::Button(Yii::t('user','Unlock'), array(
							'name'=>'btnUnlock','id'=>'btnUnlock')
						);
					}
				?>
				</div>
			</div>
<?php endif; ?>		
			<div class="form-group">
				<?php echo $form->labelEx($model,'logon_time',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
				<?php echo $form->textField($model, 'logon_time', array('size'=>30,'readonly'=>true)); ?>
				</div>

				<?php echo $form->labelEx($model,'logoff_time',array('class'=>"col-sm-2 control-label")); ?>
				<div class="col-sm-3">
				<?php echo $form->textField($model, 'logoff_time', array('size'=>30,'readonly'=>true)); ?>
				</div>
			</div>

<?php
	$flag = true;
	$tabs = array();
	$idx = 0;
	foreach($model->installedSystem() as $sid=>$sname) {
		$content = TbHtml::button(Yii::t('user','Apply Template'),array('name'=>'btnTemp_'.$sid,'id'=>'btnTemp_'.$sid,'class'=>'pull-right'));
		foreach($model->installedSystemGroup($sid) as $gname) {
			$content .= "<legend>".$model->functionLabels($gname)."</legend>";
			$cnt = 0;
			$out = '';
			foreach($model->installedSystemItems($sid, $gname) as $fid=>$fname) {
				$fieldid = get_class($model).'_rights_'.$idx.'_'.$fid;
				$fieldname = get_class($model).'[rights]['.$idx.']['.$fid.']';
				$fieldvalue = $model->rights[$idx][$fid];

				if ($cnt==0) $out .= '<div class="form-group">';

				$out .= '<div class="col-sm-2">';
				$out .= TbHtml::label($model->functionLabels($fname), $fieldid);
				$out .= '</div>';
				$out .= '<div class="col-sm-2">';
				$option = (strpos($fid,'B')===false) 
						? array('RW'=>Yii::t('misc','Read-Write'),
								'RO'=>Yii::t('misc','Read-only'),
								'NA'=>Yii::t('misc','Off'),
							)
						: array('RW'=>Yii::t('misc','On'),
								'NA'=>Yii::t('misc','Off'),
							);
				$out .= TbHtml::dropDownList($fieldname, $fieldvalue, $option,
								array('disabled'=>($model->scenario=='view')));
				$out .= '</div>';
				$cnt++;

				if ($cnt==3) {
					$out .= '</div>';
					$content .= $out;
					$cnt = 0;
					$out = '';
				}
			}
			if (!empty($out)) {
				if ($cnt!=0) $out .= '</div>';
				$content .= $out;
				$cnt = 0;
			}
		}

		$tabs[] = array(
					'label'=>$sname,
					'content'=>$content,
					'active'=>$flag,
				);
		$flag = false;
		$idx++;
	}

	$this->widget('bootstrap.widgets.TbTabs', array(
		'tabs'=>$tabs,
	));
?>
		</div>
	</div>
</section>

<?php $this->renderPartial('//site/removedialog'); ?>

<?php
	$list = TbHtml::listBox('lsttemplate', '', array(), array(
				'size'=>'15')
			);

	$content = "
<div class=\"row\">
	<div class=\"col-sm-11\" id=\"lookup-list\">
			$list
	</div>
</div>
";

	$this->widget('bootstrap.widgets.TbModal', array(
					'id'=>'applytempdialog',
					'header'=>Yii::t('user','Apply Template'),
					'content'=>$content,
					'footer'=>array(
						TbHtml::button(Yii::t('dialog','OK'), array('id'=>'btnApply','data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
						TbHtml::button(Yii::t('dialog','Cancel'), array('data-dismiss'=>'modal','color'=>TbHtml::BUTTON_COLOR_PRIMARY)),
					),
					'show'=>false,
				));
?>

<?php
	$mesg = Yii::t('dialog','No Record Found');
	$link = Yii::app()->createAbsoluteUrl("lookup");
	$ulink = Yii::app()->createAbsoluteUrl("user");
	$a_sys = Yii::app()->params['systemMapping'];
	$ta = array();
	foreach ($a_sys as $key=>$value) {
		$ta[] = $key;
	}
	$syslist = json_encode($ta);

	$js = "
$('[id^=\"btnTemp_\"').on('click',function(){
	var btnid = $(this).attr('id');
	var sid = btnid.replace('btnTemp_','');
	var data = 'system='+sid;
	var link = '$link/template';
	$('#applytempdialog').modal('show');
	$.ajax({
		type: 'GET',
		url: link,
		data: data,
		dataType: 'json',
		success: function(data) {
			$('#lsttemplate').empty();
			$.each(data, function(index, element) {
				$('#lsttemplate').append('<option value=\"'+element.id+'\">'+element.name+'</option>');
			});
			
			var count = $('#lsttemplate').children().length;
			if (count<=0) $('#lsttemplate').append('<option value=\"-1\">$mesg</option>');
		},
		error: function(data) { // if error occured
			alert('Error occured.please try again');
		}
	});
});

$('#btnApply').on('click',function(){
	var tid = $('#lsttemplate').val();
	var data = 'id='+tid;
	var link = '$ulink/applytemplate';
	var syslst = JSON.parse('$syslist');
	$.ajax({
		type: 'GET',
		url: link,
		data: data,
		dataType: 'json',
		success: function(data) {
			$.each(data, function(index, element) {
				var fldid = 'UserForm_rights_'+element.idx+'_'+element.id;
				$('#'+fldid).val(element.value);
			});
		},
		error: function(data) { // if error occured
			alert('Error occured.please try again');
		}
	});
});
	";
Yii::app()->clientScript->registerScript('lookupTemplate',$js,CClientScript::POS_READY);


$js = Script::genDeleteData(Yii::app()->createUrl('user/delete'));
Yii::app()->clientScript->registerScript('deleteRecord',$js,CClientScript::POS_READY);

if (Yii::app()->params['noOfLoginRetry']>0 && $model->lock==Yii::t('misc','Yes')) {
	$js = "
$('#btnUnlock').on('click',function() {
	$(\"[id*='fail_count']\").attr('value','0');
	var elm=$('#btnUnlock');
	jQuery.yii.submitForm(elm,'".Yii::app()->createUrl('user/save')."',{});
});
	";
}
Yii::app()->clientScript->registerScript('unlockRecord',$js,CClientScript::POS_READY);

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>

