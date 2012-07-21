<?php
/**
 * Translator - Install
 *
 * @author     PoPoutdoor <PoPoutdoor@gmail.com>
 * @package    Ushahidi - Translator
 */

class Translator_Install {

	/**
	 * Constructor to load the shared database library
	 */
	public function __construct()
	{
		$this->db = Database::instance();
	}

	/**
	 * Creates the required database tables for the Translator plugin
	 */
	public function run_install()
	{
		// Create the database tables.
		// Also include table_prefix in name

		// *table to store locale files info - has_many
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.Kohana::config('database.default.table_prefix').'i18n_file` (
			`id` smallint(2) unsigned NOT NULL AUTO_INCREMENT,
			`path` varchar(64) DEFAULT NULL,
			`filename` varchar(64) DEFAULT NULL,
			`hash` varchar(32) DEFAULT NULL,
			`status` tinyint(1) DEFAULT 0,
		  PRIMARY KEY (`id`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
		
		// *table to store i18n files data - belongs_to i18n_file
		$this->db->query('CREATE TABLE IF NOT EXISTS `'.Kohana::config('database.default.table_prefix').'i18n_data` (
			`id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
			`file_id` smallint(2) unsigned DEFAULT NULL,
			`locale` varchar(10) DEFAULT NULL,
			`key` varchar(64) DEFAULT NULL,
			`text` longtext DEFAULT NULL,
			`status` tinyint(1) DEFAULT 0,
		  PRIMARY KEY (`id`),
		  KEY `file_id` (`file_id`),
		  KEY `key` (`key`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1');
		
	}

	/**
	 * Deletes the database tables for the actionable module
	 */
	public function uninstall()
	{
		
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'i18n_file`');
		$this->db->query('DROP TABLE `'.Kohana::config('database.default.table_prefix').'i18n_data`');
	}
}
