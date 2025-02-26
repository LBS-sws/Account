<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->pageTitle=Yii::app()->name . ' - Login';
?>

<div class="login-container">
    <div class="header">
        <div class="logo">
            <?php echo '<img src="' . Yii::app()->baseUrl . '/images/login_logo.png" width="330px">';?>
        </div>
        <div class="sevice_select">
            <div class="sevice_select_bt">
                服务器：大陆
            </div>
        </div>
    </div>
    <div class="content">
        <div class="content_left">
            <div class="content_left_title">LBS <span>日常管理</span></div>
            <p class="content_left_size">为求使用史伟莎日常管理系统达至</p>
            <p class="content_left_size">最佳效果，请使用火狐浏览器</p>
            <?php echo '<img src="' . Yii::app()->baseUrl . '/images/banner.png">';?>
        </div>
        <div class="content_right">
            <h2>登录</h2>
            <?php $form=$this->beginWidget('TbActiveForm', array(
                'id'=>'login-form',
                'enableClientValidation'=>true,
                'clientOptions'=>array(
                    'validateOnSubmit'=>true,
                    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
                ),
            )); ?>

            <?php echo $form->textField($model,'username',
                array('placeholder'=>Yii::t('user','User ID'),'class'=>'login_input','id'=>'input_user'));
            ?>

            <?php echo $form->passwordField($model,'password',
                array('placeholder'=>Yii::t('user','Password'),'class'=>'login_input','id'=>'input_password'));
            ?>

            <?php echo TbHtml::submitButton(Yii::t('misc','Login'),
                array('class'=>'login_button',));
            ?>
            <?php $this->endWidget(); ?>
        </div>
        <div style="clear: both;"></div>
    </div>
</div>

<div class="mobile-login-container">
    <div class="mobile-header">
        <div class="mobile-logo">
            <?php echo '<img src="' . Yii::app()->baseUrl . '/images/login_logo.png" width="180px">';?>
        </div>
        <div class="mobile-sevice_select">
            <div class="mobile-sevice_select_bt">
                服务器：大陆
            </div>
        </div>
    </div>
    <div class="mobile-content">
        <div>
            <div class="mobile-content_left_title">LBS <span>日常管理</span></div>
            <p class="mobile-content_left_size">为求使用史伟莎日常管理系统达至</p>
            <p class="mobile-content_left_size">最佳效果，请使用火狐浏览器</p>
        </div>
        <div style="clear: both;"></div>
        <div class="mobile-content_right">
            <div class="mobile-login-title">登录</div>
            <?php $form=$this->beginWidget('TbActiveForm', array(
                'id'=>'login-form',
                'enableClientValidation'=>true,
                'clientOptions'=>array(
                    'validateOnSubmit'=>true,
                    'layout'=>TbHtml::FORM_LAYOUT_HORIZONTAL,
                ),
            )); ?>

            <?php echo $form->textField($model,'username',
                array('placeholder'=>Yii::t('user','User ID'),'class'=>'mobile-login_input','id'=>'input_user'));
            ?>

            <?php echo $form->passwordField($model,'password',
                array('placeholder'=>Yii::t('user','Password'),'class'=>'mobile-login_input','id'=>'input_password'));
            ?>

            <?php echo TbHtml::submitButton(Yii::t('misc','Login'),
                array('class'=>'mobile-login_button',));
            ?>
            <?php $this->endWidget(); ?>
        </div>
    </div>

</div>