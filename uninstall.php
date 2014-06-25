<?php
	/**
	 * This file is called when plugin is being uninstalled
	 */

	if (!defined('WP_UNINSTALL_PLUGIN'))
		exit();

	// Remove all plugin settings from database
	delete_option('cc_vrp_options');
?>