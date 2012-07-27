<?php
/**
 * Translator Edit page.
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
?>
			<div class="bg">
				<h2>
					<?php admin::manage_subtabs("translator"); ?>
				</h2>
				<div style="width:100%;background-color:#FFD8D9;padding:4px 0px;"><img src="<?php echo url::file_loc('img'); ?>media/img/experimental.png" alt="<?php echo Kohana::lang('translator.experimental');?>" style="position:relative;float:left;padding-left:250px;padding-right:5px;"/><?php echo Kohana::lang('translator.experimental_warn');?></div>
				<!-- red-box -->
				<?php if ($error):?>
				<div class="red-box">
					<h3><?php echo Kohana::lang('translator.error');?></h3>
					<ul>
					<?php
					foreach ($errors as $error_item => $error_description)
					{
						print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
					}
					?>
					</ul>
				</div>
				<?php endif; ?>
				<!-- tabs -->
				<div class="tabs">
					<!-- tabset -->
					<ul class="tabset">
						<li><a href="<?php echo url::site() . 'admin/manage/translator' ?>"><?php echo Kohana::lang('translator.file_list');?></a></li>
						<li><a href="#" class="active"><?php echo Kohana::lang('translator.edit');?></a></li>
					</ul>
					<!-- tab -->
					<div class="tab">
						<ul>
							<li><a href="<?php echo url::site() . 'admin/manage/translator/edit/' .$file_id ?>"><?php echo Kohana::lang('translator.status_all');?></a></li>
							<li><a href="<?php echo url::site() . 'admin/manage/translator/edit/' . $file_id .'?view='. _SYN_ ?>" class="state_<?php echo _SYN_;?>"><?php echo Kohana::lang('translator.status_syn');?></a></li>
							<li><a href="<?php echo url::site() . 'admin/manage/translator/edit/' . $file_id .'?view='. _NEW_ ?>" class="state_<?php echo _NEW_;?>"><?php echo Kohana::lang('translator.status_new');?></a></li>
							<li><a href="<?php echo url::site() . 'admin/manage/translator/edit/' . $file_id .'?view='. _UPD_ ?>" class="state_<?php echo _UPD_;?>"><?php echo Kohana::lang('translator.status_upd');?></a></li>
						</ul>
					</div>
				</div>
				<div class="head">
					<h3><?php echo $file_info;?></h3>
				<?php print form::open(); ?>
					<span class="rhs">
						<input type="hidden" name="file" id="file" value="<?php echo $file_id; ?>">
						<input type="submit" name="button" id="button" value="<?php echo Kohana::lang('translator.write_file');?>">
					</span>
				<?php print form::close(); ?>
				</div>
				<!-- green-box -->
				<?php if ($status):?>
				<div class="green-box">
					<h3 style="position:relative;float:left;padding:8px 35px;"><?php echo Kohana::lang('translator.view_'.$status);?></h3>
				</div>
				<?php endif; ?>
				<div class="table-holder">
					<table>
						<thead>
							<tr>
								<th>&nbsp;<?php echo Kohana::lang('translator.locale_key');?></th>
								<th><?php echo Kohana::lang('translator.locale');?></th>
								<th><?php echo Kohana::lang('translator.text');?></th>
								<th class="action"><?php echo Kohana::lang('translator.actions');?></th>
							</tr>
						</thead>
				<?php if (count($key_list)): ?>
						<tfoot>
							<tr class="foot">
								<td colspan="4" class="action">
									<strong><?php echo count($key_list) . Kohana::lang('translator.num_entries'); ?></strong>
								</td>
							</tr>
						</tfoot>
				<?php endif; ?>
						<tbody>
							<tr><td colspan="4"><a href="#" name="key_0"><hr /></a></td></tr>
				<?php if (! count($key_list)): ?>
							<tr><td colspan="4" class="action">
								<h3><?php echo Kohana::lang('translator.no_result');?></h3>
							</td></tr>
							<tr><td colspan="4"><hr /></td></tr>
				<?php endif; ?>
<!-- BEGIN: key list -->
<?php
	$locales = Kohana::config('translator.locales');

	$pos = 0;
	$css = ' cols="50"';
	$action = '<input type="submit" name="update" id="button" value="'.Kohana::lang('translator.update').'" />&nbsp;<input type="submit" name="reset" id="button" value="'.Kohana::lang('translator.set_new').'" />';

	foreach ($key_list as $key => $val)
	{
		foreach ($val as $lang_key => $text)
		{
			if (in_array($lang_key, $locales))
			{
				$id = key($text);
				if ($lang_key == $locales[0])
				{
					$show = $key;
					$style = $css.' readonly="readonly" class="ro"';
					$actions = '';
				}
				else
				{
					$show = '';
					$style = $css.' class="state_'.$text['status'].'"';
					$actions = $action;
				}
?>
<?php print form::open(); ?>
						<tr>
							<td>
								&nbsp;<?php echo $show; ?>
								<input type="hidden" name="key" id="key" value="<?php echo $id; ?>">
								<input type="hidden" name="locale" id="locale" value="<?php echo $lang_key; ?>">
								<input type="hidden" name="pos" id="pos" value="<?php echo $pos; ?>">
							</td>
							<td class="locale"><?php echo $lang_key; ?></td>
							<td><?php print form::textarea($lang_key, $text[$id], $style); ?></td>
							<td class="rhs"><?php echo $actions; ?></td>
						</tr>
<?php print form::close(); ?>
<?php
			}
			else
			{
				// 2-level text
				foreach($text as $locale => $text2)
				{
					$id = key($text2);
					if ($locale == $locales[0])
					{
						$show = $key.'<br />&nbsp;&nbsp;&nbsp;&nbsp;=>&nbsp;&nbsp;'.$lang_key;
						$style = $css.' readonly="readonly" class="ro"';
						$actions = '';
					}
					else
					{
						$show = '';
						$style = $css.' class="state_'.$text2['status'].'"';
						$actions = $action;
					}
?>
<?php print form::open(); ?>
						<tr>
							<td>
								&nbsp;<?php echo $show; ?>
								<input type="hidden" name="key" id="key" value="<?php echo $id; ?>">
								<input type="hidden" name="subkey" id="subkey" value="<?php echo $lang_key; ?>">
								<input type="hidden" name="locale" id="locale" value="<?php echo $locale; ?>">
								<input type="hidden" name="pos" id="pos" value="<?php echo $pos; ?>">
							</td>
							<td class="locale"><?php echo $locale; ?></td>
							<td><?php print form::textarea($locale, $text2[$id], $style); ?></td>
							<td class="rhs"><?php echo $actions; ?></td>
						</tr>
<?php print form::close(); ?>
<?php
				}
			}
		}

		$pos++;

		echo '<tr>
		<td colspan="4"><a href="#" name="key_'.$pos.'"><div class="hr-line"><span>'.Kohana::lang('translator.go_top').'</span><p>&#8963;</p></div></a></td>
		</tr>';
	}
?>
<!-- END: key list -->
						</tbody>
					</table>
				</div>
				<div class="tabs">
					<div class="tab">
						<ul>
							<li class="rhs"><a href="<?php echo url::site() . 'admin/manage/translator' ?>"><?php echo Kohana::lang('translator.file_list');?></a></li>
							<li><a href="#" name="go_top"><?php echo Kohana::lang('translator.go_top');?></a></li>
						</ul>
					</div>
				</div>
			</div>
