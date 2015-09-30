<?php
/**
 * @version     1.0.0
 * @package     com_recharge
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Nguyen Thanh Trung <nttrung211@yahoo.com> - 
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');
//print_r($_REQUEST);exit;
// Execute the task.
$controller	= JControllerLegacy::getInstance('Api');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
