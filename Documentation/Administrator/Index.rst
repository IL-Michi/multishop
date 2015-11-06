﻿.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../Includes.txt


.. _changelog:

Administrator
=============
Multishop is working on the latest PHP 5.6. But you need to suppress the deprecation errors. You can do this by adding the following configuration to the php.ini file (.htaccess won't work):
error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT

jQuery / t3jquery notice
========================
Multishop requires jQuery 2. Also enable the jQuery module: Migrate + all the Bootstrap modules.

Bootstrap 3 required
====================
Multishop expects the website to have Twitter Bootstrap loaded. You will have to load the JavaScript library (with i.e. t3jquery) yourself.
Multishop provides bootstrap.css and will load it automatically through TypoScript (page.includeCSS.bootstrapCss / multishop_admin_page.includeCSS.bootstrapCss).


