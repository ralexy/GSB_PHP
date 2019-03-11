
# Application web GSB

Cette application web est destinée au laboratoire Galaxy Swiss Bourdin. Elle permet à ses visiteurs médicaux de saisir leurs notes de frais et aux comptable de l'entreprise de les gérer pour les leur rembourser.

## Guide de démarrage
Ces instructions vous permettront de récupérer ce projet et de le lancer sur une machine locale de développement à des fins de tests.

### Prérequis

Vous devez posséder un serveur web et y installer

```
PHP 7 & MySQL
```

### Installation

Il suffira d'importer la base de données fournie dans le dossier sur votre machine  **/ressources/gsb_frais.sql**
Et d'éditer la configuration SQL du fichier, en précisant vos identifiants **/www/includes/class.pdogsb.inc.php**

La configuration locale la plus commune pour class.pdogsb.inc.php est :

```
'localhost' pour $serveur
'root' pour $user
'' OU 'root' pour $mdp
```
L'application web sera consultable via le dossier **/www/**, il est vivement conseillé de mettre en place un VirtualHost ou bien un .htaccess pour empêcher de remonter l'arborescence du serveur (particulièrement si celui-ci est accessible via Internet).

Quelques dépendances devront être installées via Composer (pour la génération de documentation via PHPDocumentor (https://www.phpdoc.org/ - licence MIT) et l'export de fiches en PDF via mPDF (https://mpdf.github.io/ - licence GNU).

Le fichier composer.json se trouve à la racine du projet, il suffira d'installer Composer (https://getcomposer.org/) et de se placer dans le dossier racine du projet pour enfin lancer la commande :

```
composer update
```

Ne pas oublier de donner les permissions en écriture à PHP pour le dossier d'export pdf **/www/pdf/** (chown et chmod 755 pour les systèmes basés sur unix).

Pour un serveur Apache sous Debian par exemple (il faudra adapter ces commandes à votre environnement serveur) :

```
cd /var/www/html/
find . -type d -exec chmod 0755 {} \;
find . -type f -exec chmod 0644 {} \;
sudo chown www-data:www-data -R *;
service apache2 restart;
```

## Tester l'application

Vous pouvez tester l'application avec des comptes utilisateurs ou comptable, il vous suffira d'utiliser ces couples d'identifiants pour les visiteurs :
```
Utilisateur : cbedos
Mot de passe : gmhxd

Utilisateur : ltusseau
Mot de passe : ktp3s
```
Et pour le comptable :

```
Utilisateur : comptable
Mot de passe : comptable
```

L'application est directement consultable ici : https://gsb.alexy-rousseau.com

### Documentation PHPDocumentor
La documentation PHPDoc du projet est accessible via : https://gsb.alexy-rousseau.com/documentation/

## Conçu avec

* [Composer](https://getcomposer.org/) - Gestionnaire de dépendances PHP
* [PhpDocumentor](https://www.phpdoc.org/) - Librairie permettant la génération de documentation PHP
* [mPDF](https://mpdf.github.io/) - Librairie PHP simplifiant la création et le stockage de pages HTML en PDF
* [PHPStorm](https://www.jetbrains.com/phpstorm/) IDE spécialisé pour PHP, édité par la société JetBrains également co-autrice de AndroidStudio

## Versioning

GitHub a été utilisé pour maintenir un versionning du projet.

## Auteurs

* **Réseau CERTA** - Concepteur initial - [Réseau CERTA](https://www.reseaucerta.org)
* **José GIL** - Professeur CNED - Modernisation du contexte - <jgil@ac-nice.fr>
* **Alexy ROUSSEAU** - Etudiant BTS SIO - Ajout des fonctionnalités comptables -  <contact@alexy-rousseau.com>
