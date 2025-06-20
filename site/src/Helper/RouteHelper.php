<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\Component\Phocaemail\Site\Helper;

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;

class RouteHelper
{

	public static function getNewsletterRoute($itemid = 0, $task = '', $uToken = '', $nToken = '') {

		$app			= Factory::getApplication();
		//$params 		= $app->getParams(); not needed, if needed then use:
		// $params 		= ComponentHelper::getParams('com_phocaemail') ;

		$link = 'index.php?option=com_phocaemail&view=newsletter';

		$needles = array(
			'newsletter' => ''
		);

		if ($task != '') {
			$link .= '&task='.htmlspecialchars($task);
		}

		if ($uToken != '') {
			$link .= '&u='.htmlspecialchars($uToken);
		}

		if ($nToken != '') {
			$link .= '&n='.htmlspecialchars($nToken);
		}

		if ((int)$itemid > 0) {
			$link .= '&Itemid='.(int)$itemid;
		}

		if($item = self::_findItem($needles, 1)) {
			if(isset($item->query['layout'])) {
				$link .= '&layout='.$item->query['layout'];
			}

			if (isset($item->id)) {
				$link .= '&Itemid='.$item->id;
			}
		}

		return $link;
	}


	public static function _findItem($needles, $notCheckId = 0)
	{
		$app	= Factory::getApplication();
		$menus	= $app->getMenu('site', array());
		$items	= $menus->getItems('component', 'com_phocaemail');

		if(!$items) {
			return $app->input->get('Itemid', 0, '', 'int');
			//return null;
		}

		$match = null;

		foreach($needles as $needle => $id)
		{
			/*if ($needle == 'category' && $id == 1) {
				// if root category - ignore it and make the link to all categories
				return false;
			}*/

			if ($notCheckId == 0) {
				foreach($items as $item) {

					if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $id)) {
						$match = $item;
						break;
					}
				}
			} else {
				foreach($items as $item) {

					if (@$item->query['view'] == $needle) {
						$match = $item;
						break;
					}
				}
			}

			if(isset($match)) {
				break;
			}
		}

		return $match;
	}
}
