#!/bin/bash
cd ./app
echo '======== Wechsle Verzeichnis nach ' $PWD ========;
echo '==========================    Composer     =========================='
composer install
composer dump-autoload
echo '==========================    NPM     =========================='
npm i
echo '==========================    DONE     =========================='