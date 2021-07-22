# Formation Développeur PHP / Symfony

## Projet 6 : Snowtricks

### Introduction
Projet 6 de la formation **OpenClassrooms** [*Développeur d'application PHP / Symfony*](https://openclassrooms.com/fr/paths/59-developpeur-dapplication-php-symfony) :

**Développez de A à Z le site communautaire SnowTricks**

Vous pouvez voir la démo du projet [ici](https://snowtricks.romainlouet.fr/)

### Installation

#### Prérequis
*   Version minimum de PHP : 8.0
*   Git
*   Composer

#### Copie du projet
`git clone https://github.com/RLouet/Formation-OC-P6.git`

#### Installation des dépendances
`composer install --optimize-autoloader`

#### Configuration

##### .env
Modifier le fichier .env avec vos informations, et passer le projet en dev.
*   Application en dev
    
    `APP_ENV=dev`
    
*   Configuration du mail
    *   Email d'envoi
      
        `MAILER_DSN=smtp://user:password@smtp.example.com:port`
        > Utiliser l'encodage pourcent :
        > 
        > > https://fr.wikipedia.org/wiki/Encodage-pourcent#Caract.C3.A8res_r.C3.A9serv.C3.A9s_dans_l.27encodage-pourcent
    *   Email affiché comme expéditeur (devrait correspondre au mail d'envoi)
      
        `MAILER_FROM="SnowTricks <contact@snowtricks.com>"`

*   Configration de la base de données
  
    `DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"`

##### Création de la bdd
`php bin/console doctrine:database:create`

*(Ou création de la base manuellement)*

##### Création des tables
`php bin/console make:migration`

`php bin/console doctrine:migrations:migrate`

##### Création des données
`php bin/console doctrine:fixtures:load`

#### Utilisation
Par défaut, le compte administrateur est le suivant :

> Email : contact@snowtricks.com
> 
> Mot de passe : admin

**Ce compte est destiné à une utilisation en local (l'adresse email n'est pas valide et le mot de passe non conforme à une utilisation en production)**

##### Utilisation en local
*Prérequis : symfony installé*
> https://symfony.com/download
*   Démarrer le serveur local :
    
`symfony server:start`
*   Le site est accessible à l'adresse <localhost:8000>

##### Utilisation en production
*   Modifier l'environnement de l'application dans le .env :

    `APP_ENV=dev`

*   Améliorer les performances du .env :
    
    `composer dump-env prod`

*   Mettre à jour les dépendances pour l'envirnnement :
    
    `composer install --no-dev --optimize-autoloader`

*   Vidage du cache :
    
    `APP_ENV=prod APP_DEBUG=0 php bin/console cache:clear`

*   Configurer le domaine  pour qu'il pointe vers vers le dossier */public*

###### Modifier l’administrateur
*   **En supprimant les tricks et commentaire par défaut**
    *   Création d’un compte, validation de ce compte.
    
    *   Passer ce nouveau compte en admin avec le compte contact@snowtricks.com.
    
    *   Suppression du compte contact@snowtricks.com
        
        -> Les tricks et les commentaires par défaut seront supprimés.
*   **En gardant les tricks et commentaires par défaut**
    *   Dans la base de données, remplacer l’adresse email de l’administrateur par défaut par votre adresse valide.
    
    *   Connectez vous avec votre adresse Email et le mot de passe par défaut (*admin*)
    
    *   Cliquez sur l'avatar dans le barre menu, puis sur *profil*
    
    *   Modifiez le mot de passe

###### Si erreur 404 en ouvrant un trick
`composer require symfony/apache-pack`