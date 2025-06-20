<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\Component\Phocaemail\Administrator\Table;

\defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;

class NewsletterTable extends Table
{
	
	function __construct($db) {
		parent::__construct('#__phocaemail_newsletters', 'id', $db);
	}
	
}
