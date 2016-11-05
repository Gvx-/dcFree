<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcFree for Dotclear 2.
 * Copyright © 2016 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }

if(!defined('DC_FREE')) { define('DC_FREE', 'dcFree'); }

# Zip tools
if(function_exists('zip_open')) {
	$__autoload['fileUnzip'] = dirname(__FILE__).'/class.unzip.php';
}
