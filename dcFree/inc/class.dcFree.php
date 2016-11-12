<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin exportFree for Dotclear 2.
 * Copyright © 2015-2016 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }

__('Adaptation for Free hosting');


class dcFree extends dcPluginHelper29h {

	# tags begin and end for config patch
	const CONFIG_BEGIN = "\n# BEGIN Free hosting bootstrap\n\n";
	const CONFIG_END = "\n# END Free hosting bootstrap\n\n";

	# create config plugin (TODO: specific settings)
	protected function setDefaultSettings() {
		$this->core->blog->settings->addNamespace($this->plugin_id);
		$this->core->blog->settings->{$this->plugin_id}->put('updateNoBackup', false, 'boolean', __('Update, no backup (filesize >1Mo)'), false, true);
	}

	# specific actions for install
	protected function installActions($old_version) {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		# Check config.php file
		if (!is_file(DC_RC_PATH)) { throw new Exception(sprintf(__('File %s does not exist.'), DC_RC_PATH)); }
		# Can we write config.php
		if (!is_writable(dirname(DC_RC_PATH))) { throw new Exception(sprintf(__('Cannot write %s file.'),DC_RC_PATH)); }
		# Check append file
		$append_file = dirname(__FILE__).'/append.config.in';
		if (!is_file($append_file)) { throw new Exception(sprintf(__('File %s does not exist.'), $append_file)); }
		# Backup original config.php
		@copy(DC_RC_PATH, DC_RC_PATH.'.beforeDcFree.bak');
		# Erase old in config.php
		$config_file = rtrim(preg_replace('/'.self::CONFIG_BEGIN.'.*'.self::CONFIG_END.'/s', '', @file_get_contents(DC_RC_PATH)));
		$this->debugLog('config after erase DCFREE', $config_file);
		/* # Erase END MARK of php in config.php "?>" */
		$config_file = rtrim(preg_replace('/\?>$/s', "\n", $config_file));
		$this->debugLog('config after erase "?>"', $config_file);
		# Add new code to config.php
		@file_put_contents(DC_RC_PATH, $config_file."\n".self::CONFIG_BEGIN.@file_get_contents($append_file).self::CONFIG_END);
	}

	# specific actions for uninstall
	protected function uninstallActions() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		# clean config.php
		@copy(DC_RC_PATH, DC_RC_PATH.'.afterDcFree.bak');
		# Erase old in config.php
		$config_file = rtrim(preg_replace('/'.self::CONFIG_BEGIN.'.*'.self::CONFIG_END.'/s', '', @file_get_contents(DC_RC_PATH)));
		/* # Erase END MARK of php in config.php "?>" */
		$config_file = preg_replace('/\?>$/s', "\n", $config_file);
		# Add new code to config.php
		@file_put_contents(DC_RC_PATH, $config_file);
		return true;
	}

	# actions _admin file
	public function _admin() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		# Update fix
		$scope = 'global';
		if($this->settings('updateNoBackup', null, $scope)) {
			if(basename($_SERVER['PHP_SELF']) == 'update.php') {
				if(empty($_GET['step'])) {
					dcPage::addWarningNotice('message', __('The backup is Inhibited (limiting the file size to 1MB).'));	// Add information notice
				} elseif($_GET['step'] == 'backup') {
					//http::redirect('update.php?step=unzip');	// Skip the backup step
					$_GET['step'] = 'unzip';					// Skip the backup step
				}
			}
		}
	}

	# actions _config file
	public function _config() {
		if(!defined('DC_CONTEXT_ADMIN') || !$this->core->auth->isSuperAdmin()) { return; }
		$scope = 'global';
		if (isset($_POST['save'])) {
			try {
				$this->settings('updateNoBackup', !empty($_POST['updateNoBackup']), $scope);
				$this->core->blog->triggerBlog();
				dcPage::addSuccessNotice(__('Configuration successfully updated.'));
			} catch(exception $e) {
				//$this->core->error->add($e->getMessage());
				$this->core->error->add(__('Unable to save the configuration'));
			}
			if(!empty($_GET['redir']) && strpos($_GET['redir'], 'p='.$this->info('id')) === false) {
				$this->core->error->add(__('Redirection not found'));
				$this->core->adminurl->redirect('admin.home');
			}
			http::redirect($_REQUEST['redir']);
		}
		echo
			'<p class="anchor-nav"><span class="warning">'.__('The upgrade option is global (Applies to all blogs)').'</span></p>
			<div class="fieldset">
				<h3 class="pretty-title">'.__('Parameters').'</h3>
				<p><label class="classic" for="updateNoBackup">'.form::checkbox('updateNoBackup','1',$this->settings('updateNoBackup', null, $scope)).__('Inhibits the backup for the update').'</label></p>
				<p class="form-note">'.__('Inhibits the backup for the update (limiting the file size to 1MB).').'</p>
			</div>
			<div class="fieldset">
				<h3 class="pretty-title">'.__('Configuration information').'</h3>
				<p>
					<img title="status" alt="status" src="'.($this->info('_patchs')['fileunzip'] ? 'images/check-on.png' : 'images/check-off.png').'" />
					&nbsp;&nbsp;'.__('Overloading the class fileunzip').'
				</p>
			</div>
			';
	}

	# specifics plugin functions 
	public static function getPatchs() {
		return array_keys(array_filter($GLOBALS['core']->dcFree->info('_patchs')));
	}

}
