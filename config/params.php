<?php

$local = __DIR__ . '/params-local.php';
$params = [];
if (file_exists($local)) {
    $params = require $local;
}

return \yii\helpers\ArrayHelper::merge([
    'kanban' => [
        'estimate' => 0.6
    ]
], $params);
