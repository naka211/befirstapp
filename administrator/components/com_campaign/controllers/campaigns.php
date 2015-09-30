<?php
/**
 * @version     1.0.0
 * @package     com_campaign
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Nguyen Thanh Trung <nttrung211@yahoo.com> - 
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Campaigns list controller class.
 */
class CampaignControllerCampaigns extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'campaign', $prefix = 'CampaignModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
    
    
	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$input = JFactory::getApplication()->input;
		$pks = $input->post->get('cid', array(), 'array');
		$order = $input->post->get('order', array(), 'array');

		// Sanitize the input
		JArrayHelper::toInteger($pks);
		JArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		JFactory::getApplication()->close();
	}
    
    public function publish(){die('dfgdfgdfg');
		$cid = JRequest::getVar("cid");
		$this->setPublish($cid[0], 1);
		$this->setRedirect("index.php?option=com_campaign&view=campaigns", "Hình đã được chọn");
	}
	
	public function unpublish(){die('fsf');
		$cid = JRequest::getVar("cid");
		$this->setPublish($cid[0], 0);
		$this->setRedirect("index.php?option=com_campaign&view=campaigns", "Hình đã được bỏ chọn");
	}
	
	public function setSpecial($id, $status){
		$db = JFactory::getDBO();
		$db->setQuery("UPDATE #__campaign SET published = $status WhERE id = $id");
		return $db->query();
	}
    
}