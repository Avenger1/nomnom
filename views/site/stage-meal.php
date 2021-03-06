<?php

use app\models\Order;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ListView;

$this->title = 'NomNom';

$this->registerJs(<<<JS
function getTimeRemaining(endtime) {
    var t = Date.parse(endtime) - Date.parse(new Date());
    var seconds = t < 0 ? 0 : Math.floor((t / 1000) % 60);
    var minutes = t < 0 ? 0 : Math.floor((t / 1000 / 60) % 60);
    var hours = t < 0 ? 0 : Math.floor((t / (1000 * 60 * 60)) % 24);
    return {
        'total': t,
        'hours': hours,
        'minutes': minutes,
        'seconds': seconds
    };
}
function initializeClock(id, endtime) {
    var clock = document.getElementById(id);
    var hoursSpan = clock.querySelector('.hours');
    var minutesSpan = clock.querySelector('.minutes');
    var secondsSpan = clock.querySelector('.seconds');
        
    function updateClock() {
        var t = getTimeRemaining(endtime);

        hoursSpan.innerHTML = ('0' + t.hours).slice(-2);
        minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
        secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);

        if (t.total <= 0) {
            clearInterval(timeinterval);
        }
    }

    updateClock();
    var timeinterval = setInterval(updateClock, 1000);
}

var deadline = new Date({$order->stage_end} * 1000);
initializeClock('clockdiv', deadline);
JS
);

/* @var $order Order */
?>
<?= $this->render('/menu/user') ?>
<?= $this->render('/menu/admin') ?>

<div class="row">
    <div class="col-lg-12">
        <p class="pull-right">Zamówienie otworzył <span class="label label-info"><?= Html::encode($order->admin->username) ?></span></p>
        <h1>Zamówienie na dzień <?= date('Y/m/d') ?></h1>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="alert alert-warning">
            <strong>Zamknięcie wybierania za</strong><br>
            <div class="text-center">
                <div id="clockdiv">
                    <div>
                        <span class="hours"></span>
                        <div class="smalltext">Godziny</div>
                    </div>
                    <div>
                        <span class="minutes"></span>
                        <div class="smalltext">Minuty</div>
                    </div>
                    <div>
                        <span class="seconds"></span>
                        <div class="smalltext">Sekundy</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="form-group">
            <h3>Zamawiamy z</h3>
            <h3>
                <strong>1. <?= Html::encode($order->restaurant->name) ?></strong>: 
                <?php if (!empty($order->restaurant->url)): ?>
                <?= Html::a('LINK DO MENU', $order->restaurant->url, ['class' => 'btn btn-danger', 'target' => 'restaurant']) ?>
                <?php endif ?>
                <?php if (!empty($order->restaurant->screen)): ?>
                <?= Html::a('ZDJĘCIE MENU', '/uploads/menu/' . $order->restaurant->screen, ['class' => 'btn btn-danger', 'target' => 'menu']) ?>
                <?php endif ?>
                <small>Max <?= $order->restaurant->max ?> restauracj<?= $order->restaurant->max == 1 ? 'a' : 'e' ?> z tego miejsca</small>
            </h3>
            <?php if (!empty($order->restaurant2)): ?>
            <h3>
                <strong>2. <?= Html::encode($order->restaurant2->name) ?></strong>: 
                <?php if (!empty($order->restaurant2->url)): ?>
                <?= Html::a('LINK DO MENU', $order->restaurant2->url, ['class' => 'btn btn-danger', 'target' => 'restaurant2']) ?>
                <?php endif ?>
                <?php if (!empty($order->restaurant2->screen)): ?>
                <?= Html::a('ZDJĘCIE MENU', '/uploads/menu/' . $order->restaurant2->screen, ['class' => 'btn btn-danger', 'target' => 'menu2']) ?>
                <?php endif ?>
                <small>Max <?= $order->restaurant2->max ?> restauracj<?= $order->restaurant2->max == 1 ? 'a' : 'e' ?> z tego miejsca</small>
            </h3>
            <?php endif ?>
            <hr>
        </div>
    </div>
</div>
<?php if (!$ordered): ?>
<?php $form = ActiveForm::begin(); ?>
<div class="row">
    <div class="col-lg-12">
        <?php if (!empty($order->restaurant2)): ?>
        <?= $form->field($model, 'restaurant')->radioList([
            $order->restaurant->id => $order->restaurant->name,
            $order->restaurant2->id => $order->restaurant2->name,
        ]) ?>
        <?php else: ?>
        <?= Html::activeHiddenInput($model, 'restaurant') ?>
        <?php endif ?>
        <?= $form->field($model, 'code')->textInput(['autofocus' => true]) ?>
        <?= $form->field($model, 'screen')->fileInput() ?>
    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <div class="form-group">
            <?= Html::submitButton('Zamawiam', ['class' => 'btn btn-success btn-lg']) ?>
        </div>
    </div>
</div>
<?php ActiveForm::end(); ?>
<?php else: ?>
<div class="row">
    <div class="col-lg-12">
        <div class="alert alert-success">
            <a href="<?= Url::to(['site/unorder', 'order' => $order->id]) ?>" class="btn btn-danger btn-lg pull-right" data-confirm="Czy na pewno chcesz usunąć zamówienie?">Usuń zamówienie</a>
            <strong>Moje zamówienie</strong>:<br><br>
            <strong>Restauracja</strong>: <?= Html::encode($ordered->restaurant->name) ?>
            <?php if (!empty($ordered->code)): ?>
            <div class="well well-sm"><?= Html::encode($ordered->code) ?></div>
            <?php endif ?>
            <?php if (!empty($ordered->screen)): ?>
            <?= Html::img('/uploads/' . $ordered->author_id . '/' . $ordered->screen, ['class' => 'img-thumbnail img-responsive']) ?>
            <?php endif ?>
        </div>
    </div>
</div>
<?php endif ?>
<?= ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => 'ordered',
    'viewParams' => ['ordered' => !empty($ordered)]
]) ?>