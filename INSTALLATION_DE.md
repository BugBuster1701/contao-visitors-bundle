# Installation von Contao Visitors Bundle

Es gibt zwei Arten der Installation.

* mit dem Contao-Manager, für die Contao Managed-Editon
* über die Kommandozeile, für die Contao Managed-Editon


## Installation über Contao-Manager

* Suche das Paket: `bugbuster/contao-visitors-bundle`
* Installation der Erweiterung
* Klick auf "Install Tool"
* Anmelden und Datenbank Update durchführen

__Achtung__: Nutzer von Contao 4.4 - 4.9, verwenden `^1.5` als Versionsangabe!


## Installation über die Kommandozeile

Installation in einer Composer-basierenden Contao 4.10+ Managed-Edition:

* `composer require "bugbuster/contao-visitors-bundle"`
* Aufruf https://deinedomain/contao/install
* Datenbank Update durchführen

Installation in einer Composer-basierenden Contao 4.4 - 4.9 Managed-Edition:

* `composer require "bugbuster/contao-visitors-bundle=^1.5"`
* Aufruf https://deinedomain/contao/install
* Datenbank Update durchführen
