<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin exportFree for Dotclear 2.
 * Copyright © 2015-2016 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }

# tags begin and end for config patch
define('DC_FREE_CONFIG_BEGIN', "\n# BEGIN Free hosting bootstrap\n\n");
define('DC_FREE_CONFIG_END', "\n# END Free hosting bootstrap\n\n");

__('Adaptation for Free hosting');


class dcFree extends dcPluginHelper29h {

	protected function setDefaultSettings() {
		# create config plugin (TODO: specific settings)
		$this->core->blog->settings->addNamespace($this->plugin_id);
		$this->core->blog->settings->{$this->plugin_id}->put('updateNoBackup', false, 'boolean', __('Update, no backup (filesize >1Mo)'), false, true);
	}
	
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
		@copy(DC_RC_PATH, DC_RC_PATH.'.bak');
		# Erase old code to config.php
		$config_file = preg_replace('/'.DC_FREE_CONFIG_BEGIN.'.*'.DC_FREE_CONFIG_END.'/s', '', @file_get_contents(DC_RC_PATH));
		# Add new code to config.php
		@file_put_contents(DC_RC_PATH, $config_file.DC_FREE_CONFIG_BEGIN.@file_get_contents($append_file).DC_FREE_CONFIG_END);
	}

	protected function uninstallActions() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		# specific actions for uninstall
		# clean config.php
		@copy(DC_RC_PATH, DC_RC_PATH.'.bak');
		@file_put_contents(DC_RC_PATH, preg_replace('/'.DC_FREE_CONFIG_BEGIN.'.*'.DC_FREE_CONFIG_END.'/s', '', @file_get_contents(DC_RC_PATH)));
	}
	
	public function _admin() {
		if(!defined('DC_CONTEXT_ADMIN')) { return; }
		# Update fix
		if($this->settings('updateNoBackup')) {
			if(basename($_SERVER['PHP_SELF']) == 'update.php') {
				if(empty($_GET['step'])) {
					dcPage::addWarningNotice('message', __('The backup is Inhibited (limiting the file size to 1MB).'));	// Add information notice
				} elseif($_GET['step'] == 'backup') {
					//http::redirect('update.php?step=unzip');					// Skip the backup step
					$_GET['step'] = 'unzip';
				}
			}
		}
	}

	public function _config() {
		if(!defined('DC_CONTEXT_ADMIN') || !$this->core->auth->isSuperAdmin()) { return; }
		$scope = $this->configScope();
		if (isset($_POST['save'])) {
			try {
				//$this->settings('enabled', !empty($_POST['enabled']), $scope);
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
			$this->configBaseline($scope, false).
			'<div class="fieldset">
				<h3>'.__('Parameters').'</h3>
				<p><label class="classic" for="updateNoBackup">'.form::checkbox('updateNoBackup','1',$this->settings('updateNoBackup', null, $scope)).__('Inhibits the backup for the update').'</label></p>
				<p class="form-note">'.__('Inhibits the backup for the update (limiting the file size to 1MB).').'</p>
			</div>
			';
	}

}
