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
		
		if($gender != 3){
			$gender_filter = '* T("gender", EQ, '.$gender.')';
		}
		
		$url = 'https://cp.pushwoosh.com/json/1.3/createTargetedMessage';
		$send['request'] = array('auth' => '8PaXOfTn9dzkNuqiMmup9jcmAKDppghCgAgvKqG5u0ArjTBgedOhVxMtzZIT0tibOUFJ3oPilAY1gWbSIt4E', 'send_date'=>'now', 'content'=>'You have new campaign', 'devices_filter'=>'A("64BD1-55924", ["Android"]) * T("age", BETWEEN, ['.$from_age.', '.$to_age.']) * T("postal_code", BETWEEN, ['.$from_zip.', '.$to_zip.']) '.$gender_filter);
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
		print "[PW] request: $request\n";
        print "[PW] response: $response\n";
        print "[PW] info: " . print_r($info, true);
		exit;
		$db->setQuery("UPDATE #__campaign SET push = 1 WHERE id = ".$campaign_id);
		$db->execute();
		
		$this->setRedirect(JRoute::_('index.php?option=com_campaign&view=campaigns'), "Push successfully");
		
	}

}