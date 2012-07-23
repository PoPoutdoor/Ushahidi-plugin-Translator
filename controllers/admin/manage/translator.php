<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Translator - Administrative Controller
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     PoPoutdoor <PoPoutdoor@gmail.com>
 * @package    Ushahidi - Translator
 * @copyright  PoPoutdoor <PoPoutdoor@gmail.com>
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */

class Translator_Controller extends Admin_Controller
{
	// database table prefix
	var $table_prefix = '';

	function __construct()
	{
		parent::__construct();
		$this->template->this_page = 'translator';

		// If user doesn't have access, redirect to dashboard
		if ( ! $this->auth->has_permission("manage"))
		{
			url::redirect(url::site().'admin/dashboard');
		}

		// file/data status
		define('_DEL_', 0);
		define('_SYN_', 1);
		define('_NEW_', 2);
		define('_UPD_', 3);
		// File status only
//		define('VERIFIED', 4);
//		define('APPROVED', 8);
//		define('WIP', 16);
	}

	function index()
	{
		$error = $first_run = FALSE;
		$errors = array();

		$this->template->content = new View('translator/main');


		$this->template->content->update_db = FALSE;
		$this->template->content->show = '';

		// check config and setup local vars
		if ($this->check_config())
		{
			return;
		}

		$status = (isset($_GET['view'])) ? (int) $_GET['view'] : 0;

		$locales = Kohana::config('translator.locales');

		if ($_POST)
		{
			$post = new Validation($_POST);
			$post->add_rules('file', 'required', 'numeric');

			if($post->validate())
			{
				$this->write_file($post['file']);
			}
		}
		else
		{
			$lang_files = Kohana::list_files('i18n'.DIRECTORY_SEPARATOR.$locales[0], TRUE);

			// check source data
			$row = ORM::factory('file', 1);
			if (!$row->id)
			{
				// first run, init database
				$this->template->content->init_db = TRUE;
				$this->template->content->count = Kohana::config('translator.locale_count');

				$this->import_locale($lang_files);
			}
			else
			{
	/*			// Kohana internal cache issue, no clear internal cache method		
				if (is_file('application/cache/kohana_find_file_paths'))
				{
					unlink('application/cache/kohana_find_file_paths');
					$lang_files = Kohana::list_files('i18n'.DIRECTORY_SEPARATOR.$locales[0], TRUE);
				}
	*/
				// check source locale files for updates
				$this->sync_locale($lang_files);
			}
		}

		if ($status)
		{
			$files = ORM::factory('file')->where('status', $status)->orderby(array('path' => 'asc', 'filename' => 'asc'))->find_all();
			$file_count = ORM::factory('file')->where('status', $status)->count_all();
		}
		else
		{
			$files = ORM::factory('file')->orderby(array('path' => 'asc', 'filename' => 'asc'))->find_all();
			$file_count = ORM::factory('file')->count_all();
		}

		$this->template->content->error = $error;
		$this->template->content->init_db = $first_run;
		$this->template->content->files = $files;
		$this->template->content->total = $file_count;
	}

	/*
	* Check config settings
	*
	* @return bool - TRUE means error
	*/
	private function check_config()
	{
		// Get the table prefix
		$this->table_prefix = Kohana::config('database.default.table_prefix');
		// Get settings
		$locales = Kohana::config('translator.locales');

		// check defined locales
		$clean_locales = array();
		foreach ($locales as $key => $not_used)
		{
			if (empty($locales[$key]))
			{
				unset($locales[$key]);
				continue;
			}

			$clean_locales[] = $locales[$key];
		}

		$i = count($clean_locales);
		// config check passed
		if ($i > 1)
		{
			Kohana::config_set('translator.locales', $clean_locales);
			Kohana::config_set('translator.locale_count', $i);
			return FALSE;
		}

		if (!$i)
		{
			$this->template->content->errors = array(Kohana::lang('translator.source_error'));
		}
		else
		{
			$this->template->content->errors = array(Kohana::lang('translator.target_error'));
		}

		$this->template->content->error = TRUE;
		return TRUE;
	}

	/*
	* import new language files to database
	*
	* @parm array file lists
	*/
	public function import_locale($file_list)
	{
		$locales = Kohana::config('translator.locales');
		$values_template = "('%s', '%s', '%s', %d)";
		$query = sprintf("INSERT INTO %si18n_file (`path`, `filename`, `hash`, `status`) VALUES ", 
							 $this->table_prefix);

		$values = array();

		// construct db query
		foreach ($file_list as $not_used => $full_path)
		{
			list($path, $filename) = explode(DIRECTORY_SEPARATOR."i18n".DIRECTORY_SEPARATOR.$locales[0].DIRECTORY_SEPARATOR,
						 str_replace(DOCROOT, '', $full_path));

			$values[] = sprintf($values_template, $path, $filename, md5_file($full_path), _NEW_);
		}

		// update file db
		$query .= implode(",", $values);
		// need to check return status on db operations?
		$ret = Database::instance()->query($query);

		// handle file data
		foreach ($file_list as $not_used => $full_path)
		{
			$rel_path = str_replace(DOCROOT, '', $full_path);
			list($path, $filename) = explode(DIRECTORY_SEPARATOR."i18n".DIRECTORY_SEPARATOR.$locales[0].DIRECTORY_SEPARATOR,
							 $rel_path);

			$file = ORM::factory('file')->where(array('path' => $path, 'filename' => $filename))->find();
			// process file data
			$this->import_data($file->id, $rel_path);
		}
	}

	/*
	* sync language files to database
	*
	* @parm array file lists
	*/
	public function sync_locale($file_list)
	{
		$locales = Kohana::config('translator.locales');
		$update_hash = "UPDATE ".$this->table_prefix."i18n_file SET `hash` = '%s' WHERE `id` = %d";

		$ids_syn = $ids_upd = $file_new = $file_upd = $sql = array();

		// set ALL file status to DEL in db
		$query = 'UPDATE '.$this->table_prefix.'i18n_file SET status = '._DEL_;
		// need to check return status on db operations?
		$ret = Database::instance()->query($query);

		// get filesystem file info
		foreach ($file_list as $not_used => $full_path)
		{
			list($path, $filename) = explode(DIRECTORY_SEPARATOR."i18n".DIRECTORY_SEPARATOR.$locales[0].DIRECTORY_SEPARATOR,
							 str_replace(DOCROOT, '', $full_path));

			$file = ORM::factory('file')->where(array('path' => $path, 'filename' => $filename))->find();

			if ($file->loaded)
			{
				$hash = md5_file($full_path);

				if ($file->hash != $hash)
				{
					// update file hash
					$sql[] = sprintf($update_hash, $hash, $file->id);
					// set status to UPD
					$ids_upd[] = $file->id;
					$file_upd[] = $full_path;
				}
				else
				{
					// set status to SYN
					$ids_syn[] = $file->id;
				}
			}
			else
			{
				// new file, prepare import list
				$file_new[] = $full_path;
			}
		}

		// not modified, set file status back to SYN 
		if (!empty($ids_syn))
		{
			$ids = (count($ids_syn) > 1) 
				? "in (".implode(",", $ids_syn).")"
				: "= ".implode(",", $ids_syn);
			$query = sprintf("UPDATE ".$this->table_prefix."i18n_file SET `status` = %d WHERE `id` %s", 
							 _SYN_, $ids);
			// need to check return status on db operations?
			$ret = Database::instance()->query($query);
		}
		// modified, set file status to UPD 
		if (!empty($ids_upd))
		{
			$ids = (count($ids_upd) > 1) 
				? "in (".implode(",", $ids_upd).")"
				: "= ".implode(",", $ids_upd);
			$query = sprintf("UPDATE ".$this->table_prefix."i18n_file SET `status` = %d WHERE `id` %s", 
							 _UPD_, $ids);
			// need to check return status on db operations?
			$ret = Database::instance()->query($query);

			// update hash
			foreach ($sql as $query)
			{
				// need to check return status on db operations?
				$ret = Database::instance()->query($query);
			}
		}

		/*
			TODO: need code to handle renamed(add/del with most keys match) files?
		*/
		// new, import file
		if (!empty($file_new))
		{
			$this->import_locale($file_new);
		}

		// update, import file
		if (!empty($file_upd))
		{
			foreach ($file_upd as $not_used => $full_path)
			{
				$rel_path = str_replace(DOCROOT, '', $full_path);
				list($path, $filename) = explode(DIRECTORY_SEPARATOR."i18n".DIRECTORY_SEPARATOR.$locales[0].DIRECTORY_SEPARATOR,
								 $rel_path);
				$file = ORM::factory('file')->where(array('path' => $path, 'filename' => $filename))->find();
				// process file data
				$this->import_data($file->id, $rel_path, _UPD_);
			}
		}

		// no longer exists in filesystem, remove from db
		$del_files = ORM::factory('file')->where('status', _DEL_)->find_all();
		foreach ($del_files as $file)
		{
			if ($file->loaded)
			{
				$query = "DELETE FROM ".$this->table_prefix."i18n_file WHERE `id` = ".$file->id;
				// need to check return status on db operations?
				$ret = Database::instance()->query($query);

				// delete data
				$query = "DELETE FROM ".$this->table_prefix."i18n_data WHERE `file_id` = ".$file->id;
				// need to check return status on db operations?
				$ret = Database::instance()->query($query);
			}
		}
	}

	/*
	*  Read in locale files on first run or new file, 
	*  Only read in source locale files, read target locales from db afterwards.
	*
	*  @parm $fid: 	int file id
	*  @parm $file:	string file's related path
	*  @parm $status:	int _NEW_ or _UPD_
	*/
	public function import_data($fid, $file, $status = _NEW_)
	{
		$count = Kohana::config('translator.locale_count');
		$locales = Kohana::config('translator.locales');
		$src_locale = $locales[0];

		$query = sprintf("INSERT INTO %si18n_data (`file_id`, `locale`, `key`, `text`, `status`) VALUES ", 
							 $this->table_prefix);
		$values_template = "(%d, '%s', '%s', '%s', %d)";

		$values = $updates = array();
		// Database instance
		$db = new Database();

		if ($status == _UPD_)
		{
			$source_data = ORM::factory('data')->where(array('file_id' => $fid, 'locale' => $src_locale))->orderby('key', 'asc')->find_all();
			// construct $lang array
			$db_lang = array();
			foreach ($source_data as $dat)
			{
				$db_lang[$dat->key] = unserialize($dat->text);
			}
		}

		// read locales data from filesystem
		for ($i = 0; $i < $count; $i++)
		{
			$locale = $locales[$i];

			// handle source file key => value changes
			if (!$i)
			{
				// read source locale
				ob_start();
				include $file;
				ob_end_clean();

				if ($status == _UPD_)
				{
					$add = array_diff_key($lang, $db_lang);
					$del = array_diff_key($db_lang, $lang);
					$upd = array_diff(array_diff($lang, $db_lang), $add);

					if (!empty($add))
					{
						// add new key=>value from file to $lang
						foreach ($add as $key => $val)
						{
							$lang[$key] = $val;
						}
					}

					if (!empty($upd))
					{
						// add modified keys from file to $lang
						foreach ($upd as $key => $val)
						{
							$lang[$key] = $val;
						}
					}
				}

				ksort($lang);
				$keys = array_keys($lang);
				$source = $lang;
			}
			else
			{
				if ($status == _UPD_)
				{
					$locale_data = ORM::factory('data')->where(array('file_id' => $fid, 'locale' => $locale))->orderby('key', 'asc')->find_all();
					// construct $lang array
					$lang = array();
					foreach ($locale_data as $dat)
					{
						$lang[$dat->key] = unserialize($dat->text);
					}

					$add = array_diff_key($source, $lang);
					$del = array_diff_key($lang, $source);
					$upd = array_diff(array_diff($source, $lang), $add);
				}
				else
				{
					$inc_file = str_replace($src_locale, $locale, $file);

					if (is_file($inc_file))
					{
						ob_start();
						include $inc_file;
						ob_end_clean();

						ksort($lang);
					}
					else
					{
						$lang = array();
					}

					$add = array_diff_key($source, $lang);
					$del = array_diff_key($lang, $source);
				}
			}

			// generate SQL statements
			foreach ($keys as $not_used => $key)
			{
				if (!empty($add) AND !empty($add[$key]))
				{
					if ($status == _NEW_ OR empty($lang[$key]))
					{
						if (is_array($add[$key]))
						{
							foreach ($add[$key] as $key2 => $empty)
							{
								$add[$key][$key2] = '';
							}
						}
						else
						{
							$add[$key] = '';
						}
					}

					$values[] = sprintf($values_template, $fid, $locale, $key,
								 $db->escape_str(serialize($add[$key])), _NEW_);
				}
				elseif ($status == _NEW_)
				{
					$values[] = sprintf($values_template, $fid, $locale, $key,
								 $db->escape_str(serialize($lang[$key])), _NEW_);
				}
				elseif ($status == _UPD_)
				{
					$updates[] = "UPDATE ".$this->table_prefix."i18n_data 
								SET `status` = "._SYN_." 
								WHERE (`file_id` = '".$fid."' 
									AND `locale` = '".$locale."' 
									AND `key` = '".$key."')";
				}

			}

			if (!empty($del))
			{
				foreach ($del as $key => $not_used)
				{
					// remove from db
					$updates[] = "DELETE FROM ".$this->table_prefix."i18n_data 
								WHERE (`file_id` = '".$fid."'
									AND `locale` = '".$locale."' 
									AND `key` = '".$key."')";
				}
			}

			if (!empty($upd))
			{
				// add modified keys from $file
				foreach ($upd as $key => $val)
				{
					$updates[] = "UPDATE ".$this->table_prefix."i18n_data 
								SET `text` = '".$db->escape_str(serialize($lang[$key]))."', 
									`status` = "._UPD_." 
								WHERE (`file_id` = '".$fid."' 
									AND `locale` = '".$locale."' 
									AND `key` = '".$key."')";
				}
			}
		}

		if (!empty($updates))
		{
			foreach ($updates as $not_used => $sql)
			{
				// need to check return status on db operations?
				$ret = Database::instance()->query($sql);
			}
		}

		if (!empty($values))
		{
			$query .= implode(",", $values);
			// need to check return status on db operations?
			$ret = Database::instance()->query($query);
		}
	}

	/**
	* TODO: add Google translation support
	* Edit file with all configured locales
	*
	* @param int $id The file id 
	*/
	public function edit($id = FALSE)
	{
		// If user doesn't have access, redirect to dashboard
		if ( ! $this->auth->has_permission("manage"))
		{
			url::redirect(url::site().'admin/dashboard');
		}

		$locales = Kohana::config('translator.locales');

		$this->template->content = new View('translator/edit');
		$this->template->content->title = Kohana::lang('translator.edit');

		$error = FALSE;
		$errors = $lists = array();
		$file_info = '';
		$status = (isset($_GET['view'])) ? (int) $_GET['view'] : 0;

		if ($_POST)
		{
			$post = new Validation($_POST);

			// something wrong, just return
			if (! isset($post['update']) AND ! isset($post['reset']) AND ! isset($post['file'])) return;
/*
			// FIXME: not working
			if (isset($post['file']))
			{
				$ret = $this->write($post['file']);

				if (! empty($ret))
				{
					$errors[] = $ret;
					$error = TRUE;
				}

//				url::redirect('admin/manage/translator#'.(int) $post['file']);
				url::redirect('admin/manage/translator');
			}
*/
			$post->add_rules('locale', 'required', 'in_array['.implode(",", $locales).']');
			$post->add_rules('key', 'required', 'numeric');
			$post->add_rules('pos', 'required', 'numeric');
			$post->add_rules('subkey', 'alpha_dash');

			if($post->validate())
			{
				$locale = $post['locale'];
				$key_id = $post['key'];
				$pos = $post['pos'];

				$value = $post[$locale];

				// set key status
				$dat = ORM::factory('data')->where('locale', $locale)->find($key_id);
				if ($dat->loaded == TRUE AND $locale != $locales[0])
				{
					$db_text = unserialize($dat->text);

					$db_text = (is_array($db_text))
								? array_merge($db_text, array($post['subkey'] => $value))
								: $value;
					$dat->text = serialize($db_text);

					if (isset($post['update']))	$dat->status = _SYN_;
					if (isset($post['reset']))	$dat->status = _NEW_;

					$dat->save();

					url::redirect(url::current(TRUE) . '#' . $pos);
				}
				else
				{
					$errors[] = Kohana::lang('translator.err_load_key');
					$error = TRUE;
				}
			}
			else
			{
				$errors = arr::overwrite($errors, $post->errors());
				$error = TRUE;
			}
		}

		if (! $error)
		{
			// generate key list
			$file_data = ORM::factory('data')->where('file_id', $id)->orderby(array('key' => 'asc', 'id' => 'asc'))->find_all();

			$last_key = '';
			$arr = $lists = $show = array();

			$key_state = (!$status OR ($status == _SYN_)) ? TRUE : FALSE;
			// pack data
			foreach ($file_data as $dat)
			{
				$key = $dat->key;
				$text = unserialize($dat->text);
				$state = $dat->status;

				if (!is_array($text))
				{
					$text = array($dat->locale => array($dat->id => $text, 'status' => $state));
				}
				else
				{
					foreach ($text as $key2 => $txt)
					{
						$text[$key2] = array($dat->locale => array($dat->id => $txt, 'status' => $state));
					}
				}

				if ($key == $last_key OR empty($last_key))
				{
					$arr = array_merge_recursive($arr, $text);
				}
				elseif ($key != $last_key)
				{
					if ($status == _SYN_)
					{
						$logic = '$display = ($key_state AND '.implode(" AND ", $show).') ? TRUE : FALSE;';
					}
					else
					{
						$logic = '$display = ($key_state OR '.implode(" OR ", $show).') ? TRUE : FALSE;';
					}

					eval($logic);

					if ($display) $lists[$last_key] = $arr;
					$arr = $text;
					$show = array();
				}

				if ($dat->locale != $locales[0])
				{
					$show[] = ($state == $status) ? 'TRUE' : 'FALSE';
				}

				$last_key = $key;
			}
			// add back last $arr
			if ($display) $lists[$key] = $arr;

			// get file info
			$file = ORM::factory('file', $id);
			$file_info = $file->path.' -> '.$file->filename;
		}

		// template
		$this->template->content->error = $error;
		$this->template->content->errors = $errors;
		$this->template->content->file_id = $id;
		$this->template->content->status = $status;
		$this->template->content->file_info = $file_info;
		$this->template->content->key_list = $lists;
	}



	/*
	*  Output locale file from db to filesystem
	*
	*  @parm $id: int file id
	*/
	public function write($id = FALSE)
	{
		if (! $id) return;

		$id = (int) $id;

		$file = ORM::factory('file', $id);

		$path = $file->path;
		$filename = $file->filename;

		$locales = Kohana::config('translator.locales');

		$count = Kohana::config('translator.locale_count');
		for ($i = 1; $i < $count; $i++)
		{
			$locale = $locales[$i];

			$i18n_path = $path.DIRECTORY_SEPARATOR.'i18n';
			if (! is_writable($i18n_path))
			{
				$err = $i18n_path . Kohana::lang('translator.not_writable');
				throw new Kohana_Exception($err);
			}

			// get key=>text from db
			$out = '';
			$dat = ORM::factory('data')->where(array('file_id' => $id, 'locale' => $locale, 'status' => _SYN_))->orderby('key', 'asc')->select_list('key', 'text');
			foreach ($dat as $key => $val)
			{
				$text = unserialize($val);
				if (empty($text)) continue;

				$out .= "\n\t'$key' => ";

				if (!is_array($text))
				{
					$out .= "'$text',";
				}
				else
				{
					$out .= "array(\n";
					foreach ($text as $key2 => $txt)
					{
						$out .= "\t\t'$key2' => '$txt',\n";
					}
					$out .= "\t),";
				}
			}

			if (empty($out))
			{
				$err = Kohana::lang('translator.not_write_empty');
				throw new Kohana_Exception($err);
			}

			$rel_path = $i18n_path.DIRECTORY_SEPARATOR.$locale;
			if (!file_exists($rel_path))
			{
				if (! mkdir($rel_path, 0755))
				{
					$err = Kohana::lang('translator.not_writable');
					throw new Kohana_Exception($err);
				}
			}

			$file_path = $rel_path.DIRECTORY_SEPARATOR.$filename;

			// patch with php tags
			$out = "<?php\n\t// Generate by Translator. More than 2 level array is not supported!\n\t// Note: output edited, non-empty keys only!\n\t\$lang = array(" . $out . "\n\t);\n?>";

			if (file_put_contents($file_path, $out) === FALSE)
			{
				$err = Kohana::lang('translator.write_error') . $file_path;
				throw new Kohana_Exception($err);
			}

			chmod($file_path, 0644);
		}

		url::redirect('admin/manage/translator');
	}
}

?>
