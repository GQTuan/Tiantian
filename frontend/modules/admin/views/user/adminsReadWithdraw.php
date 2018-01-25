<?php use common\helpers\Hui; ?>
<h2 style="text-align: center"><?= $model->retail->company_name ?>，余额<?= $model->retail->total_fee ?></h2>
<?php $form = self::beginForm() ?>
<?= $form->field($model->adminAccount, 'bank_address')->textInput(['disabled' => 'disabled']) ?>
<?= $form->field($model->adminAccount, 'bank_name')->textInput(['disabled' => 'disabled']) ?>
<?= $form->field($model->adminAccount, 'bank_card')->textInput(['disabled' => 'disabled']) ?>
<?= $form->field($model->adminAccount, 'bank_user')->textInput(['disabled' => 'disabled']) ?>
<?= $form->field($model->adminAccount, 'bank_mobile')->textInput(['disabled' => 'disabled']) ?>
<?= $form->field($model, 'amount')->textInput(['disabled' => 'disabled']) ?>
<?php self::endForm() ?>
