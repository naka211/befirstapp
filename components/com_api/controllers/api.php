<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class ApiControllerApi extends JControllerLegacy {

    public function login() {
		$app    = JFactory::getApplication();
		$username = JRequest::getVar("username");
		$password = JRequest::getVar("password");
		$type = JRequest::getVar("type");
		$token = JRequest::getVar("token");
		
		$credentials["username"] = $username;
		$credentials["password"] = $password;
		
		$options['remember'] = false;
		$options['return']   = '';
		
		$result = $app->login($credentials, $options);
		if($result == true){
			$db = JFactory::getDBO();
			
			$user = JFactory::getUser();
			$userProfile = JUserHelper::getProfile( $user->id );
			
			$db->setQuery("INSERT INTO #__users_token (user_id, token, type) VALUES (".$user->id.", '".$token."', '".$type."')");
			//$db->query();
			
			$data = array("result" => 1,
						"userid" => $user->id,
						"name" => $user->name,
						"email" => $user->email,
						"gender" => $userProfile->profile["gender"],
						"dob" => JHtml::_('date', $userProfile->profile["dob"], 'd-m-Y'),
						"address" => $userProfile->profile["address"],
						"postal_code" => $userProfile->profile["postal_code"],
						"city" => $userProfile->profile["city"],
						"picture" => JURI::base()."media/plg_user_profilepicture/images/original/".$userProfile->profilepicture["file"]
			);
			//
		} else {
			$data = array("result" => 0);
		}
        die(json_encode($data));
    }
}
