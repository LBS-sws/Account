<?php
$this->pageTitle=Yii::app()->name . ' - query Report';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'tc-list',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_INLINE,
    'action'=>Yii::app()->createUrl('IDCommission/amend',array('index'=>$model->id,'type'=>$this->type,'year'=>$model->year,'month'=>$model->month)),
)); ?>

<section class="content-header">
    <h1>
        <strong>
            <?php
            if($this->type == 1){
                echo "ID ".Yii::t('app','Sales Edit Commission');
            }else{
                echo "ID ".Yii::t('app','Sales Edit Query');
            }
            ?>
        </strong>
    </h1>
    <!--
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="#">Layout</a></li>
            <li class="active">Top Navigation</li>
        </ol>
    -->
</section>

<?php if ($this->type==1&&$this->allowEditDate()): ?>
    <div class="box">
        <div class="box-body">
            <div class="btn-group" role="group">
                <?php echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save1'), array(
                        'submit'=>Yii::app()->createUrl('iDCommission/amendSave',array('index'=>$model->id,'type'=>$this->type,'year'=>$model->year,'month'=>$model->month)))
                ); ?>
            </div>
        </div>
    </div>
<?php endif ?>

<section class="content" >
    <div class="box">
        <div id="yw0" class="tabbable">
            <ul class="nav nav-tabs" role="menu">
                <li>
                    <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('iDCommission/view',array('index'=>$model->id,'type'=>$this->type,'year'=>$model->year,'month'=>$model->month));?>" ><?php echo Yii::t('commission','ALL'); ?></a>
                </li>
                <li>
                    <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('iDCommission/new',array('index'=>$model->id,'type'=>$this->type,'year'=>$model->year,'month'=>$model->month));?>" ><?php echo Yii::t('commission','New'); ?></a>
                </li>
                <li class="active">
                    <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('iDCommission/amend',array('index'=>$model->id,'type'=>$this->type,'year'=>$model->year,'month'=>$model->month));?>" ><?php echo Yii::t('commission','Edit'); ?></a>
                </li>
                <li  class="">
                    <a  tabindex="-1" href="<?php echo Yii::app()->createUrl('iDCommission/renew',array('index'=>$model->id,'type'=>$this->type,'year'=>$model->year,'month'=>$model->month));?>" ><?php echo Yii::t('commission','Renewal'); ?></a>
                </li>
            </ul>
            <div class="box-info" style="height: 1000px;" >
                <div class="box-body" >
                    <?php
                    $this->widget('ext.layout.ListPageWidget', array(
                        'title'=>Yii::t('app','sale commission'),
                        'model'=>$model,
                        'viewhdr'=>'//iDCommission/s_listhdr',
                        'viewdtl'=>'//iDCommission/s_listdtl',
                        'gridsize'=>'24',
                        'height'=>'600',
                        'search'=>array(),
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
echo TbHtml::hiddenField("IDCommissionBox[id]",$model->id);
echo $form->hiddenField($model,'pageNum');
echo $form->hiddenField($model,'totalRow');
echo $form->hiddenField($model,'orderField');
echo $form->hiddenField($model,'orderType');
?>
<?php $this->endWidget(); ?>

<?php
$js = <<<EOF

$(document).ready(function(){ 
    $("#chkboxAll").on('click',function() {
        if($(this).is(':checked')){
            $('.checkBlock').prop('checked',true);
        }else{
            $('.checkBlock').prop('checked',false);
        }
    });
});
EOF;
Yii::app()->clientScript->registerScript('starClick',$js,CClientScript::POS_HEAD);
$js = Script::genTableRowClick();
Yii::app()->clientScript->registerScript('rowClick',$js,CClientScript::POS_READY);
?>

