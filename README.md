# Reservation-salle Alma SCDI
**Reservation-salle Alma SCDI** est une application écrite en PHP/JS/CSS qui permet de visualiser les plannings de disponibilité et de réserver des salles de travail déclarées dans l'application Alma Exlibris.

## Fonctionnement de l'application
L'utilisateur consulte le planning des disponibilités des salles inscrites dans l'application. Il sélectionne les créneaux disponibles qu'il veut réserver.
Pour valider sa réservation, il s'authentifie avec son compte universitaire (shibboleth SAML) qui va faire le lien avec l'utilisateur dans Alma.
S'il n'y a pas de conflit (la gestion des conflits est faite par Alma au moment de la réservation via API) : la réservation est confirmée.
Un mail récapitulatif est envoyé sur le mail de l'utilisateur. Ce mail comporte un lien unique pour annuler sa réservation. Selon la configuration d'Alma : il envoie ou nom un mail de confirmation d'annulation.
Les interactions entre l'application et Alma sont faites à travers l'API RESTful Alma mis à disposition par Exlibris en temps réel. Toutes la gestion du backoffice par les bibliothécaires se fait dans Alma directement (ex : suppression d'une réservation, blocage d'une salle via une réservation faite directement dans Alma...)

## Pré-requis :
- Serveur Apache en HTTPS (testé sur CentOS v7.9 / Apache v2.4) avec module URLrewriting activé 
- PHP v7.3 (non testé sur PHP v7.4+/8+)
- Serveur MariaDB v10.3 (non testé sur version supérieure)
- 1 token Developer Network Exlibris pour utiliser l'API RESTful Alma Exlibris (https://developers.exlibrisgroup.com/ -> demander à exlibris d'associer votre compte developpeur à votre instance Alma, créer un token dans - Manage API Keys - pour l'application Alma avec les bons droits : Users/Bibs/Configurations - Read/write)

### Autres dépendances :
#### JS/CSS (via CDN - lien en dur dans le code) :
- utilisation de bootstrap (testé en v4.6) (https://getbootstrap.com/)
- utilisation de JQuery (testé en v3.6) (https://jquery.com/)
- utilisation de JQueryUI (testé en v1.13) (https://jqueryui.com/)
- utilisation de JQuery-dateFormat (testé en v1.0) (https://github.com/phstc/jquery-dateFormat/)
- utilisation de PopperJS (testé en v1.16) (https://popper.js.org/)
- utilisation de FontAwesome (testé en v4.7) (https://fontawesome.com/v4/icons/)
#### PHP (installation locale) :
- SimpleSAMLPHP (testé en v1.19) pour l'authentification via SAML (shibboleth) afin de récupérer l'identifiant de l'utilisateur dans Alma (https://simplesamlphp.org/) : un SP doit être installé et fonctionnel pour fonctionner avec l'application.
- PHPMailer (testé en v6.0) : envoi de mail pour confirmer la réservation (https://github.com/PHPMailer/PHPMailer). Un serveur d'envoi de mail SMTP doit être disponible (sur port tcp/25 sans chiffrement et authentification).

## Installation
- Copier tous les fichiers de l'application dans le répertoire root d'apache
- Modifier le fichier config.php pour paramètrer/personnaliser l'application (chemin, url, titre, logo, serveur base de données, token Alma, serveur smtp, serveurs IDP SAML...) : voir exemple fourni
- Importer le script reservation-salle.sql sur votre serveur MariaDB pour créer la base et la table nécessaire à l'application (attention à bien mettre les bons droits utilisateur). Supprimer le fichier reservation-salle.sql du serveur.
- Copier vos logos en png dans le dossier /images -> Mettre des logos de taille comparable aux exemples fournis (les déclarer dans config.php comme selon exemple)
- Téléchager PHPMailer et copier les fichiers dans un dossier /PHPMailer à la racine du site (nom dossier en dur dans le code)
- Téléchager et installer SimpleSAMLPHP, configurer un SP dans l'application et tester que l'authentification fonctionne bien depuis l'interface SimpleSAMLPHP (il faut renseigner le chemin vers SimpleSAMLPHP et le nom du SP dans config.php. L'attribut eppn retourné par shibboleth doit être un identifiant utilisateur dans alma).
- Configurer les notices de salles dans Alma (noter les mmsid, holdingid, itemid, code-barres de chaque salle). Attention : suite à un bug dans Alma, il faut mettre une seule salle par notice sinon on ne peut retrouver la salle d'une réservation faite depuis Alma dans l'application.
- Configurer les BU et salles disponibles dans l'applications via le fichier JSON config_bu.json (voir exemple fourni) : ce fichier contient les BU et les salles accessibles, une url de description de la BU et des salles vers un site externe, une chemin vers une photo de la BU, la description des salles (en html), les horaires d'ouverture pour chaque jour de la semaine, les plannings spécifiques d'ouverture et fermeture (jour férié, BU fermées exceptionnellement...), les informations techniques pour faire le lien avec Alma (mmsid, holdingid, itemid, CB), une catégorie de salle (permettant de générer un filtre automatiquement)...
- copier dans le dossier /images une photo des BU déclarées dans config_bu.json (nomenclature [attribut_id].png)

## Notes
- Application en français uniquement pour le moment
- Plusieurs serveurs IDP Shibboleth peuvent être utilisés pour l'authentification des utilisateurs (voir fichier config.php)
- Il n'est pas possible de configurer le port (par défaut : tcp/25), le chiffrement (par défaut : sans) et l'authentification (par défaut : sans) pour le serveur SMTP à utiliser directement dans config.php

## Licence
Reservation-salle Alma SCDI est un logiciel libre sous licence GNU GPL (voir fichier LICENSE / https://www.gnu.org/licenses/).
  
Aucun support n'est assuré pour le moment par le SCDI de Montpellier mais si vous utilisez notre application pour votre établissement, nous serions content de le savoir !

## Captures
- Accueil / choix bibliothèque :
https://reservation-salle.scdi-montpellier.fr/capture/1.png

- Visualisation des plannings :
https://reservation-salle.scdi-montpellier.fr/capture/2.png
