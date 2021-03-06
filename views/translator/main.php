<?php
/**
 * Translator Main page.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com>
 * @package    Ushahidi - http://source.ushahididev.com
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */
?>
			<div class="bg">
				<h2>
					<?php echo admin::manage_subtabs("translator"); ?>
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
				<!-- First run -->
				<?php if ($init_db):?>
				<div class="green-box">
					<h3><?php echo Kohana::lang('translator.init_db');?></h3>
					<ul>
					<?php
						$locales = Kohana::config('translator.locales');
						for ($i = 0; $i < $count; $i++)
						{
							echo '<li>'. ($i) ? Kohana::lang('translator.config_target') : Kohana::lang('translator.config_source') . $locales[$i] .'</li>';
						}
					?>
					</ul>
				</div>
				<?php endif; ?>
				<?php if (is_object($files)): ?>
				<!-- tabs -->
				<div class="tabs">
					<!-- tabset -->
					<ul class="tabset">
						<li><a href="<?php echo url::site() . 'admin/manage/translator' ?>" class="active"><?php echo Kohana::lang('translator.file_list');?></a></li>
						<li><a href="#"><?php echo Kohana::lang('translator.edit');?></a></li>
					</ul>
					<!-- tab -->
					<div class="tab">
						<ul>
							<li><a href="<?php echo url::site() . 'admin/manage/translator' ?>"><?php echo Kohana::lang('translator.status_all');?></a></li>
							<li><a href="<?php echo url::site() . 'admin/manage/translator?view='. _XLT_ ?>" class="state_<?php echo _XLT_;?>"><?php echo Kohana::lang('translator.status_xlt');?></a></li>
							<li><a href="<?php echo url::site() . 'admin/manage/translator?view='. _NEW_ ?>" class="state_<?php echo _NEW_;?>"><?php echo Kohana::lang('translator.status_new');?></a></li>
							<li><a href="<?php echo url::site() . 'admin/manage/translator?view='. _UPD_ ?>" class="state_<?php echo _UPD_;?>"><?php echo Kohana::lang('translator.status_upd');?></a></li>
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
				<!-- BEGIN: file list -->
				<div class="table-holder">
					<table class="xlat">
						<thead>
							<tr>
								<th width="18%"><?php echo Kohana::lang('translator.folder');?></th>
								<th width="306px;"><?php echo Kohana::lang('translator.progress');?></th>
								<th><?php echo Kohana::lang('translator.file');?></th>
								<th class="action"><?php echo Kohana::lang('translator.actions');?></th>
							</tr>
						</thead>
						<?php if (count($files)): ?>
						<tfoot>
							<tr class="foot">
								<td colspan="4" class="action">
									<strong><?php echo count($files).Kohana::lang('translator.num_files'); ?></strong>
								</td>
							</tr>
						</tfoot>
						<?php endif; ?>
						<tbody>
						<?php if (! count($files)): ?>
							<tr><td colspan="4"><hr /></td></tr>
							<tr>
								<td colspan="4" class="action">
									<h3><?php echo Kohana::lang('translator.no_result');?></h3>
								</td>
							</tr>
						<?php endif; ?>
						<!-- dump file info -->
						<?php
							$locales = Kohana::config('translator.locales');
							$last_folder = '';
							foreach ($files as $file)
							{
								$folder = $file->path;
								$hr = ($folder == $last_folder) ? FALSE : TRUE;

								if ($hr)  echo '<tr><td colspan="4"><hr /></td></tr>';
								$all_keys = ORM::factory('data')->where(array('locale !=' => $locales[0], 'file_id' => $file->id))->count_all();
								$xlat_keys = ORM::factory('data')->where(array('locale !=' => $locales[0], 'file_id' => $file->id, 'status' => _XLT_))->count_all();
								$progress = intval(floor(($xlat_keys / $all_keys)*100));
								// update file status if 100% translated
								if ($progress == 100)
								{
									$file = ORM::factory('file', $file->id);
									$file->status = _XLT_;
									$file->save();
								}
								$progress .= '%';
						?>
							<tr>
								<td><?php if ($hr)  echo $folder; ?></td>
								<td>
									<div class="bar-wrap">
										<div class="bar-value" style="width: <?php echo $progress;?>;">
											<div class="bar-text">
												<?php echo $progress;?>
											</div>
										</div>
									</div>
								</td>
								<td class="file">
									<a name="file_<?php echo $file->id;?>" href="<?php echo url::site() . 'admin/manage/translator/edit/' . $file->id . $search; ?>"><?php echo $file->filename; ?></a>
								</td>
								<td class="rhs">
						<?php 
							print form::open(NULL, array(), array('file_id' => $file->id));
							print form::submit(array('name' => 'xlat', 'id' => 'button'), Kohana::lang('translator.set_new')) . '&nbsp;'
								. form::submit(array('name' => 'edit', 'id' => 'button'), Kohana::lang('translator.edit')) . '&nbsp;'
								. form::submit(array('name' => 'write', 'id' => 'button'), Kohana::lang('translator.write_file'));
							print form::close(); 
						?>
								</td>
							</tr>
						<?php
								$last_folder = $folder;
							}
						?>
							<tr><td colspan="4"><hr /></td></tr>
						</tbody>
					</table>
				</div>
				<!-- END: file list -->
				<div class="tabs">
					<div class="tab">
						<ul>
							<li class="rhs"><a href="#" name="go_top"><?php echo Kohana::lang('translator.go_top');?></a></li>
							<li><a href="#" name="go_top"><?php echo Kohana::lang('translator.go_top');?></a></li>
						</ul>
					</div>
				</div>
				<?php endif; ?>
			</div>
