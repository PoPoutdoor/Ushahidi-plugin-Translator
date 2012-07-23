Ushahidi-plugin-Translator
==========================

Online tools which make life easier for every translator of Ushahidi system.

Note: This is a functional release with imcomplete coding, security issues and bug is expected.
      DO NOT install on production server.

Version
-------
Curent released version is 0.1

Limitation
----------
This tool supports up to 2 level $lang array.

To do
-----
1. Add 'Manage' tab to handle database operations. e.g. clear all data, remove data of written file, etc.
2. Add code to make progress bar works on index page.
3. Improve UI. e.g. Better form handling.
4. Remove dependency of Ushahidi system (make this tool works on any kohana 2.3)
5. Add feature to call external online translation on Edit page.

Configuration
-------------
1. Edit config/translator.php to add target locale(s) of your translation work.
2. Save config/translator.php. Note: No checking for valid locale code.

Installation
------------
1. Copy the entire /translator/ directory into your /plugins/ directory.
2. Change owner recursively to your webserver user.
3. Change access rights on directory recursively to 0750, file to 0640.
If this doesn't works on your server, 0755 on directory & 0644 on file will do the tricks.
4. Activate the plugin.

Change log
----------
2012-07-21 Initial release.
2012-07-24 Update README.md


