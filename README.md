# Formation Développeur PHP / Symfony

## Projet 6 : Snowtricks
### Introduction
#### Prérequis
*   Version minimum de PHP : 8.0
*   Git
*   Composer
### Installation
#### Copie du projet
*   git clone
#### Installation des dépendances
*   composer install --optimize-autoloader
#### Configuration
##### .env
* dev
* MAILER_DSN=smtp://user:pass@smtp.example.com:port
* MAILER_FROM="SnowTricks <contact@snowtricks.romainlouet.fr>"
* db
##### création de la bdd
    * php bin/console doctrine:database:create
##### création des tables
    * php bin/console make:migration
    * php bin/console doctrine:migrations:migrate
##### Création des données
    *php bin/console doctrine:fixtures:load
#### Utilisation
contact@snowtricks.com
admin
##### Local
prérequis: symfony installé
https://symfony.com/download
*   symfony server:start
*   localhost:8000
##### Production
*   .env : prod
*   domaine vers /public
*   Création d'un compte
*   Passer nouveau compte en admin
*   Suppression du compte contact@snowtricks.com
###### Si problème 404 en ouvrant un trick :
*   composer require symfony/apache-pack