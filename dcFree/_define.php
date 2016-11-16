<?php
/* -- BEGIN LICENSE BLOCK -----------------------------------------------------
 * This file is part of plugin dcFree for Dotclear 2.
 * Copyright © 2016 Gvx
 * Licensed under the GPL version 2.0 license.
 * (http://www.gnu.org/licenses/old-licenses/gpl-2.0.html)
 * -- END LICENSE BLOCK -----------------------------------------------------*/
if(!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */			'dcFree',
	/* Description*/	'Adaptation for Free hosting',
	/* Author */		'Gvx',
	/* Version */		'0.2.0',
	array(
		/* standard plugin options dotclear */
		'permissions'				=>	'admin'
		, 'type'					=>	'plugin'
		, 'priority'				=>	1010
		, 'support'		/* url */	=>	'https://forum.dotclear.org/viewtopic.php?pid=338582'
		, 'details' 	/* url */	=>	'https://bitbucket.org/Gvx_/dcfree'
		, 'requires'	/* id(s) */	=>	array(
			array('core', '2.9')
		)
		/* since dc 2.11 */
		, 'settings'				=> array(
			//'self'		=> ''
			//, 'blog'	=> '#params.id'
			//, 'pref'	=> '#user-options.id'
		)
		/* specific plugin options */
		, '_patchs'					=>	array(
			'fileunzip'	=> (defined('DC_FREE') && function_exists('zip_open'))
		)
	)
);
