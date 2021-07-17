# Formation Développeur PHP / Symfony

## Projet 6 : Snowtricks
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
*   APP_ENV=dev
*   MAILER_DSN=smtp://user:pass@smtp.example.com:port
  *   https://fr.wikipedia.org/wiki/Encodage-pourcent#Caract.C3.A8res_r.C3.A9serv.C3.A9s_dans_l.27encodage-pourcent
*   MAILER_FROM="SnowTricks <contact@snowtricks.com>"
*   DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"
##### Création de la bdd
    * php bin/console doctrine:database:create
##### Création des tables
    * php bin/console make:migration
    * php bin/console doctrine:migrations:migrate
##### Création des données
    *php bin/console doctrine:fixtures:load
#### Utilisation
contact@snowtricks.com
admin
##### Local
Prérequis : symfony installé
https://symfony.com/download
*   symfony server:start
*   localhost:8000
##### Production
*   .env : prod
*   composer dump-env prod
*   composer install --no-dev --optimize-autoloader
*   APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear
*   domaine vers /public
*   Création d’un compte
*   Passer nouveau compte en admin
*   Suppression du compte contact@snowtricks.com
###### Si problème 404 en ouvrant un trick
*   composer require symfony/apache-pack