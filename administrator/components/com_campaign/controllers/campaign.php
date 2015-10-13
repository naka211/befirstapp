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
use Gomoob\Pushwoosh\Model\Request\RegisterDeviceRequest;
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
		
		$tmp = array();
		if($gender != 3){
			$tmp = array();
			$db->setQuery("SELECT user_id, profile_value as gender FROM #__user_profiles WHERE profile_key = 'profile.gender'");
			$users = $db->loadObjectList();
			for($i=0; $i<count($users); $i++){
				$users[$i]->gender = substr($users[$i]->gender, 1, -1);
				if((int)$users[$i]->gender == $gender){
					array_push($tmp, $users[$i]->user_id);
				}
			}
		}
		
		$tmp_str = implode(",", $tmp);
		$tmp1 = array();
		$db->setQuery("SELECT user_id, profile_value as code FROM #__user_profiles WHERE profile_key = 'profile.postal_code' AND user_id IN (".$tmp_str.")");
		$users = $db->loadObjectList();
		for($i=0; $i<count($users); $i++){
			$users[$i]->code = substr($users[$i]->code, 1, -1);
			if((int)$users[$i]->code <= $to_zip && (int)$users[$i]->code >= $from_zip){
				array_push($tmp1, $users[$i]->user_id);
			}
		}
		
		$tmp1_str = implode(",", $tmp1);
		$tmp2 = array();
		$db->setQuery("SELECT user_id, profile_value as dob FROM #__user_profiles WHERE profile_key = 'profile.dob' AND user_id IN (".$tmp1_str.")");
		$users = $db->loadObjectList();
		$year = date("Y");
		for($i=0; $i<count($users); $i++){
			$users[$i]->dob = substr($users[$i]->dob, 1, -1);
			$t = explode("-", $users[$i]->dob);
			$yob = $t[0];
			$old = $year - $yob;
			if((int)$old <= $to_age && (int)$old >= $from_age){
				array_push($tmp2, $users[$i]->user_id);
			}
		}
		
		
		$tmp2_str = implode(",", $tmp2);
		$db->setQuery("SELECT user_id, token, hw_id, type FROM #__users_token WHERE user_id IN (".$tmp2_str.")");
		$devices = $db->loadObjectList();
		foreach($devices as $device){
			$device_arr[] = "(".$device->user_id.", '".$device->token."', '".$device->hw_id."', '".$device->type."', ".$campaign_id.")";
		}
		
		$db->setQuery("INSERT INTO #__campaign_push (user_id, token, hw_id, type, campaign_id) VALUES ".implode(",", $device_arr).";");
		$db->execute();
		
		foreach($devices as $device){
			// Creates the request instance
			$request = RegisterDeviceRequest::create()
				->setDeviceType(DeviceType::iOS())
				->setHwid($device->hw_id)
				->setLanguage('da')
				->setPushToken($device->token)
				->setTimezone(3600);
			
			// Call the '/registerDevice' Web Service
			$response = $pushwoosh->registerDevice($request);
			
			if($response->isOk()) {
				print 'Ok, operation successful.';
			} else {
				die($response->getStatusMessage());
				print 'Oups, the operation failed :-('; 
				print 'Status code : ' . $response->getStatusCode();
				print 'Status message : ' . $response->getStatusMessage();
			}
		}
		print_r($devices);exit;
		
	}

}