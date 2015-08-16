# yii2-config

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist bupy7/yii2-config "*"
```

or add

```
"bupy7/yii2-config": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Run migration:

```
./yii migrate/up --migrationPath=@bupy7/config/migrations 
```

Added to console application config:

```php
'controllerMap' => [
    ...

    'config' => [
        'class' => 'bupy7\config\commands\ManagerController',
    ],

    ...
],
```

Added to main application config:

```php
'bootstrap' => [
    ...

    'config',

    ...
],
'modules' => [
    'config' => [
        'class' => 'bupy7\config\Module',
        'enableCaching' => !YII_DEBUG,
        'as access' => [
            'class' => AccessControl::className(),
            'rules' => [
                [
                    'allow' => true,
                    'roles' => ['admin'],
                ],
            ],
        ],
    ],
],
'components' => [
    'cfg' => [
        'class' => 'bupy7\config\components\ConfigManager',
    ],
],
```

TO BE CONTINUE ...

##License

yii2-config is released under the BSD 3-Clause License.