=== About ===
name: Translator
website: 
description: A tool for i18n translation
version: 0.1
requires: 2.x
tested up to: Ushahidi 2.5
author: PoPoutdoor
author website: coming soon!!!

== Description ==
A tool for i18n translation, need admin rights.

== Configuration ==
1. Edit config/translator.php to add target locale(s) of your translation work.
2. Save config/translator.php.

Note: No checking for valid locale code.

== Installation ==
1. Copy the entire /translator/ directory into your /plugins/ directory.
2. Change owner recursively to your WEBSERVER USER.
3. If default access rights doesn't works on your server, change access rights 
   recursively on directory to 0755, on file to 0644.
   If you need to tighten access rights, recommended settings is: 
     directory 0750 and file 0640.
4. Activate the plugin.
5. The plugin is accessible from: admin/manage/translator

== Changelog ==
2012-07-21 Initial release.
2012-07-24 Update README.md and readme.txt.
