<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT . '/controller.php';

/**
 * Profile controller class for Users.
 *
 * @since  1.6
 */
class UsersControllerProfile extends UsersController
{
	/**
	 * Method to check out a user for editing and redirect to the edit form.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	public function edit()
	{
		$app         = JFactory::getApplication();
		$user        = JFactory::getUser();
		$loginUserId = (int) $user->get('id');

		// Get the previous user id (if any) and the current user id.
		$previousId = (int) $app->getUserState('com_users.edit.profile.id');
		$userId     = $this->input->getInt('user_id', null, 'array');

		// Check if the user is trying to edit another users profile.
		if ($userId != $loginUserId)
		{
			JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));

			return false;
		}

		$cookieLogin = $user->get('cookieLogin');

		// Check if the user logged in with a cookie
		if (!empty($cookieLogin))
		{
			// If so, the user must login to edit the password and other data.
			$app->enqueueMessage(JText::_('JGLOBAL_REMEMBER_MUST_LOGIN'), 'message');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));

			return false;
		}

		// Set the user id for the user to edit in the session.
		$app->setUserState('com_users.edit.profile.id', $userId);

		// Get the model.
		$model = $this->getModel('Profile', 'UsersModel');

		// Check out the user.
		if ($userId)
		{
			$model->checkout($userId);
		}

		// Check in the previous user.
		if ($previousId)
		{
			$model->checkin($previousId);
		}

		// Redirect to the edit screen.
		$this->setRedirect(JRoute::_('index.php?option=com_users&view=profile&layout=edit', false));

		return true;
	}

	/**
	 * Method to save a user's profile data.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function save()
	{
		// Check for request forgeries.
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app    = JFactory::getApplication();
		$model  = $this->getModel('Profile', 'UsersModel');
		$user   = JFactory::getUser();
		$userId = (int) $user->get('id');

		// Get the user data.
		//$data = $app->input->post->get('jform', array(), 'array');

		$user_id = JRequest::getVar("user_id");
		$name = JRequest::getVar("name", "");
		$email1 = JRequest::getVar("email");
		$email2 = JRequest::getVar("email");
		$username = JRequest::getVar("email");
		$password1 = JRequest::getVar("password1");
		$password2 = JRequest::getVar("password2");
		$gender = JRequest::getVar("gender");
		$dob = JRequest::getVar("dob");
		$address = JRequest::getVar("address", "");
		$postal_code = JRequest::getVar("postal_code");
		$city = JRequest::getVar("city", "");
		$remove_picture = JRequest::getVar("remove_picture", NULL);
		
		$data['id'] 		= $user_id;
		$data['name'] 		= $name;
		$data['username'] 	= $username;
		$data['email1'] 	= $email1;
		$data['email2'] 	= $email2;
		$data['password1'] 	= $password1;
		$data['password2'] 	= $password2;
		$data['profile']['gender'] 		= $gender;
		$data['profile']['dob'] 			= $dob;
		$data['profile']['address'] 		= $address;
		$data['profile']['postal_code'] 	= $postal_code;
		$data['profile']['city'] 		= $city;
		$_POST['jform']['profilepicture']['file']['remove'] = $remove_picture;
		
		if(isset($_FILES['picture'])){
			$_FILES['jform']['name']['profilepicture']['file'] = $_FILES['picture']['name'];
			$_FILES['jform']['type']['profilepicture']['file'] = $_FILES['picture']['type'];
			$_FILES['jform']['tmp_name']['profilepicture']['file'] = $_FILES['picture']['tmp_name'];
			$_FILES['jform']['error']['profilepicture']['file'] = $_FILES['picture']['error'];
			$_FILES['jform']['size']['profilepicture']['file'] = $_FILES['picture']['size'];
		}
		
		// Force the ID to this user.
		//$data['id'] = $userId;

		// Validate the posted data.
		/*$form = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());

			return false;
		}*/

		// Validate the posted data.
		/*$data = $model->validate($form, $data);

		// Check for errors.
		if ($data === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_users.edit.profile.data', $data);

			// Redirect back to the edit screen.
			$userId = (int) $app->getUserState('com_users.edit.profile.id');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=profile&layout=edit&user_id=' . $userId, false));

			return false;
		}*/

		// Attempt to save the data.
		$return = $model->save($data);
		//T.Trung
		if($return == false){
			$result['result'] = 0;
			$result['error'] = "Update fail";
			
			die(json_encode($result));
		} else {
			$user = JFactory::getUser($user_id);
			$userProfile = JUserHelper::getProfile($user_id);
			
			$result['result'] = 1;
			$result['error'] = "";
			$result['user_id'] = $user->id;
			$result['name'] = $user->name;
			$result['email'] = $user->email;
			$result['gender'] = $userProfile->profile["gender"];
			$result['dob'] = JHtml::_('date', $userProfile->profile["dob"], 'd-m-Y');
			$result['address'] = $userProfile->profile["address"];
			$result['postal_code'] = $userProfile->profile["postal_code"];
			$result['city'] = $userProfile->profile["city"];
			if($userProfile->profilepicture["file"]){
				$result['picture'] = JURI::base()."media/plg_user_profilepicture/images/original/".$userProfile->profilepicture["file"];
			} else {
				$result['picture'] = "";
			}
			$db = JFactory::getDBO();
			$db->setQuery("SELECT facebook_id FROM #__users WHERE id = ".$user_id);
			$result['facebook_id'] = $db->loadResult();
			
			die(json_encode($result));
		}
		//T.Trung end
		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_users.edit.profile.data', $data);

			// Redirect back to the edit screen.
			$userId = (int) $app->getUserState('com_users.edit.profile.id');
			$this->setMessage(JText::sprintf('COM_USERS_PROFILE_SAVE_FAILED', $model->getError()), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=profile&layout=edit&user_id=' . $userId, false));

			return false;
		}

		// Redirect the user and adjust session state based on the chosen task.
		switch ($this->getTask())
		{
			case 'apply':
				// Check out the profile.
				$app->setUserState('com_users.edit.profile.id', $return);
				$model->checkout($return);

				// Redirect back to the edit screen.
				$this->setMessage(JText::_('COM_USERS_PROFILE_SAVE_SUCCESS'));

				$redirect = $app->getUserState('com_users.edit.profile.redirect');

				// Don't redirect to an external URL.
				if (!JUri::isInternal($redirect))
				{
					$redirect = null;
				}

				if (!$redirect)
				{
					$redirect = 'index.php?option=com_users&view=profile&layout=edit&hidemainmenu=1';
				}

				$this->setRedirect(JRoute::_($redirect, false));
				break;

			default:
				// Check in the profile.
				$userId = (int) $app->getUserState('com_users.edit.profile.id');

				if ($userId)
				{
					$model->checkin($userId);
				}

				// Clear the profile id from the session.
				$app->setUserState('com_users.edit.profile.id', null);

				$redirect = $app->getUserState('com_users.edit.profile.redirect');

				// Don't redirect to an external URL.
				if (!JUri::isInternal($redirect))
				{
					$redirect = null;
				}

				if (!$redirect)
				{
					$redirect = 'index.php?option=com_users&view=profile&user_id=' . $return;
				}

				// Redirect to the list screen.
				$this->setMessage(JText::_('COM_USERS_PROFILE_SAVE_SUCCESS'));
				$this->setRedirect(JRoute::_($redirect, false));
				break;
		}

		// Flush the data from the session.
		$app->setUserState('com_users.edit.profile.data', null);
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   JModelLegacy  $model      The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		$item = $model->getData();
		$tags = $validData['tags'];

		if ($tags)
		{
			$item->tags = new JHelperTags;
			$item->tags->getTagIds($item->id, 'com_users.user');
			$item->metadata['tags'] = $item->tags;
		}
	}
}
