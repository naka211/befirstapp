<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

class ApiControllerApi extends JControllerLegacy {

	public function test(){
		die("API is ok!!!");
	}
    public function login() {
		$app    = JFactory::getApplication();
		$username = JRequest::getVar("username");
		$password = JRequest::getVar("password");
		
		$credentials["username"] = $username;
		$credentials["password"] = $password;
		
		$options['remember'] = false;
		$options['return']   = '';
		
		$result = $app->login($credentials, $options);
		if($result == true){
			$db = JFactory::getDBO();
			
			$user = JFactory::getUser();
			$userProfile = JUserHelper::getProfile( $user->id );
			
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
			
		} else {
			$data["result"] = 0;
			$data["error"] = "Email or password is incorrect";
		}
        die(json_encode($data));
    }
	
	public function delete_token(){
		$user_id = JRequest::getVar("user_id");
		$token = JRequest::getVar("token");
		
		$db = JFactory::getDBO();
		$q = "DELETE FROM #__users_token WHERE user_id = ".(int)$user_id." AND token = '".$token."'";
		$db->setQuery($q);
		if($db->query()){
			$return["result"] = 1;
			$return["error"] = "";
		} else {
			$return["result"] = 0;
			$return["error"] = "Can not delete token";
		}
		die(json_encode($return));
	}
	
	public function forgot_password(){
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
				$data["result"] = 0;
				$data["error"] = "Can not update new password";
			}
		} else {
			$data["result"] = 0;
			$data["error"] = "Can not send email";
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
	
	public function facebook_login(){
		$facebook_id = JRequest::getVar("facebook_id");
		$email = JRequest::getVar("email", "");
		$name = JRequest::getVar("name");
		$gender = JRequest::getVar("gender", "");
		$postal_code = JRequest::getVar("postal_code", "");
		$dob = JRequest::getVar("dob", "");
		$tmp = explode("-", $dob);
		$dob = $tmp[2]."-".$tmp[1]."-".$tmp[0]." 00:00:00";
				
		$db = JFactory::getDBO();
		$db->setQuery("SELECT id FROM #__users WHERE email = '".$email."'");
		$isEmail = $db->loadResult();
		if(!$isEmail){
			$db->setQuery("SELECT id FROM #__users WHERE facebook_id = '".$facebook_id."'");
			$id = $db->loadResult();
			$result = array("result" => 1);
			if($id){
				$db->setQuery("UPDATE #__users SET name = '".$name."' WHERE facebook_id='".$facebook_id."'");
				if(!$db->execute()){
					$result['result'] = 0;
					$result['error'] = "Can't update Facebook id";
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
				
				$db->setQuery("UPDATE #__user_profiles SET profile.value = ".$postal_code." WHERE user_id=".$id." AND profile_key = 'profile.postal_code'");
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
				$id = $db->insertid();
				$db->setQuery("INSERT INTO #__user_profiles VALUES ('".$db->insertid()."', 'profile.gender', '".$gender."', 1), ('".$db->insertid()."', 'profile.dob', '".$dob."', 2), ('".$db->insertid()."', 'profile.postal_code', '".$postal_code."', 2);");
				if(!$db->execute()){
					$result = array("result" => 0);
					die(json_encode($result));
				}
				
			}
			$result['result'] = 1;
			$result['user_id'] = $id;
			die(json_encode($result));
		} else {
			$result['result'] = 1;
			$result['user_id'] = $isEmail;
			die(json_encode($result));
		}
	}
	
	public function get_unread_campaigns(){
		$user_id = JRequest::getVar("user_id");
		$db = JFactory::getDBO();
		$q = "SELECT campaign_id FROM #__campaign_users WHERE user_id = ".$user_id." AND viewed = 1";
		$db->setQuery($q);
		$campaign_ids = $db->loadColumn();
		if($campaign_ids){
			$campaign_str = implode(",", $campaign_ids);
			$q = "SELECT * FROM #__campaign WHERE id NOT IN (".$campaign_str.") ORDER BY id DESC";
		} else {
			$q = "SELECT * FROM #__campaign ORDER BY id DESC";
		}
		
		$db->setQuery($q);
		$campaigns = $db->loadAssocList();
		if($campaigns){
			$i = 0;
			foreach($campaigns as $campaign){
				$campaigns[$i]['campaign_image'] = JURI::base().$campaign['campaign_image'];
				$i++;
			}
			$return["result"] = 1;
			$return["data"] = $campaigns;
			$return["error"] = "";
			die(json_encode($return));
		} else {
			$return["result"] = 0;
			$return["data"] = NULL;
			$return["error"] = "Not results";
			die(json_encode($return));
		}
	}
	
	public function get_read_campaigns(){
		$user_id = JRequest::getVar("user_id");
		$page = JRequest::getVar("page", 1);
		$limitstart = ($page-1) * 20;
		$db = JFactory::getDBO();
		$q = "SELECT campaign_id FROM #__campaign_users WHERE user_id = ".$user_id." AND viewed = 1 LIMIT ".$limitstart.", 20";
		$db->setQuery($q);
		$campaign_ids = $db->loadColumn();
		if($campaign_ids){
			$campaign_str = implode(",", $campaign_ids);
			$q = "SELECT * FROM #__campaign WHERE id IN (".$campaign_str.") ORDER BY id DESC";
		
			$db->setQuery($q);
			$campaigns = $db->loadAssocList();
			$i = 0;
			foreach($campaigns as $campaign){
				$campaigns[$i]['campaign_image'] = JURI::base().$campaign['campaign_image'];
				$i++;
			}
			$return["result"] = 1;
			$return["data"] = $campaigns;
			$return["error"] = "";
			die(json_encode($return));
		} else {
			$return["result"] = 0;
			$return["data"] = NULL;
			$return["error"] = "Not results";
			die(json_encode($return));
		}
	}
	
	public function get_winning_campaigns(){
		$user_id = JRequest::getVar("user_id");
		$page = JRequest::getVar("page", 1);
		$limitstart = ($page-1) * 20;
		$db = JFactory::getDBO();
		$q = "SELECT campaign_id FROM #__campaign_users WHERE user_id = ".$user_id." AND win = 1 LIMIT ".$limitstart.", 20";
		$db->setQuery($q);
		$campaign_ids = $db->loadColumn();
		if($campaign_ids){
			$campaign_str = implode(",", $campaign_ids);
			
			$q = "SELECT * FROM #__campaign WHERE id IN (".$campaign_str.") ORDER BY id DESC";
			$db->setQuery($q);
			$campaigns = $db->loadAssocList();
			$i = 0;
			foreach($campaigns as $campaign){
				$campaigns[$i]['campaign_image'] = JURI::base().$campaign['campaign_image'];
				$i++;
			}
			$return["result"] = 1;
			$return["data"] = $campaigns;
			$return["error"] = "";
			die(json_encode($return));
		} else {
			$return["result"] = 0;
			$return["data"] = NULL;
			$return["error"] = "Not results";
			die(json_encode($return));
		}
	}
	
	public function get_campaign_detail(){
		$id = JRequest::getVar("id");
		$db = JFactory::getDBO();
		$db->setQuery("SELECT * FROM #__campaign WHERE id = ".$id);
		$campaign = $db->loadAssoc();
		$campaign['campaign_image'] = JURI::base().$campaign['campaign_image'];
		if($campaign){
			$return["result"] = 1;
			$return["data"] = $campaign;
			$return["error"] = "";
		} else {
			$return["result"] = 0;
			$return["data"] = NULL;
			$return["error"] = "Not results";
		}
		die(json_encode($return));
	}
	
	public function join_campaign(){
		$user_id = JRequest::getVar("user_id");
		$campaign_id = JRequest::getVar("campaign_id");
		
		$db = JFactory::getDBO();
		$db->setQuery("INSERT INTO #__campaign_users (user_id, campaign_id, join_time, viewed, win, viewed_time) VALUES ('".$user_id."', '".$campaign_id."', NOW(), 0, 0, '')");
		if($db->execute()){
			$return["result"] = 1;
			$return["error"] = "";
		} else {
			$return["result"] = 0;
			$return["error"] = "Can't join campaign";
		}
		die(json_encode($result));
	}
	
	public function finish_viewing(){
		$user_id = JRequest::getVar("user_id");
		$campaign_id = JRequest::getVar("campaign_id");
		
		$db = JFactory::getDBO();
		$db->setQuery("SELECT number_of_winners FROM #__campaign WHERE id = ".$campaign_id);
		$nofw = $db->loadResult();
		$db->setQuery("SELECT COUNT(*) FROM #__campaign_users WHERE campaign_id = ".$campaign_id." AND win = 1");
		$nofw_current = $db->loadResult();
		if($nofw_current < $nofw){
			$rank = $nofw_current + 1;
			$db->setQuery("UPDATE #__campaign_users SET viewed = 1, win = 1, viewed_time = NOW(), rank = ".$rank." WHERE user_id = ".$user_id." AND campaign_id = ". $campaign_id);
			$db->execute();
		} else {
			$db->setQuery("SELECT MAX(rank) FROM #__campaign_users WHERE campaign_id = ".$campaign_id);
			$current_rank = $db->loadResult();
			$rank = $current_rank + 1;
			$db->setQuery("UPDATE #__campaign_users SET viewed = 1, win = 0, viewed_time = NOW(), rank = ".$rank." WHERE user_id = ".$user_id." AND campaign_id = ". $campaign_id);
			$db->execute();
		}
		
		$result = array("rank" => $rank);
		die(json_encode($result));
	}
	
	public function add_token(){
		$type = JRequest::getVar("type");
		$token = JRequest::getVar("token");
		$user_id = JRequest::getVar("user_id");
		
		$db->setQuery("INSERT INTO #__users_token (user_id, token, type) VALUES (".$user_id.", '".$token."', '".$type."')");
		if($db->execute()){
			$return["result"] = 1;
			$return["error"] = "";
		} else {
			$return["result"] = 0;
			$return["error"] = "Can not insert new token";
		}
		die(json_encode($return));
	}
	
	public function get_winners(){
		$campaign_id = JRequest::getVar("campaign_id");
		
		$db = JFactory::getDBO();
	
		$q = "SELECT cu.user_id, cu.rank, u.name, cu.win FROM #__campaign_users cu INNER JOIN #__users u ON cu.user_id = u.id WHERE cu.campaign_id = ".$campaign_id." AND cu.win = 1 ORDER BY cu.rank ASC";
		$db->setQuery($q);
		$winners = $db->loadAssocList();
		return $winners;
	}
	
	public function get_my_rank(){
		$campaign_id = JRequest::getVar("campaign_id");
		$user_id = JRequest::getVar("user_id");
		
		$db = JFactory::getDBO();
		$q = "SELECT rank FROM #__campaign_users WHERE campaign_id = ".$campaign_id." AND user_id = ".$user_id;
		$db->setQuery($q);
		$my_rank = $db->loadResult();
		
		$result = array("rank" => $my_rank);
		die(json_encode($result));
	}
	
	public function get_near_me(){
		$campaign_id = JRequest::getVar("campaign_id");
		$user_id = JRequest::getVar("user_id");
		
		$db = JFactory::getDBO();
		$q = "SELECT rank FROM #__campaign_users WHERE campaign_id = ".$campaign_id." AND user_id = ".$user_id;
		$db->setQuery($q);
		$my_rank = $db->loadResult();
		
		$q = "SELECT COUNT(id) as user_total FROM #__campaign_users WHERE campaign_id = ".$campaign_id;
		$db->setQuery($q);
		$user_total = $db->loadResult();
		
		if(($my_rank == 1) && ($my_rank <= $user_total-2)){
			$near = $this->_get_near($campaign_id, 1, 3);
		}
		if(($my_rank == 2) && ($my_rank <= $user_total-2)){
			$near = $this->_get_near($campaign_id, 1, 4);
		}
		if(($my_rank > 2) && ($my_rank >= $user_total-1)){
			$near = $this->_get_near($campaign_id, $my_rank-2, $user_total);
		}

		if(($my_rank > 2) && ($my_rank < $user_total-2)){
			$near = $this->_get_near($campaign_id, $my_rank-2, $my_rank+2);
		}
		
		
		if(($my_rank <= 2) && ($user_total == 2)){
			$near = $this->_get_near($campaign_id, 1, 2);
		}
		
		if(($my_rank == 2) && ($user_total == 3)){
			$near = $this->_get_near($campaign_id, 1, 3);
		}
		return $near;
	}
	
	private function _get_near($campaign_id, $from, $to){
		$rank_arr = array();
		for($i=$from; $i<=$to; $i++){
			$rank_arr[] = $i;
		}
		
		$rank_str = implode(",", $rank_arr);
		
		$db = JFactory::getDBO();
		$q = "SELECT cu.user_id, cu.rank, u.name, cu.win FROM #__campaign_users cu INNER JOIN #__users u ON cu.user_id = u.id WHERE cu.campaign_id = ".$campaign_id." AND cu.rank IN (".$rank_str.") AND viewed = 1 ORDER BY cu.rank ASC";
		$db->setQuery($q);
		$near = $db->loadAssocList();
		
		return $near;
	}
	
	public function get_list(){
		$winners = $this->get_winners();
		$near = $this->get_near_me();
		$list = $winners;
		
		foreach($winners as $winner){
			$win_arr[] = $winner["user_id"];
		}
		foreach($near as $item){
			if(!in_array($item["user_id"], $win_arr)){
				array_push($list, $item);
			}
		}
		
		$db = JFactory::getDBO();
		$i = 0;
		foreach($list as $item){
			$db->setQuery("SELECT profile_value FROM #__user_profiles WHERE user_id = ".$item["user_id"]." AND profile_key = 'profilepicture.file'");
			if($db->loadResult()){
				$list[$i]['picture'] = JURI::base()."media/plg_user_profilepicture/images/original/".$db->loadResult();
			} else {
				$list[$i]['picture'] = "";
			}
			
			$i++;
		}
		if($list){
			$return["result"] = 1;
			$return["data"] = $list;
			$return["error"] = "";
		} else {
			$return["result"] = 0;
			$return["data"] = NULL;
			$return["error"] = "Not result";
		}
		die(json_encode($return));
	}
}
