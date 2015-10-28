<?php

$localParamsFile = __DIR__ . '/params-local.php';
$params = array_merge(
    require(__DIR__ . '/params.php'),
    is_file($localParamsFile) ? require($localParamsFile) : []
);

return [
    'id' => 'test-app',
    'basePath' => dirname(__DIR__),
    'components' => [
        'braintree' => [
            'class' => 'tuyakhov\braintree\Braintree',
            'merchantId' => 'gbz2n5pjhctjsh8x',
            'publicKey' => 'xqcj5tzd4rrrmdt7',
            'privateKey' => 'd12cf2acdd73a23d20bc71ed2235c43e',
        ],
    ],
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'params' => $params,
];
