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
						<li><a href="<?php echo url::site() . 'admin/manage/translator' . $search ?>"><?php echo Kohana::lang('translator.file_list');?></a></li>
						<li><a href="#" class="active"><?php echo Kohana::lang('translator.edit');?></a></li>
					</ul>
					<!-- tab -->
					<div class="tab">
						<ul>
							<li><a href="<?php echo url::site() . 'admin/manage/translator/edit/' .$file_id ?>"><?php echo Kohana::lang('translator.status_all');?></a></li>
							<li><a href="<?php echo url::site() . 'admin/manage/translator/edit/' . $file_id .'?view='. _XLT_ ?>" class="state_<?php echo _XLT_;?>"><?php echo Kohana::lang('translator.status_xlt');?></a></li>
							<li><a href="<?php echo url::site() . 'admin/manage/translator/edit/' . $file_id .'?view='. _NEW_ ?>" class="state_<?php echo _NEW_;?>"><?php echo Kohana::lang('translator.status_new');?></a></li>
							<li><a href="<?php echo url::site() . 'admin/manage/translator/edit/' . $file_id .'?view='. _UPD_ ?>" class="state_<?php echo _UPD_;?>"><?php echo Kohana::lang('translator.status_upd');?></a></li>
				<?php print form::open(NULL, array('method'=>'get')); ?>
							<li class="rhs">
								<?php 
									print form::input('search', '') . form::checkbox('mode', 'key') . form::label('key', Kohana::lang('translator.key')) . '&nbsp;' . form::submit(array('name' => '', 'id' => 'button'), Kohana::lang('translator.search'));
								?>
							</li>
				<?php print form::close(); ?>
						</ul>
					</div>
				</div>
				<div class="head">
					<h3 class="info"><?php echo $file_info;?></h3>
					<span class="rhs">
				<?php 
					print form::open(NULL, array(), array('file' => $file_id))
						. form::submit(array('name' => '', 'id' => 'button'), Kohana::lang('translator.write_file'))
						. form::close(); 
				?>
					</span>
				</div>
				<!-- green-box -->
				<?php if ($status):?>
				<div class="green-box">
					<h3 style="position:relative;float:left;padding:8px 35px;"><?php echo Kohana::lang('translator.view_'.$status);?></h3>
				</div>
				<?php endif; ?>
				<div class="table-holder">
					<table class="xlat">
						<thead>
							<tr>
								<th>&nbsp;<?php echo Kohana::lang('translator.key');?></th>
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
	$css = ' cols="50" ';
	$action = form::submit(array('name' => 'xlat', 'id' => 'button'), Kohana::lang('translator.set_xlat')) . '&nbsp;'
		. form::submit(array('name' => 'reset', 'id' => 'button'), Kohana::lang('translator.set_new'));

	foreach ($key_list as $key => $val)
	{
		foreach ($val as $lang_key => $text)
		{
			if (in_array($lang_key, $locales))
			{
				$id = key($text);

				$height = ceil(strlen($text[$id]) / 50) * 1.2;
				if ($height != 0)  $css .= 'onClick="this.style.height=' . $height . ' + \'em\';"';

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
						<tr>
							<td>
								&nbsp;<?php echo $show; ?>
							</td>
							<td class="locale"><?php echo $lang_key; ?></td>
<?php print form::open(NULL, array(), array('key' => $id, 'locale' => $lang_key, 'pos' => $pos)); ?>
							<td><?php print form::textarea($lang_key, $text[$id], $style); ?></td>
							<td class="rhs"><?php echo $actions . form::close('</td>'); ?>
						</tr>
<?php
			}
			else
			{
				// 2-level text
				foreach($text as $locale => $text2)
				{
					$id = key($text2);

					$height = ceil(strlen($text2[$id]) / 50) * 1.2;
					if ($height != 0) $css .= 'onClick="this.style.height=' . $height . ' + \'em\';"';

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
						<tr>
							<td>
								&nbsp;<?php echo $show; ?>
							</td>
							<td class="locale"><?php echo $locale; ?></td>
<?php print form::open(NULL, array(), array('key' => $id, 'subkey' => $lang_key, 'locale' => $locale, 'pos' => $pos)); ?>
							<td><?php print form::textarea($locale, $text2[$id], $style); ?></td>
							<td class="rhs"><?php echo $actions . form::close('</td>'); ?>
						</tr>
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
