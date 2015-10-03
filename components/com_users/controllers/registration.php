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
 * Registration controller class for Users.
 *
 * @since  1.6
 */
class UsersControllerRegistration extends UsersController
{
	/**
	 * Method to activate a user.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function activate()
	{
		$user  	 = JFactory::getUser();
		$input 	 = JFactory::getApplication()->input;
		$uParams = JComponentHelper::getParams('com_users');

		// Check for admin activation. Don't allow non-super-admin to delete a super admin
		if ($uParams->get('useractivation') != 2 && $user->get('id'))
		{
			$this->setRedirect('index.php');

			return true;
		}

		// If user registration or account activation is disabled, throw a 403.
		if ($uParams->get('useractivation') == 0 || $uParams->get('allowUserRegistration') == 0)
		{
			JError::raiseError(403, JText::_('JLIB_APPLICATION_ERROR_ACCESS_FORBIDDEN'));

			return false;
		}

		$model = $this->getModel('Registration', 'UsersModel');
		$token = $input->getAlnum('token');

		// Check that the token is in a valid format.
		if ($token === null || strlen($token) !== 32)
		{
			JError::raiseError(403, JText::_('JINVALID_TOKEN'));

			return false;
		}

		// Attempt to activate the user.
		$return = $model->activate($token);

		// Check for errors.
		if ($return === false)
		{
			// Redirect back to the homepage.
			$this->setMessage(JText::sprintf('COM_USERS_REGISTRATION_SAVE_FAILED', $model->getError()), 'warning');
			$this->setRedirect('index.php');

			return false;
		}

		$useractivation = $uParams->get('useractivation');

		// Redirect to the login screen.
		if ($useractivation == 0)
		{
			$this->setMessage(JText::_('COM_USERS_REGISTRATION_SAVE_SUCCESS'));
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
		}
		elseif ($useractivation == 1)
		{
			$this->setMessage(JText::_('COM_USERS_REGISTRATION_ACTIVATE_SUCCESS'));
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
		}
		elseif ($return->getParam('activate'))
		{
			$this->setMessage(JText::_('COM_USERS_REGISTRATION_VERIFY_SUCCESS'));
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=registration&layout=complete', false));
		}
		else
		{
			$this->setMessage(JText::_('COM_USERS_REGISTRATION_ADMINACTIVATE_SUCCESS'));
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=registration&layout=complete', false));
		}

		return true;
	}

	/**
	 * Method to register a user.
	 *
	 * @return  boolean  True on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function register()
	{
		// Check for request forgeries.
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// If registration is disabled - Redirect to login page.
		if (JComponentHelper::getParams('com_users')->get('allowUserRegistration') == 0)
		{
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));

			return false;
		}

		$app   = JFactory::getApplication();
		$model = $this->getModel('Registration', 'UsersModel');

		// Get the user data.
		//$requestData = $this->input->post->get('jform', array(), 'array');
		
		$name = JRequest::getVar("name");
		$email1 = JRequest::getVar("email");
		$email2 = JRequest::getVar("email");
		$username = JRequest::getVar("email");
		$password1 = JRequest::getVar("password1");
		$password2 = JRequest::getVar("password2");
		$gender = JRequest::getVar("gender");
		$dob = JRequest::getVar("dob");
		$address = JRequest::getVar("address");
		$postal_code = JRequest::getVar("postal_code");
		$city = JRequest::getVar("city");
		
		$requestData['name'] 		= $name;
		$requestData['username'] 	= $username;
		$requestData['email1'] 		= $email1;
		$requestData['email2'] 		= $email2;
		$requestData['password1'] 	= $password1;
		$requestData['password2'] 	= $password2;
		$requestData['profile']['gender'] 		= $gender;
		$requestData['profile']['dob'] 			= $dob;
		$requestData['profile']['address'] 		= $address;
		$requestData['profile']['postal_code'] 	= $postal_code;
		$requestData['profile']['city'] 		= $city;
		
		
		$_FILES['jform']['name']['profilepicture']['file'] = $_FILES['picture']['name'];
		$_FILES['jform']['type']['profilepicture']['file'] = $_FILES['picture']['type'];
		$_FILES['jform']['tmp_name']['profilepicture']['file'] = $_FILES['picture']['tmp_name'];
		$_FILES['jform']['error']['profilepicture']['file'] = $_FILES['picture']['error'];
		$_FILES['jform']['size']['profilepicture']['file'] = $_FILES['picture']['size'];
		
		// Validate the posted data.
		$form = $model->getForm();

		if (!$form)
		{
			JError::raiseError(500, $model->getError());

			return false;
		}

		$data = $model->validate($form, $requestData);

		// Check for validation errors.
		if ($data === false)
		{
			//T.Trung
			$result['result'] = 0;
			die(json_encode($result));
			//T.Trung end
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
			$app->setUserState('com_users.registration.data', $requestData);

			// Redirect back to the registration screen.
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=registration', false));

			return false;
		}

		// Attempt to save the data.
		$return = $model->register($data);
		//T.Trung
		if($return == true){
			$result['result'] = 1;
			die(json_encode($result));
		} else {
			$result['result'] = 0;
			die(json_encode($result));
		}
		//T.Trung end
		// Check for errors.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_users.registration.data', $data);

			// Redirect back to the edit screen.
			$this->setMessage($model->getError(), 'warning');
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=registration', false));

			return false;
		}

		// Flush the data from the session.
		$app->setUserState('com_users.registration.data', null);

		// Redirect to the profile screen.
		if ($return === 'adminactivate')
		{
			$this->setMessage(JText::_('COM_USERS_REGISTRATION_COMPLETE_VERIFY'));
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=registration&layout=complete', false));
		}
		elseif ($return === 'useractivate')
		{
			$this->setMessage(JText::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE'));
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=registration&layout=complete', false));
		}
		else
		{
			$this->setMessage(JText::_('COM_USERS_REGISTRATION_SAVE_SUCCESS'));
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
		}

		return true;
	}
}
