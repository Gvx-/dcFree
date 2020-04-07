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

if(!defined('DC_FREE')) { define('DC_FREE', 'dcFree'); }

# Zip tools
if(function_exists('zip_open')) {
	$__autoload['fileUnzip'] = dirname(__FILE__).'/class.unzip.php';
}
