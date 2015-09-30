<?php

/**
 * @version     1.0.0
 * @package     com_campaign
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Nguyen Thanh Trung <nttrung211@yahoo.com> - 
 */
defined('_JEXEC') or die;

class CampaignFrontendHelper
{
	

	/**
	 * Get an instance of the named model
	 *
	 * @param string $name
	 *
	 * @return null|object
	 */
	public static function getModel($name)
	{
		$model = null;

		// If the file exists, let's
		if (file_exists(JPATH_SITE . '/components/com_campaign/models/' . strtolower($name) . '.php'))
		{
			require_once JPATH_SITE . '/components/com_campaign/models/' . strtolower($name) . '.php';
			$model = JModelLegacy::getInstance($name, 'CampaignModel');
		}

		return $model;
	}
}
