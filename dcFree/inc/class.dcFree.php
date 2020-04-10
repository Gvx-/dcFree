<?php
/**
  * This file is part of dcFree plugin for Dotclear 2.
  *
  * @package Dotclear\plungin\dcFree
  *
  * @author Gvx <g.gvx@free.fr>
  * @copyright © 2015-2020 Gvx
  * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

 if(!defined('DC_RC_PATH')) { return; }

if(!isset($__autoload['dcPluginHelper216'])) { $__autoload['dcPluginHelper216'] = dirname(__FILE__).'/class.dcPluginHelper.php'; }

__('Adaptation for Free hosting');


class dcFree extends dcPluginHelper216 {

	# tags begin and end for config patch
	const CONFIG_BEGIN = "# BEGIN Free hosting bootstrap\n\n";
	const CONFIG_END = "\n# END Free hosting bootstrap";

	# specific actions for install
	protected function installActions($old_version) {
		if(version_compare($old_version, '0.2', '<')) {
			# delete unnecessary settings
			$this->settingDrop('updateNoBackup');
		}
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
		/* # Erase END MARK of php in config.php "?>" */
		$config_file = rtrim(preg_replace('/\?>$/s', "\n", $config_file));
		# Add new code to config.php
		@file_put_contents(DC_RC_PATH, $config_file."\n\n".self::CONFIG_BEGIN.@file_get_contents($append_file).self::CONFIG_END."\n\n");
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

	# actions _config file
	public function _config() {
		if(!defined('DC_CONTEXT_ADMIN') || !$this->core->auth->isSuperAdmin()) { return; }
		$scope = 'global';
		if (isset($_POST['save'])) {
			try {

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
			'<div class="fieldset">
				<h3 class="pretty-title">'.__('Configuration information').'</h3>
				<p>
					<img title="status" alt="status" src="'.($this->info('_patchs')['fileunzip'] ? 'images/check-on.png' : 'images/check-off.png').'" />
					&nbsp;&nbsp;'.__('Overloading the class fileunzip').'
				</p>
			</div>
			<hr />
			'.$this->adminFooterInfo();
	}

	# specifics plugin functions

	/**
	 * getPatchs
	 *
	 * @return string
	 */
	public static function getPatchs() {
		return array_keys(array_filter($GLOBALS['core']->dcFree->info('_patchs')));
	}

}
