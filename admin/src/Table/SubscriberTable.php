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

namespace Phoca\Component\Phocaemail\Administrator\Table;

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;

class SubscriberTable extends Table
{

	function __construct($db) {
		parent::__construct('#__phocaemail_subscribers', 'id', $db);
	}
}
