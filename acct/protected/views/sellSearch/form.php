<?php
$this->pageTitle=Yii::app()->name . ' - SellCompute Form';
?>

<?php $form=$this->beginWidget('TbActiveForm', array(
'id'=>'SellCompute-form',
'enableClientValidation'=>true,
'clientOptions'=>array('validateOnSubmit'=>true,),
'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
)); ?>
<style>
    .form-group{ margin-bottom: 0px;}
</style>

<section class="content-header">
	<h1>
        <strong><?php echo Yii::t('report','Sales Commission All'); ?></strong>
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
				'submit'=>Yii::app()->createUrl('sellSearch/index')));
		?>
	</div>
	</div></div>

    <div class="box">
        <div class="box-body">
            <div class="btn-group text-info" role="group">
                <p><b>注：</b></p>
                <p style="text-indent: 15px;"><?php echo Yii::t('commission','Zhu1'); ?></p>
                <p style="text-indent: 15px;"><?php echo Yii::t('commission','Zhu2'); ?></p>
                <p style="text-indent: 15px;"><?php echo Yii::t('commission','Zhu3'); ?></p>
            </div>
        </div>
    </div>

    <div class="box">
        <div id="yw0" class="tabbable">
            <?php
            echo $model->getMenuHtml("Search");
            ?>

            <div class="box-info" >
                <?php echo $form->hiddenField($model, 'id'); ?>
                <?php echo $form->hiddenField($model, 'year'); ?>
                <?php echo $form->hiddenField($model, 'month'); ?>
                <div class="box-body" >
                    <div class="col-lg-4">
                        <div class="form-group" >
                            <label class="col-sm-5 control-label"><?php echo Yii::t('commission','city'); ?></label>
                            <div class="col-sm-7" >
                                <?php echo $form->textField($model, 'city_name',
                                    array('size'=>50,'maxlength'=>100,'readonly'=>(true))
                                ); ?>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="col-sm-5 control-label"><?php echo Yii::t('commission','employee_name'); ?></label>
                            <div class="col-sm-7" >
                                <?php echo $form->textField($model, 'employee_name',
                                    array('size'=>50,'maxlength'=>100,'readonly'=>(true))
                                ); ?>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="col-sm-5 control-label"><?php echo Yii::t('commission','group_type'); ?></label>
                            <div class="col-sm-7">
                                <?php echo TbHtml::textField("group_type",SellComputeForm::getGroupName($model->group_type),array('readonly'=>true));?>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="col-sm-5 control-label"><?php echo Yii::t('commission','saleyear'); ?></label>
                            <div class="col-sm-7">
                                <?php echo TbHtml::textField("year",$model->year."/".$model->month,array('readonly'=>true));?>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="col-sm-5 control-label"><?php echo Yii::t('commission','new_calc'); ?></label>
                            <div class="col-sm-7">
                                <?php echo TbHtml::textField("new_calc",SellComputeList::showText($model->dtl_list['new_calc'],$model->showNull,"rate"),array('readonly'=>true));?>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="col-sm-5 control-label"><?php echo Yii::t('commission','performance'); ?></label>
                            <div class="col-sm-7">
                                <?php echo TbHtml::textField("performance",$model->performance,array('readonly'=>true));?>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="col-sm-5 control-label"><?php echo Yii::t('commission','point'); ?></label>
                            <div class="col-sm-7">
                                <?php echo TbHtml::textField("point",SellComputeList::showText($model->dtl_list['point'],$model->showNull,"rate"),array('readonly'=>true));?>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="col-sm-5 control-label"><?php echo Yii::t('commission','service reward'); ?></label>
                            <div class="col-sm-7">
                                <?php echo TbHtml::textField("service_reward",SellComputeList::showText($model->dtl_list['service_reward'],$model->showNull,"rate"),array('readonly'=>true));?>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="col-sm-5 control-label"><?php echo Yii::t('commission','span rate'); ?></label>
                            <div class="col-sm-7">
                                <?php echo TbHtml::hiddenField("span_id",$model->span_id);?>
                                <?php echo TbHtml::textField("span_rate",SellComputeList::showText($model->span_rate,$model->showNull,"rate"),array('readonly'=>true));?>
                            </div>
                        </div>
                        <div class="form-group" >
                            <label class="col-sm-5 control-label"><?php echo Yii::t('commission','span other rate'); ?></label>
                            <div class="col-sm-7">
                                <?php echo TbHtml::textField("span_other_rate",SellComputeList::showText($model->span_other_rate,$model->showNull,"rate"),array('readonly'=>true));?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <?php
                        $rowList = array("new_amount","edit_amount","end_amount","performance_amount","performanceedit_amount","performanceend_amount","renewal_amount",
                            "renewalend_amount","product_amount");
                        foreach ($rowList as $item){
                            echo '<div class="form-group" >';
                            echo '<label class="col-sm-5 control-label">';
                            echo Yii::t('commission',$item);
                            echo '</label>';
                            echo '<div class="col-sm-7">';
                            echo TbHtml::textField($item,SellComputeList::showText($model->dtl_list[$item],$model->showNull),array('readonly'=>true));
                            echo '</div>';
                            echo '</div>';
                        }
                        ?>
                        <div class="form-group" >
                            <label class="col-sm-5 control-label"><?php echo Yii::t('commission','all_amount'); ?></label>
                            <div class="col-sm-7">
                                <?php echo TbHtml::textField("all_amount",SellComputeList::showText($model->all_amount,$model->showNull),array('readonly'=>true));?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <?php
                        $rowList = array("new_money","edit_money","","out_money","performanceedit_money","",
                            "renewal_money");
                        foreach ($rowList as $item){
                            if(!empty($item)){
                                echo '<div class="form-group" >';
                                echo '<label class="col-sm-5 control-label">';
                                echo Yii::t('commission',$item);
                                echo '</label>';
                                echo '<div class="col-sm-7">';
                                echo TbHtml::textField($item,SellComputeList::showText($model->dtl_list[$item],$model->showNull),array('readonly'=>true));
                                echo '</div>';
                                echo '</div>';
                            }else{
                                echo '<div class="form-group" >';
                                echo '<label class="col-sm-5 control-label" style="height: 34px;">';
                                echo '<span>&nbsp;</span>';
                                echo '</label>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
                <div class="box-body">
                    <p>说明：</p>
                    <p>1、新增提成比例：新增业绩+更改新增业绩的结果对比服务提成阶梯</p>
                    <p>2、新生意额、跨区新增生意额使用的时间是：服务表单的“首次日期”</p>
                    <p>3、历史提成比例：离当前服务（同客户且同类型且同业务员）最近的一次服务（首次日期和服务日期都小于提成月份,不包含等于）的新增提成（续约终止生意额是续约提成）</p>
                    <p>4、续约生意额：性质的报表类型为A01则是餐饮，报表类型为B01则是非餐饮，其它报表类型不计算</p>
                    <p>5、续约提成点：</p>
                    <p>
                        <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <span>5.1、非餐饮：一线城市（广州、上海、深圳、北京）满足月金额大于等于2000、其它城市满足月金额大于等于1000 则提成点为0.01，不满足则是0</span>
                    </p>
                    <p>
                        <span>&nbsp;&nbsp;&nbsp;&nbsp;</span>
                        <span>5.2、餐饮：找到十家集团编号相同且在服务中的客户资料 则提成点为0.01，不满足则是0</span>
                    </p>
                    <p>6、续约不考虑跨区（即使有跨区员工当做没有跨区计算）</p>

                </div>
            </div>

        </div>

    </div>
</section>

<?php

$js = Script::genReadonlyField();
Yii::app()->clientScript->registerScript('readonlyClass',$js,CClientScript::POS_READY);
?>

<?php $this->endWidget(); ?>


