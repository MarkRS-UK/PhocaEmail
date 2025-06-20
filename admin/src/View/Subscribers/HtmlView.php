<?php
/* @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @extension Phoca Extension
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

namespace Phoca\Component\Phocaemail\Administrator\View\Subscribers;

\defined( '_JEXEC' ) or die();

use Joomla\CMS\MVC\View\HtmlView AS BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Language\Text;
use Phoca\Component\Phocaemail\Administrator\Helper\UtilsHelper;
use Phoca\Component\Phocaemail\Administrator\View\Adminviews\Adminviews;
use Phoca\Component\Phocaemail\Administrator\Helper\SubscriberHelper;

class HtmlView extends BaseHtmlView
{
	protected $items;
	protected $pagination;
	protected $state;
	protected $t;
	protected $r;
	public $filterForm;
    public $activeFilters;

	function display($tpl = null)
	{
		$this->t			= UtilsHelper::setVars('subscriber');
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->filterForm   = $this->get('FilterForm');
        $this->activeFilters = $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors), 500);
		}

		// Preprocess the list of items to find ordering divisions.
		foreach ($this->items as &$item) {
			$this->ordering[0][] = $item->id;
		}

		$this->r = new Adminviews();

		$this->addToolbar();
		parent::display($tpl);

	}

	function addToolbar()
	{
		$state	= $this->get('State');
		$canDo	= SubscriberHelper::getActions($this->t , $state->get('filter.subscriber_id'));

		ToolbarHelper::title( Text::_( $this->t['l'].'_SUBSCRIBERS' ), 'user fa-user' );

		if ($canDo->get('core.create')) {
			ToolbarHelper::addNew($this->t['task'].'.add','JTOOLBAR_NEW');
		}

		if ($canDo->get('core.edit')) {
			ToolbarHelper::editList($this->t['task'].'.edit','JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.edit.state')) {

			ToolbarHelper::divider();
			ToolbarHelper::custom($this->t['tasks'].'.publish', 'publish.png', 'publish_f2.png','JTOOLBAR_PUBLISH', true);
			ToolbarHelper::custom($this->t['tasks'].'.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
		}

		if ($canDo->get('core.delete')) {
			ToolbarHelper::deleteList( $this->t['l'].'_WARNING_DELETE_ITEMS', 'subscribers.delete', $this->t['l'].'_DELETE');
		}
		ToolbarHelper::divider();
		ToolbarHelper::help( 'screen.'.$this->t['c'], true );
	}

	protected function getSortFields() {
		return array(
			'a.ordering'	=> Text::_('JGRID_HEADING_ORDERING'),
			'a.name' 		=> Text::_($this->t['l'] . '_NAME'),
			'a.email' 		=> Text::_($this->t['l'] . '_EMAIL'),
			'a.date_register' 	=> Text::_($this->t['l'] . '_SIGN_UP_DATE'),
			'a.date_active' 	=> Text::_($this->t['l'] . '_ACTIVATION_DATE'),
			'a.date_unsubscribe' => Text::_($this->t['l'] . '_UNSUBSCRIPTION_DATE'),
			'a.type' 		=> Text::_($this->t['l'] . '_SIGN_UP_TYPE'),
			'a.active' 		=> Text::_($this->t['l'] . '_ACTIVE_USER'),
			'a.hits' 		=> Text::_($this->t['l'] . '_ATTEMPTS'),
			'ml.title' 		=> Text::_($this->t['l'] . '_MAILING_LIST'),
			'a.privacy' 	=> Text::_($this->t['l'] . '_PRIVACY_CONFIRMATION'),
			'a.id' 			=> Text::_('JGRID_HEADING_ID')
		);
	}
}
