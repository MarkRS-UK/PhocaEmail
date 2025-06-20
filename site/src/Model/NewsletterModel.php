<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\Component\Phocaemail\Site\Model;

\defined('_JEXEC') or die();

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Phoca\Component\Phocaemail\Administrator\Helper\EmailHelper;
use Phoca\Component\Phocaemail\Administrator\Helper\SendnewsletteremailHelper;
use Phoca\Component\Phocaemail\Administrator\Table\SubscriberTable;

class NewsletterModel extends BaseDatabaseModel
{

/*    public function getTable($name = 'newsletters', $prefix = '', $options = []) {
		return parent::getTable($name, $prefix, $options);
	}*/

	public function storeSubscriber( $name, $email, $privacy, $mailinglist = array())
	{
		Session::checkToken( 'request' ) or jexit(Text::_('JINVALID_TOKEN'));

		$params 	= ComponentHelper::getParams('com_phocaemail') ;
		$app		= Factory::getApplication();
		$message	= '';

		$data['name'] 			= $name;
		$data['email']			= $email;
		$data['privacy']		= $privacy;
		$data['date'] 			= gmdate('Y-m-d H:i:s');
		$data['date_register'] 	= gmdate('Y-m-d H:i:s');
		$data['token'] 		= EmailHelper::getToken();
		$data['active'] 	= 0;
		$data['published'] 	= 1;
		$data['hits'] 		= 0;
		$data['type'] 		= 1;// Phoca Email

		// Test - if there is active user, inactive user with many requests,
		$query = 'SELECT a.id, a.active, a.hits'
			. ' FROM #__phocaemail_subscribers AS a'
			. ' WHERE a.email = '.$this->_db->quote($data['email'])
			. ' LIMIT 1';
		$this->_db->setQuery( $query );
		$user = $this->_db->loadObject();

		// X) ACTIVE USER
		if (isset($user->active) && $user->active == 1) {
			$message = Text::_('COM_PHOCAEMAIL_YOUR_SUBSCRIPTION_IS_ACTIVE');
			$app->enqueueMessage($message, 'message');
			return false;
		}

		// X) UPDATE HITS - ATTEMPTS
		if (isset($user->hits) && (int)$user->hits > 0) {
			$user->hits++;// This attempts must be counted
			$data['hits'] = (int)$user->hits;
		} else {
			$data['hits'] = 1;
		}

		// X) NOT ACTIVE BUT STORED IN DATABASE
		$allowedHits = (int)$params->get('count_subscription', 5);

		if (isset($user->hits) && (int)$user->hits > (int)$allowedHits) {
			$message = Text::_('COM_PHOCAEMAIL_YOUR_SUBSCRIPTION_IS_BLOCKED_PLEASE_CONTACT_ADMINISTRATOR');
			$app->enqueueMessage($message, 'error');
			return false;
		}

		// X) USER EXISTS BUT IS INACTIVE AND ALLOWED TO SUBSCRIBE
		if (isset($user->active) && (int)$user->active != 1 && isset($user->id) && (int)$user->id > 0) {
			$data['id'] = (int)$user->id;
		}

		// X) SEEMS LIKE USER IS NOT IN DATABASE, ADD IT - user id will be automatically created
		// ... ok

		// X) IF REGISTERED USER - ASSIGN AN ACCOUNT TO HIM/HER
		$query = 'SELECT u.id'
			. ' FROM #__users AS u'
			. ' WHERE u.email = '.$this->_db->quote($data['email'])
			. ' LIMIT 1';
		$this->_db->setQuery( $query );
		$registeredUser = $this->_db->loadObject();
		if (isset($registeredUser->id) && $registeredUser->id > 0) {
			$data['userid'] = (int)$registeredUser->id;
		}

//		$row = $this->getTable('phocaemailsubscriber');
		$row = new SubscriberTable($this->_db); // $this->getTable('Subscribers', 'Administrator');

		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (!empty($mailinglist) && (int)$row->id > 0) {
			SendnewsletteremailHelper::storeLists((int)$row->id, $mailinglist, '#__phocaemail_subscriber_lists', 'id_subscriber');
		}

		return true;

	}

	public function getNewsletter($nToken)
	{
		$query = 'SELECT a.message_html, a.token, a.url'
			. ' FROM #__phocaemail_newsletters AS a'
			. ' WHERE a.token = '.$this->_db->quote($nToken)
			. ' LIMIT 1';
		$this->_db->setQuery( $query );
		$newsletter = $this->_db->loadObject();
		return $newsletter;
	}

	public function getSubscriber($uToken) {

		$query = 'SELECT a.id, a.name, a.email, a.token'
			. ' FROM #__phocaemail_subscribers AS a'
			. ' WHERE a.token = '.$this->_db->quote($uToken)
			. ' LIMIT 1';
		$this->_db->setQuery( $query );
		$newsletter = $this->_db->loadObject();
		return $newsletter;
	}
}
