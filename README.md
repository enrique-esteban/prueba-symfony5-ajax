# prueba-php

## Instrucciones de instalación para servidor local

1. Instalar servidor Lamp y configurarlo para Symfony 5.4:

    [https://symfony.com/doc/4.2/reference/requirements.html](https://symfony.com/doc/4.2/reference/requirements.html)

2. Descargarte aplicación en la carpeta del servidor:

    `$ git clone git@github.com:enrique-esteban/prueba-php.git nombre-app`
    
    `$ cd nombre-app/ `
    
    `$ composer install` 

3. Configurar la base de datos en el archivo _.env_.
4. Instalar fixtures:

    `$ php bin/console doctrine:fixtures:load`
    
5. Cargar el servidor de symfony (necesario [symfony_cli](https://symfony.com/download)):

    `$ symfony server:start`
