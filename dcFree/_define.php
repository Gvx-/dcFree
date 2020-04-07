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

$this->registerModule(
	/* Name */			'dcFree',
	/* Description*/	'Adaptation for Free hosting',
	/* Author */		'Gvx',
	/* Version */		'0.3.0-dev-r0010',
	array(
		/* standard plugin options dotclear */
		'permissions'				=>	'admin',
		'type'						=>	'plugin',
		'priority'					=>	1010,
		'support'	/* url */		=>	'https://forum.dotclear.org/viewtopic.php?pid=338582',
		'details' 	/* url */		=>	'https://github.com/Gvx-/dcFree',
		'requires'	/* id(s) */		=>	array(
			array('core', '2.16')
		),
		/* since dc 2.11 */
		/*
		'settings'					=> array(
			//'self'				=> '',
			//'blog'				=> '#params.id',
			//'pref'				=> '#user-options.id'
		),
		//*/
		/* specific plugin options */
		'_class_name'				=>	'dcFree',								// Required: plugin master class name
		'_class_path'				=>	'/inc/class.dcFree.php',				// Required: plugin master class path (relative)
		'_icon_small'				=>	'/inc/icon-small.png',					// Required: plugin small icon path (16*16 px) (relative)
		'_icon_large'				=>	'/inc/icon-large.png',					// Required: plugin large icon path (64*64 px) (relative)
		'_patchs'					=>	array(
			'fileunzip'	=> (defined('DC_FREE') && function_exists('zip_open'))
		)
	)
);
