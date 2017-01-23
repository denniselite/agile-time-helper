<?php
/**
 * Created by PhpStorm.
 * User: denniselite
 * Date: 18.10.16
 * Time: 15:56
 *
 * @var \yii\data\ArrayDataProvider $dataProvider
 * @var float $completedTotalSP
 * @var float $reviewTotalSP
 * @var float $inDevTotalSP
 * @var float $requiredSP
 * @var int $workDaysInMonthLeft
 * @var int $workDaysInMonth
 */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Jira';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6 col-sm-12 col-xs-12">
            <h3>Plan of month:</h3>

            <div class="row">
                <div class="col-md-2 col-sm-4 col-xs-4">
                    <div class="progress" style="height: 30px">
                        <div
                                class="progress-bar progress-bar-success progress-bar-striped"
                                role="progressbar"
                                aria-valuenow="100"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                style="width: 100%;padding: 5px"
                        >
                        </div>
                    </div>
                </div>
                <div class="col-md-4" style="font-size: 16px;padding: 5px 0 0 0;">
                    - Completed
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 col-sm-4 col-xs-4">
                    <div class="progress" style="height: 30px">
                        <div
                                class="progress-bar progress-bar-info progress-bar-striped"
                                role="progressbar"
                                aria-valuenow="100"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                style="width: 100%;padding: 5px"
                        >
                        </div>
                    </div>
                </div>
                <div class="col-md-4" style="font-size: 16px;padding: 5px 0 0 0;">
                    - Review / Test
                </div>
            </div>

            <div class="row">
                <div class="col-md-2 col-sm-4 col-xs-4">
                    <div class="progress" style="height: 30px">
                        <div
                                class="progress-bar progress-bar-warning progress-bar-striped"
                                role="progressbar"
                                aria-valuenow="100"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                style="width: 100%;padding: 5px"
                        >
                        </div>
                    </div>
                </div>
                <div class="col-md-4" style="font-size: 16px;padding: 5px 0 0 0;">
                    - In development
                </div>
            </div>
        </div>
        <div class="col-md-6 col-sm-12 col-xs-12" style="text-align: right">
            <h3>Time info:</h3>

            <div class="row" style="font-size: 16px;padding: 5px 0 0 0; height: 50px;">
                <div class="col-md-12">
                    <span>Work days left: <b><?= $workDaysInMonthLeft ?></b></span>
                </div>
            </div>

            <div class="row" style="font-size: 16px;padding: 5px 0 0 0; height: 50px;">
                <div class="col-md-12">
                    <span>Total work days: <b><?= $workDaysInMonth ?></b></span>
                </div>
            </div>

            <div class="row" style="font-size: 16px;padding: 5px 0 0 0; height: 50px;">
                <div class="col-md-12">
                    <span>Required estimate: <b><?= number_format(($requiredSP - $completedTotalSP) / $workDaysInMonthLeft, 2) ?></b></span>
                </div>
            </div>

        </div>
    </div>

    <div class="progress" style="height: 30px">

        <?php if ($completedTotalSP > 0) : ?>
            <div
                    class="progress-bar progress-bar-success progress-bar-striped"
                    role="progressbar"
                    aria-valuenow="<?= ($completedTotalSP / $requiredSP) * 100 ?>"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    style="min-width: 3em;width: <?= ($completedTotalSP / $requiredSP) * 100 ?>%;padding: 5px"
            >
                <?= $completedTotalSP ?> / <?= $requiredSP ?> Story points
            </div>
        <?php endif; ?>

        <?php if ($reviewTotalSP > 0) : ?>
            <div
                    class="progress-bar progress-bar-info progress-bar-striped"
                    role="progressbar"
                    aria-valuenow="<?= ($reviewTotalSP / $requiredSP) * 100 ?>"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    style="min-width: 3em;width: <?= ($reviewTotalSP / $requiredSP) * 100 ?>%;padding: 5px"
            >
                <?= $reviewTotalSP ?> SP
            </div>
        <?php endif; ?>

        <?php if ($inDevTotalSP > 0) : ?>
            <div
                    class="progress-bar progress-bar-warning progress-bar-striped"
                    role="progressbar"
                    aria-valuenow="<?= ($inDevTotalSP / $requiredSP) * 100 ?>"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    style="min-width: 3em;width: <?= ($inDevTotalSP / $requiredSP) * 100 ?>%;padding: 5px"
            >
                <?= $inDevTotalSP ?> SP
            </div>
        <?php endif; ?>

    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute' => 'key',
                'label' => 'Key',
                'format' => 'raw',
                'value' => function ($data) {
                    return '<a href="' . Yii::$app->params['jira']['host'] . Yii::$app->params['jira']['issueBrowse'] . '/' . $data['key'] . '" target="_blank">' . $data['key'] . '</a>';
                }
            ],
            [
                'label' => 'Summary',
                'value' => function ($data) {
                    return $data['fields']['summary'];
                },
            ],
            [
                'label' => 'Story points',
                'footer' => $completedTotalSP,
                'value' => function ($data) {
                    return $data['fields']['customfield_10002'];
                },
            ],
        ],
        'showFooter' => true,

        'footerRowOptions' => [
            'style' => 'font-weight:bold;text-decoration: underline;'
        ],
    ]) ?>

</div>