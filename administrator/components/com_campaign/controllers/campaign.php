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
	
	public function postSaveHook($model, $validData)
	{
		$item = $model->getItem();
		$campaign_id = $item->get('id');
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT name, gender, from_age, to_age, from_zipcode, to_zipcode FROM #__campaign WHERE id = ".$campaign_id);
		$boudaries = $db->loadObject();
		
		$gender = $boudaries->gender;
		$from_age = $boudaries->from_age;
		$to_age = $boudaries->to_age;
		$from_zip = $boudaries->from_zipcode;
		$to_zip = $boudaries->to_zipcode;
		
		$filter = "";
		if($gender != 3){
			$filter .= '* T("gender", EQ, '.$gender.') ';
		}
		
		if($from_age != 0 && $to_age != 0){
			$filter .= '* T("age", BETWEEN, ['.$from_age.', '.$to_age.']) ';
		}
		
		if($from_zip != "" || $to_zip != ""){
			$filter .= '* T("postal_code", BETWEEN, ['.$from_zip.', '.$to_zip.']) ';
		}
		
		$url = 'https://cp.pushwoosh.com/json/1.3/createTargetedMessage';
		$send['request'] = array('auth' => '8PaXOfTn9dzkNuqiMmup9jcmAKDppghCgAgvKqG5u0ArjTBgedOhVxMtzZIT0tibOUFJ3oPilAY1gWbSIt4E', 'send_date'=>'now', 'content'=>'You have new campaign: '.$boudaries->name, 'devices_filter'=>'A("64BD1-55924") '.$filter);

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
		
		$db->setQuery("UPDATE #__campaign SET push = 1 WHERE id = ".$campaign_id);
		$db->execute();
	}
}