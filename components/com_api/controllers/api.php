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
						"user_id" => $user->id,
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
	
	public function logout(){
		$user_id = JRequest::getVar("user_id");
		$token = JRequest::getVar("token");
		
		$db = JFactory::getDBO();
		$q = "DELETE FROM #__users_token WHERE user_id = ".(int)$user_id." AND token = '".$token."'";
		$db->setQuery($q);
		if($db->query()){
			$data = array("result" => 1);
		} else {
			$data = array("result" => 0);
		}
		die(json_encode($data));
	}
	
	public function change_password(){
		$email = JRequest::getVar("email");
		
		$new_pass = $this->_generateRandomString();
		
		$app = JFactory::getApplication();
		$mailfrom = $app->get('mailfrom');
		$fromname = $app->get('fromname');
		$sitename = $app->get('sitename');
		$body   = "Hi user, \r\n\r\n This is your new password: ".$new_pass." \r\n\r\n Be First App";
			
		$mail = JFactory::getMailer();
		$mail->addRecipient($email);
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($sitename . ': New password');
		$mail->setBody($body);
		$sent = $mail->Send();
		if($sent){
			jimport('joomla.user.helper');
			
			$db = JFactory::getDBO();
			$pass = JUserHelper::hashPassword($new_pass);
			$db->setQuery("UPDATE #__users SET password = '".$pass."' WHERE email = '".$email."'");
			if($db->query()){
				$result = array("result" => 1);
			} else {
				$result = array("result" => 0);
			}
		} else {
			$result = array("result" => 0);
		}
		die(json_encode($result));
	}
	
	private function _generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
	
	public function get_term(){
		$db = JFactory::getDBO();
		$db->setQuery("SELECT introtext FROM #__content WHERE id = 1");
		$result = array("text" => $db->loadResult());
		die(json_encode($result));
	}
	
	public function get_campaigns(){
		$user_id = JRequest::getVar("user_id");
		$db = JFactory::getDBO();
		$q = "SELECT id, name, campaign_image, reward, published FROM #__campaign ORDER BY published DESC, id DESC LIMIT 0,20";
		$db->setQuery($q);
		$campaigns = $db->loadAssocList();
		$i = 0;
		foreach($campaigns as $campaign){
			$campaigns[0]['campaign_image'] = JURI::base().$campaign['campaign_image'];
			$i++;
		}
		die(json_encode($campaigns));
	}
	
	public function facebook_login(){
		$facebook_id = JRequest::getVar("facebook_id");
		$email = JRequest::getVar("email");
		$name = JRequest::getVar("name");
		$gender = JRequest::getVar("gender", "");
		$dob = JRequest::getVar("dob", "");
		$tmp = explode("-", $dob);
		$dob = $tmp[2]."-".$tmp[1]."-".$tmp[0]." 00:00:00";
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT id FROM #__users WHERE facebook_id = '".$facebook_id."'");
		$id = $db->loadResult();
		$result = array("result" => 1);
		if($id){
			$db->setQuery("UPDATE #__users SET name = '".$name."' WHERE facebook_id='".$facebook_id."'");
			if(!$db->execute()){
				$result = array("result" => 0);
				die(json_encode($result));
			}
			
			$db->setQuery("UPDATE #__user_profiles SET profile.value = ".$gender." WHERE user_id=".$id." AND profile_key = 'profile.gender'");
			if(!$db->execute()){
				$result = array("result" => 0);
				die(json_encode($result));
			}
			
			$db->setQuery("UPDATE #__user_profiles SET profile.value = ".$dob." WHERE user_id=".$id." AND profile_key = 'profile.dob'");
			if(!$db->execute()){
				$result = array("result" => 0);
				die(json_encode($result));
			}
		} else {
			$db->setQuery("INSERT INTO #__users (email, name, facebook_id) VALUES ('".$email."', '".$name."', '".$facebook_id."')");
			if(!$db->execute()){
				$result = array("result" => 0);
				die(json_encode($result));
			}
			
			$db->setQuery("INSERT INTO #__user_profiles VALUES ('".$db->insertid()."', 'profile.gender', '".$gender."', 1), ('".$db->insertid()."', 'profile.dob', '".$dob."', 2);");
			if(!$db->execute()){
				$result = array("result" => 0);
				die(json_encode($result));
			}
		}
		die(json_encode($result));
	}
}
