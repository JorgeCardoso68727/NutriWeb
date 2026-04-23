<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'NutriWeb',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => '5-qc8hphFgCL5k6ld474D4THacEzE4BS',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'class' => 'amnah\yii2\user\components\User',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => \yii\symfonymailer\Mailer::class,
            'viewPath' => '@app/mail',
            'useFileTransport' => false, // ATIVADO PARA ENVIAR EMAILS REAIS
            'messageConfig' => [
                'from' => [$params['senderEmail'] => $params['senderName']],
            ],
            'transport' => [
                'scheme' => 'smtp',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'username' => 'nutriweb.support@gmail.com', // MUDE PARA SEU EMAIL
                'password' => 'glti dtah tsrm efuz', // MUDE PARA A APP PASSWORD DO GOOGLE
                'encryption' => 'tls',
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,

        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                '' => 'user/default/index',
                '/perfil' => 'profile/perfil',
                '/editar-perfil' => 'profile/editar-perfil',
                '/toggle-follow/<username:[A-Za-z0-9_\-\.]+>' => 'profile/toggle-follow',
                '/criar-plano' => 'plan/criar-plano',
                '/criar-plano-semanal' => 'plan/criar-plano-semanal',
                '/plano/<id:\d+>' => 'plan/ver-plano',
                '/plano/<id:\d+>/eliminar' => 'plan/delete',
                '/public-profile' => 'profile/public-profile',
                '/public-profile/<username:[A-Za-z0-9_\-\.]+>' => 'profile/public-profile',

                '/inicio' => 'homepage/inicio',
                '/homepage/inicio' => 'homepage/inicio',
                '/toggle-like/<postId:\d+>' => 'homepage/toggle-like',
                '/post-aberto' => 'homepage/post-aberto',
                '/post-aberto/<id:\d+>' => 'homepage/post-aberto',
                '/remove-post/<id:\d+>' => 'homepage/remove-post',
                '/feed' => 'homepage/feed',
                '/mensagens' => 'mensagens/mensagens',
                '/gotinha' => 'gotinha/index',
                '/criarpost' => 'homepage/criarpost',
                '/procurar' => 'procurar/index',

                '/badge' => 'badge/badge',
                '/dashboard' => 'reports/dashboard',
                '/reports-contas' => 'reports/reports-accounts',
                '/reports-conteudo' => 'reports/reports-content',
                '/reports-conteudo/revisto/<id:\d+>' => 'reports/mark-post-report-reviewed',
                '/moderar-conta/<id:\d+>/<acao:(banir|nao-banir)>' => 'reports/moderate-account',
                '/badge-review/<id:\d+>/<acao:(aprovar|rejeitar)>' => 'badge/badge-review',
                '/reportar' => 'reports/create',

                '<username:(?!user$|site$|debug$|gii$|assets$|perfil$|editar-perfil$|toggle-follow$|criar-plano$|criar-plano-semanal$|plano$|public-profile$|inicio$|homepage$|toggle-like$|post-aberto$|remove-post$|feed$|mensagens$|gotinha$|criarpost$|procurar$|badge$|badge-review$|dashboard$|reports-contas$|reports-conteudo$|reportar$|moderar-conta$)[A-Za-z0-9_\-\.]+>' => 'profile/public-profile',
            ],
        ],
        'view' => [
            'theme' => [
                'pathMap' => [
                    '@vendor/amnah/yii2-user/views' => '@app/views/user', // example: @app/views/user/default/login.php
                ],
            ],
        ],

    ],
    'modules' => [
        'user' => [
            'class' => 'amnah\yii2\user\Module',
            'emailViewPath' => '@app/mail',
            //Override o controller default para usar o nosso controlador personalizado
            'controllerMap' => [
                'default' => 'app\controllers\MyDefaultController',
            ],
            //Override no modelClasses para usar o nsso modelo com base no alias.
            'modelClasses' => [
                'User' => 'amnah\yii2\user\models\User',
                'Profile' => 'app\models\Perfil',
                'ForgotForm' => 'app\models\forms\ForgotForm',
                'post' => 'app\models\Post',
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
