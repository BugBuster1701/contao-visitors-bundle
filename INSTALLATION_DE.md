# Installation von Contao Visitors Bundle

Es gibt zwei Arten der Installation.

* mit dem Contao-Manager, für die Contao Managed-Editon
* über die Kommandozeile, für die Contao Managed-Editon


## Installation über Contao-Manager

* Suche das Paket: `bugbuster/contao-visitors-bundle`
* Installation der Erweiterung
* Datenbank Update durchführen

__Achtung__: Nutzer von Contao 4.13, verwenden `^1.8` als Versionsangabe!


## Installation über die Kommandozeile

Installation in einer Composer-basierenden Contao 5.2+ Managed-Edition:

* `composer require "bugbuster/contao-visitors-bundle"`
* `php bin/console contao:migrate`

Installation in einer Composer-basierenden Contao 4.13 Managed-Edition:

* `composer require "bugbuster/contao-visitors-bundle=^1.8"`
* `php bin/console contao:migrate`
