<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

if (!($loader = @include __DIR__ . '/../vendor/autoload.php')) {
    exit(<<<EOT
You need to install the project dependencies using Composer:
$ wget http://getcomposer.org/composer.phar
OR
$ curl -s https://getcomposer.org/installer | php
$ php composer.phar install --dev
$ phpunit
EOT
    );
}

require_once './Tests/SymfonyIntegration/AppKernel.php';
AnnotationRegistry::registerLoader('class_exists');
