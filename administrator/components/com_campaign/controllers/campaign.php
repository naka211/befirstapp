<?php
/**
 * @version     1.0.0
 * @package     com_campaign
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Nguyen Thanh Trung <nttrung211@yahoo.com> - 
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

require_once 'Gomoob'.DIRECTORY_SEPARATOR.'Pushwoosh'.DIRECTORY_SEPARATOR.'IPushwoosh.php';
use Gomoob\Pushwoosh\IPushwoosh;
require_once 'Gomoob'.DIRECTORY_SEPARATOR.'Pushwoosh'.DIRECTORY_SEPARATOR.'ICURLClient.php';
use Gomoob\Pushwoosh\ICURLClient;
require_once 'Gomoob'.DIRECTORY_SEPARATOR.'Pushwoosh'.DIRECTORY_SEPARATOR.'Client'.DIRECTORY_SEPARATOR.'CURLClient.php';
use Gomoob\Pushwoosh\Client\CURLClient;
require_once 'Gomoob'.DIRECTORY_SEPARATOR.'Pushwoosh'.DIRECTORY_SEPARATOR.'Client'.DIRECTORY_SEPARATOR.'Pushwoosh.php';
use Gomoob\Pushwoosh\Client\Pushwoosh;
require_once 'Gomoob'.DIRECTORY_SEPARATOR.'Pushwoosh'.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'Response'.DIRECTORY_SEPARATOR.'AbstractResponse.php';
use Gomoob\Pushwoosh\Model\Response\AbstractResponse;

require_once 'Gomoob'.DIRECTORY_SEPARATOR.'Pushwoosh'.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'Request'.DIRECTORY_SEPARATOR.'UnregisterDeviceRequest.php';
use Gomoob\Pushwoosh\Model\Request\UnregisterDeviceRequest;
require_once 'Gomoob'.DIRECTORY_SEPARATOR.'Pushwoosh'.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'Response'.DIRECTORY_SEPARATOR.'UnregisterDeviceResponse.php';
use Gomoob\Pushwoosh\Model\Response\UnregisterDeviceResponse;

require_once 'Gomoob'.DIRECTORY_SEPARATOR.'Pushwoosh'.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'Request'.DIRECTORY_SEPARATOR.'CreateMessageRequest.php';
use Gomoob\Pushwoosh\Model\Request\CreateMessageRequest;
require_once 'Gomoob'.DIRECTORY_SEPARATOR.'Pushwoosh'.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR.'Response'.DIRECTORY_SEPARATOR.'CreateMessageResponse.php';
use Gomoob\Pushwoosh\Model\Response\CreateMessageResponse;

/**
 * Campaign controller class.
 */
class CampaignControllerCampaign extends JControllerForm
{

    function __construct() {
        $this->view_list = 'campaigns';
        parent::__construct();
    }
	
	function push_notification(){
		
		$campaign_id = JRequest::getVar("campaign_id");
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT gender, from_age, to_age, from_zipcode, to_zipcode FROM #__campaign WHERE id = ".$campaign_id);
		$boudaries = $db->loadObject();
		
		$gender = $boudaries->gender;
		$from_age = $boudaries->from_age;
		$to_age = $boudaries->to_age;
		$from_zip = $boudaries->from_zipcode;
		$to_zip = $boudaries->to_zipcode;
		
		$url = 'https://cp.pushwoosh.com/json/1.3/createMessage';
		$send['request'] = array('application' => '64BD1-55924','auth' => '8PaXOfTn9dzkNuqiMmup9jcmAKDppghCgAgvKqG5u0ArjTBgedOhVxMtzZIT0tibOUFJ3oPilAY1gWbSIt4E','notifications' => array(array('send_date' => 'now',   'content' => 'push notification from website', 'data' => array('custom' => 'json data'),'link' => 'http://pushwoosh.com/')));
		$request = json_encode($send);
	 
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	 
		$response = curl_exec($ch);
		$info = curl_getinfo($ch);
		curl_close($ch);
		
		exit;
		
	}

}