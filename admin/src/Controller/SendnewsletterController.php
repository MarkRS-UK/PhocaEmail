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

namespace Phoca\Component\Phocaemail\Administrator\Controller;

\defined('_JEXEC') or die();

use Phoca\Component\Phocaemail\Administrator\Controller\DisplayController;

class SendnewsletterController extends DisplayController
{
	function __construct() {
		parent::__construct();
		$this->registerTask( 'cancel'  , 'cancel' );		
	}

	function cancel($key = NULL) {
		$this->setRedirect( 'index.php?option=com_phocaemail' );
	}
	
}
// utf-8 test: ä,ö,ü,ř,ž
