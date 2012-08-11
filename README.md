Ushahidi-plugin-Translator
==========================

Online tools which make life easier for every translator of Ushahidi system.

Note: This is a functional release with imcomplete coding, security issues and 
      bug is expected. DO NOT install on production server.

Version
-------
Curent released version is 0.1

Limitation
----------
This tool supports up to 2 level $lang array.

To do
-----
1. Add 'Manage' tab to handle database operations. e.g. clear all data, remove 
   data of written file, etc.
2. Add code to make progress bar works on index page.
3. Improve UI. e.g. Better form handling.
4. Remove dependency of Ushahidi system (make this tool works on any kohana 2.3)
5. Add feature to call external online translation on Edit page.

Configuration
-------------
1. Edit config/translator.php to add target locale(s) of your translation work.
2. Save config/translator.php.

Note: No checking for valid locale code.

Installation
------------
1. Copy the entire /translator/ directory into your /plugins/ directory.
2. Change owner recursively to your WEBSERVER USER.
3. If default access rights doesn't works on your server, change access rights 
   recursively on directory to 0755, on file to 0644.
   If you need to tighten access rights, recommended settings is: 
     directory 0750 and file 0640.
4. Activate the plugin.
5. The plugin is accessible from: admin/manage/translator

Change log
----------
2012-07-21 Initial release.
2012-07-24 Update README.md and readme.txt.
2012-08-12 Code completed for v0.1.

