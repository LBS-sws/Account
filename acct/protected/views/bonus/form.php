<?php
$this->pageTitle=Yii::app()->name . ' - Bonus Report';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
    'id'=>'Bonus-list',
    'enableClientValidation'=>true,
    'clientOptions'=>array('validateOnSubmit'=>true,),
    'layout'=>TbHtml::FORM_LAYOUT_INLINE,
)); ?>

<section class="content-header">
    <h1>
        <strong><?php echo Yii::t('app','Bonus'); ?></strong>
    </h1>
    <!--
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="#">Layout</a></li>
            <li class="active">Top Navigation</li>
        </ol>
    -->
</section>
<!--<div class="box">-->
<!--    <div class="box-body">-->
<!--        <div class="btn-group" role="group">-->

<!--            --><?php //echo TbHtml::button('<span class="fa fa-reply"></span> '.Yii::t('misc','Back'), array(
//                'submit'=>Yii::app()->createUrl('query/index_s')));
//            ?>
<!--            --><?php //echo TbHtml::button('<span class="fa fa-upload"></span> '.Yii::t('misc','Save1'), array(
//                    'submit'=>Yii::app()->createUrl('query/editsave',array('year'=>$year,'month'=>$month,'index'=>$index)))
//            ); ?>
<!--        </div>-->
<!--    </div>-->
<!--</div>-->
<section class="content" >
        <div class="box">
            <div class="box-body">
                <div class="btn-group text-info" role="group">
<!--                    <p><b>备注：</b></p>-->
                    <p style="text-indent: 15px;font-size: 25px;">奖金：<?php echo $money['money']; ?></p>
<!--                    <p style="text-indent: 15px;">2.最好按顺序进行计算提成，首先需要计算新生意额。</p>-->
                </div>
            </div>
        </div>
    <div class="box">
        <div id="yw0" class="tabbable">

            <div class="box-info" >

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
                        'title'=>Yii::t('app','Bonus List'),
                        'model'=>$model,
                        'viewhdr'=>'//bonus/t_listhdr',
                        'viewdtl'=>'//bonus/t_listdtl',
                        'gridsize'=>'24',
                        'height'=>'600',
                        'search'=>$search,
                        'hasNavBar'=>false,
                        'hasPageBar'=>false,
                        'hasSearchBar'=>false,
                    ));
                    echo TBhtml::button('dummyButtin',array('style'=>'display:none','disabled'=>true,'submit'=>'#',))
                    ?>

            </div>
        </div>
    </div>
</section>

<?php
echo $form->hiddenField($model,'pageNum');
echo $form->hiddenField($model,'totalRow');
echo $form->hiddenField($model,'orderField');
echo $form->hiddenField($model,'orderType');
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

