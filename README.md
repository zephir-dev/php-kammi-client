

# PHP Kammi Client

## Technos utilisées
Ci-dessous, les technos essentielles utilisées dans le projet.
* [Composer](https://getcomposer.org/)
* [PHP 7.1](http://php.net/manual/fr/migration71.php)
* [Cmder](http://cmder.net/)
* [Guzzle](http://docs.guzzlephp.org/en/stable/)

## Installation Locale
### Pré-requis

* Installer [WAMP](http://www.wampserver.com/)
	Avant de télécharger, **ne pas cliquer comme un bourrin** et lire les pré-requis pour le téléchargement (en bas dans la popup sur le site WAMP).
	Les télécharger et les installer. Si l'install ne marche pas, installer [tous les packages pré-requis](http://wampserver.aviatechno.net/?lang=fr&prerequis=afficher).

* Installer  [Composer](https://getcomposer.org/)
	Lors de l'installation, **ne pas cliquer comme un bourrin** et sélectionner **le même exécutable PHP que WAMP utilise (recommandé)**; ou alors une version **au moins égale à 7.1**

* Télécharger [Cmder](http://cmder.net/) en version "Full".
	Extraire l'archive, et lancer l'exécutable en tant qu'administrateur.
	Cmder est un émulateur de ligne de commande qui vous permettra d'utiliser les commandes bash et git facilement sous Windows.

### Récupération du projet
* Cloner le repo dans n'importe quel répertoire
```bash
$ cd /my/projects
$ git clone https://github.com/zephir-dev/php-kammi-client.git
```

### Vérification des versions et des composants

```bash
$ php -v # Doit être supérieur ou égal à 7.1
$ composer --version # Doit être supérieur ou égal à 1.7
$ php --ini # Bien noter le chemin du "Loaded Configuration File"
```

### Installation des certificats pour cURL en HTTPS via localhost
Le chemin du **"Loaded Configuration File"**  noté dans la section précédente correspond au php.ini utilisé par votre PHP CLI.

Ouvrez ce fichier (ex: C:\Program Files\php-7.2.9\php.ini), et ajouter à la fin ces deux lignes, en pensant à bien mettre à jour le chemin vers votre projet :
```bash
curl.cainfo="C:\my\projects\php-kammi-client\cacert.pem"
openssl.cafile="C:\my\projects\php-kammi-client\cacert.pem"
```
Le fichier de certificat est déjà présent et versionné dans le repo : [cacert.pem](cacert.pem).

### Installation des dépendances
```bash
$ cd /my/projects/php-kammi-client
$ composer install
```

### Lancement du serveur
Vous pouvez utiliser le serveur PHP embarqué afin de faire tourner le client, en local.
```bash
$ cd /my/projects/php-kammi-client
$ php -S localhost:8080
```
### Example de requête en local
Voici comment requêter en local
 ```php
<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

use KammiApiClient\ApiClientFactory;
require_once('./vendor/autoload.php');

$data = array();

// Plante si pas initialisé. Sans paramètres, tape par défaut sur le couple api-dev/z-dev
ApiClientFactory::init([
	'host' => 'localhost',
	'X-Client-Url' => 'api-dev.zephir.pro'
]);

// Renvoie un client
$c = ApiClientFactory::fromLogin('damien.parbhakar', 'zephir');

// $data = $c->getInstanceParams();
$data = $c->get('/v1/admin/instance/params')->getBody()->getContents();

var_dump($data);
```

### Example de requête sur Zephir
 ```php
<?php
Use KammiApiClient\ApiClientFactory;

ApiClientFactory::init(array(
	'host' => $_SESSION['api2']['host'], # Correspond au serveur sur lequel on requête
	'X-Client-Url' => $_SESSION['api2']['X-Client-Url'] # Correspond à la BDD utilisée
));

try {
	$c = ApiClientFactory::fromToken($_SESSION['api2']['token']);
	$absences = json_decode($c->get('/v1/users/me/absences')->getBody()->getContents(),true);
} catch (\Exception $e) {
	// Gestion des erreurs ici
}

$params = $c->getInstanceParams(array(
	'google_calendar_sync_active',
	'google_calendar_sync_full_day_start',
	'google_calendar_sync_full_day_end',
	'google_calendar_sync_morning_start',
	'google_calendar_sync_morning_end',
	'google_calendar_sync_afternoon_start',
	'google_calendar_sync_afternoon_end',
));

var_dump($absences,$params);

```
