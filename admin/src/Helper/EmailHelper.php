<?php
/*
 * @package		Joomla.Framework
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @component Phoca Component
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License version 2 or later;
 */

namespace Phoca\Component\Phocaemail\Administrator\Helper;

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

class EmailHelper
{
	public static function getPath() {
		$path = array();
		$path['path_abs']		= JPATH_ROOT . '/phocaemail/' ;
		$path['path_abs_nods']	= JPATH_ROOT . '/phocaemail' ;
		$path['path_rel']		= 'phocaemail/';
		$path['path_rel_full']	= Uri::base(true) . '/' . $path['path_rel'];

		return $path;
	}


	public static function getPhocaVersion()
	{
		$folder = JPATH_ADMINISTRATOR . '/components/com_phocaemail';
		if (is_dir($folder)) {
			$xmlFilesInDir = Folder::files($folder, '.xml$');
		} else {
			$folder = JPATH_SITE .'/components/com_phocaemail';
			if (is_dir($folder)) {
				$xmlFilesInDir = Folder::files($folder, '.xml$');
			} else {
				$xmlFilesInDir = null;
			}
		}

		$xml_items = array();
		if (!empty($xmlFilesInDir))
		{
			foreach ($xmlFilesInDir as $xmlfile)
			{
				if ($data = Installer::parseXMLInstallFile($folder.'/'.$xmlfile)) {
					foreach($data as $key => $value) {
						$xml_items[$key] = $value;
					}
				}
			}
		}

		if (isset($xml_items['version']) && $xml_items['version'] != '' ) {
			return $xml_items['version'];
		} else {
			return '';
		}
	}

	/**
	 * Method to display multiple select box
	 * @param string $name Name (id, name parameters)
	 * @param array $active Array of items which will be selected
	 * @param int $nouser Select no user
	 * @param string $javascript Add javascript to the select box
	 * @param string $order Ordering of items
	 * @param int $reg Only registered users
	 * @return array of id
	 */

	public static function usersList( $name, $active, $nouser = 0, $javascript = NULL, $order = 'name', $reg = 1 )
	{

		$activeArray = $active;
		$db		= Factory::getDBO();

		$and	= '';
		/*if ( $reg ) {
			// does not include registered users in the list
			$and = ' AND gid > 18';
		}*/

		$query = 'SELECT id AS value, name AS text'
		. ' FROM #__users'
		. ' WHERE block = 0'
		. $and
		. ' ORDER BY '. $order
		;
		$db->setQuery( $query );

		$users = $db->loadObjectList();
		$users = HTMLHelper::_('select.genericlist',   $users, $name, 'class="form-control inputbox" size="4" multiple="multiple"'. $javascript, 'value', 'text', $activeArray );

		return $users;
	}

	public static function newsletterList( )
	{

		$db		= Factory::getDBO();

		$query = 'SELECT a.id AS value, a.title AS text,'
		. ' GROUP_CONCAT(DISTINCT n.id_list) AS mlist'
		. ' FROM #__phocaemail_newsletters AS a'
		. ' LEFT JOIN #__phocaemail_newsletter_lists AS n ON n.id_newsletter = a.id'
		. ' WHERE a.published = 1'
		. ' GROUP BY a.id'
		. ' ORDER BY a.ordering';
		$db->setQuery( $query );

		$newsletters = $db->loadObjectList();

		if (!empty($newsletters)) {
			foreach ($newsletters as $k => $v) {
				if (isset($v->mlist) && $v->mlist != '') {
					$query = 'SELECT id_subscriber'
					. ' FROM #__phocaemail_subscriber_lists'
					. ' WHERE id_list IN ('.$v->mlist.')'
					. ' GROUP BY id_subscriber';
					$db->setQuery( $query );

					$subscribers = $db->loadColumn();

					if (!empty($subscribers)) {
						$newsletters[$k]->slist = new \stdClass();
						$newsletters[$k]->slist = implode(',', $subscribers);
					} else {
						$newsletters[$k]->slist = new \stdClass();
						$newsletters[$k]->slist = null;
					}
				} else {
					// !!!!!!!!!!!!
					// In this newsletter no list is selected, means, it should be sent to all

					$query = 'SELECT a.id'
					. ' FROM #__phocaemail_subscribers AS a'
					. ' WHERE a.published = 1 AND a.active = 1';
					$db->setQuery( $query );

					$subscribers = $db->loadColumn();

					if (!empty($subscribers)) {
						$newsletters[$k]->slist = new \stdClass();
						$newsletters[$k]->slist = implode(',', $subscribers);
					}
				}
			}
		}

		$newsletterList['list']			= $newsletters;
		$newsletterList['genericlist'] 	= HTMLHelper::_('select.genericlist',   $newsletters, 'newsletter', 'class="form-select inputbox"'. '', 'value', 'text', '' );

		return $newsletterList;
	}

	public static function groupslist($name, $selected, $attribs = '', $allowAll = true)
	{
		$db = Factory::getDbo();
		$query = 'SELECT a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level' .
			' FROM #__usergroups AS a' .
			' LEFT JOIN #__usergroups AS b ON a.lft > b.lft AND a.rgt < b.rgt' .
			' GROUP BY a.id' .
			' ORDER BY a.lft ASC';

		try {
			$db->setQuery($query);
			$options = $db->loadObjectList();
		} catch (RuntimeException $e) {
			throw new Exception($e->getMessage(), 500);
		}

		for ($i = 0, $n = count($options); $i < $n; $i++) {
			$options[$i]->text = str_repeat('- ', $options[$i]->level).$options[$i]->text;
		}

		// If all usergroups is allowed, push it into the array.
		/*if ($allowAll) {
			array_unshift($options, HTMLHelper::_('select.option', '', Text::_('JOPTION_ACCESS_SHOW_ALL_GROUPS')));
		}*/

		return HTMLHelper::_('select.genericlist', $options, $name,
			array(
				'list.attr' => 'class="form-control inputbox" size="4" multiple="multiple"',
				'list.select' => $selected
			)
		);
	}

	public static function getToken($type = 'token')
	{

		$app		= Factory::getApplication();
		$secret		= $app->get('secret');
		$secretPartA= substr($secret, mt_rand(5,15), mt_rand(2,10));
		$secretPartB= substr($secret, mt_rand(5,15), mt_rand(2,10));

		$saltArray	= array('a', '0', 'c', '1', 'e', '2', 'h', '3', 'i', '4', 'k', '5', 'm', '6', 'o', '7', 'q', '8', 'r', '0', 'u', '1', 'w', '2', 'y');
		$randA		= mt_rand(0,8000);
		$randB		= mt_rand(0, $randA);
		$randC		= mt_rand(0, $randB);
		$randD		= mt_rand(0,24);
		$randD2		= mt_rand(0,24);


		$salt0 		= md5('string '. $secretPartA . date('s'). $randA . str_replace($randC, $randD, date('r')). $secretPartB . 'end string');
		$salt 		= str_replace($saltArray[$randD], $saltArray[$randD2], $salt0);
		if ($type > 100) {
			$salt 	=  md5($salt);
		}

		if ($salt == '') {
			$salt = $salt0;
		}

		$salt		= crypt($salt, $salt);
		$rT			= $randC + $randA;
		if ($rT < 1) {$rT = 1;}
		$time		= (int)time() * $randB / $rT;
		$token = hash('sha256', $salt . $time . time());

		if ($type == 'folder') {
			return substr($token, $randD, 16);
		} else {
			return $token;
		}
	}
}
