=== PictPocket ===
Contributors: Semageek
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=admin%40semageek%2ecom&item_name=wp%2dPictPocket&no_shipping=0&no_note=1&tax=0&currency_code=EUR&lc=FR&bn=PP%2dDonationsBF&charset=UTF%2d8
Tags: blocage, contenu, rss, image, jpg, hotlinking, block, htaccess
Requires at least: 2.1
Tested up to: 2.8
Stable tag: 1.2.2

Identifier les voleurs de contenus et les bloquer - Identify and block HotLinks.

== Description ==

= FRANCAIS  = 

PictPocket est un plugin qui permet d'identifier et de bloquer les voleurs de contenus.

La plupart des bloggueurs sont confront&eacute; a un grave probleme : 
le vol de contenu ou hotlinking. 

En effet, souvent d'autres sites s'approprient les images heberg&eacute;es chez vous pour les publier sur leur site. 

Pickpocket est un plugin pour Wordpress qui permet d'identifier ces voleurs de contenus et de pouvoir les bloquer en replacant les images vol&eacute;es par un logo decourageant.

Allez sur [pictPocket Plugin Page](http://www.semageek.com/2009/06/27/pictpocket-un-plugin-wp-qui-identifie-et-bloque-les-voleurs-de-contenu/) pour plus d'information.

= ENGLISH =

PictPocket is a plugin that allows to identify and block content thieves.

Most bloggers are faced with a serious problem: the theft of content or hotlinking.

Indeed, many other sites appropriate images hosted with you to publish on their site.

Pickpocket is a plugin for Wordpress that allows thieves to identify such content and to block stolen images by placing a discouraging logo.

Go on [pictPocket Plugin Page](http://www.semageek.com/2009/06/27/pictpocket-un-plugin-wp-qui-identifie-et-bloque-les-voleurs-de-contenu/) for more information.



== Installation ==


= FRANCAIS = 

1. T&eacute;l&eacute;chargez `pictPoket.zip`
2. Uploader le dossier `picpocket` dans le r&eacute;pertoire `/wp-content/plugins/`
3. Activer le plugin dans le menu `Extensions` de Wordpress

= ENGLISH =

1. Download `pictPoket.zip`
2. Upload file `picpocket` in the folder `/wp-content/plugins /` 
3. Activate the plugin in the menu `Extensions` Wordpress 

== Frequently Asked Questions ==

= Impossible de charger pictpocket/pictPocket.php =

V&eacute;rifiez que le nom du repertoire ou se situe la base du plugin est bien `picpocket`,
En minuscule et sous `/wp-content/plugins/`.

= Mon site est devenu inaccesible =

Peut etre que vote hebergeur ne supporte pas les RewriteRules, editer le fichier `.htaccess` a la racine de votre site, et effacez tous ce qui est entre les balises `# BEGIN pictPocket` et `# END pictPocket`.
Puis desactivez le plugin.

== Screenshots ==

= FRANCAIS = 

1. Menu de Pictpocket dans l'interface d'administration.
2. Synthese : Tableau de Bord de Pictpocket.
3. Autorisation : G&eacute;rer les autorisations.
4. Hotlinks : Identification et blocage des voleurs.

= ENGLISH =

1. SideBar Menu of Pictpocket
2. Overview : Dashbord of Pictpocket.
3. Autorisation : Allow acces.
4. Hotlinks : Identify and block thieves.

== Changelog ==

= 1.0.0 =
* Premiere Version stable

= 1.1.0 =
* Utilisation des noms de domaines pour les voleurs
* Corrections de bugs mineurs

= 1.1.1 =
* Correction d'un bug plantant l'interface admin

= 1.2.0 =
* Ajout du module de gestion multilingue.
* Ajout du language Anglais.
* Correction de duplicate entry en RewriteCond.
* Chemin du plugin automatique.
* Am&eacute;lioration de l'install et d&eacute;sinstallation.
* Affichage du nombre de nouveaux hotlink dans la side bar.
* Possibilit&eacute; de g&eacute;rer des autorisations a partir de la page Voleurs.

= 1.2.1 =
* Correction de quelques bugs mineurs

= 1.2.2 =
* correction : Affichage du nombre de nouveaux hotlink dans la side bar.
* ajout d'un menu option
* image de remplacement custom
* Am&eacute;lioration de la d&eacute;sintallation.

== Am&eacute;liorations Futures ==

* Choix de plusieurs m&eacute;thode pour contrer les hotlinks en graduel (pr&eacute;vention, radical et violent).
* Ajout d'une sous rubrique Bloqu&eacute;s dans Hotlink.
* Ajout d'une sous rubrique Ignor&eacute;s dans Hotlink.
* Ajout d'une sous rubrique Nouveau dans Hotlink.
* Possibilit&eacute; de purger les hotlinks inactifs.

== Remerciement ==

lamogere : pour son code de Watermark.

== Contactez l'auteur ==

Pour signaler un bug ou encore une demande de modification.
Vous pouvez contacter l'auteur `pictpocket@semageek.com`




