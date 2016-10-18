<?php
/**
 * Created by PhpStorm.
 * User: denniselite
 * Date: 18.10.16
 * Time: 15:56
 *
 * @var \yii\data\ArrayDataProvider $dataProvider
 * @var float $totalSP
 * @var float $requiredSP
 */

use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'Jira';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">

    <h1><?= Html::encode($this->title) ?></h1>

    <h3>Plan of month:</h3>

    <div class="progress" style="height: 30px">
        <div
            class="progress-bar progress-bar-info progress-bar-striped"
            role="progressbar"
            aria-valuenow="<?=($totalSP / $requiredSP) * 100?>"
            aria-valuemin="0"
            aria-valuemax="100"
            style="width: <?=($totalSP / $requiredSP) * 100?>%;padding: 5px"
        >
            <?=$totalSP?> / <?=$requiredSP?> Story points
        </div>
    </div>

    <?=GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            [
                'attribute' => 'key',
                'label' => 'Key',
                'format' => 'raw',
                'value' => function($data) {
                    return '<a href="'.Yii::$app->params['jira']['host'] . Yii::$app->params['jira']['issueBrowse'].'/'.$data['key'].'" target="_blank">' . $data['key'] . '</a>';
                }
            ],
            [
                'label' => 'Story points',
                'footer' => $totalSP,
                'value' => function($data) {
                    return $data['fields']['customfield_10002'];
                },
            ]
        ],
        'showFooter' => true,

        'footerRowOptions' => [
            'style' => 'font-weight:bold;text-decoration: underline;'
        ],
    ])?>

</div>