<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcFree for Dotclear 2.
 * Copyright © 2016 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }

$__autoload['dcPluginHelper29h'] = dirname(__FILE__).'/inc/class.dcPluginHelper.php';
$__autoload['dcFree'] = dirname(__FILE__).'/inc/class.dcFree.php';

# initialization
$core->dcFree = new dcFree(basename(dirname(__FILE__)));
