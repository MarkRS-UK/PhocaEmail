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

namespace Phoca\Component\Phocaemail\Administrator\Field;

defined('JPATH_BASE') or die;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
jimport('joomla.html.html');
jimport('joomla.form.formfield');

class PhocaEmailOrderingField extends FormField
{

	protected $type = 'PhocaEmailOrdering';

	protected function getInput() {

		// Initialize variables.
		$html = array();
		$attr = '';

		// Get some field values from the form.
		$id			= (int) $this->form->getValue('id');

		if ($this->element['table']) {
			switch (strtolower($this->element['table'])) {

				case "subscriber":
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocaemail_subscribers';
				break;

				case "newsletter":
				default:
					$whereLabel	=	'';
					$whereValue	=	'';
					$table		=	'#__phocaemail_newsletters';
				break;

			}
		} else {
			$whereLabel	=	'';
			$whereValue	=	'';
			$table		=	'#__phocaemail_newsletters';
		}

		// Initialize some field attributes.
		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

		// Initialize JavaScript field attributes.
		$attr .= $this->element['onchange'] ? ' onchange="'.(string) $this->element['onchange'].'"' : '';



		// Build the query for the ordering list.
		$query = 'SELECT ordering AS value, title AS text' .
				' FROM ' . $table;
		if ($whereLabel != '') {
			$query .= ' WHERE '.$whereLabel.' = ' . (int) $whereValue;
		}
		$query .= ' ORDER BY ordering';

		// Create a read-only list (no name) with a hidden input to store the value.
		if ((string) $this->element['readonly'] == 'true') {
			$html[] = HTMLHelper::_('list.ordering', '', $query, trim($attr), $this->value, $id ? 0 : 1);
			$html[] = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"/>';
		}
		// Create a regular list.
		else {
			$html[] = HTMLHelper::_('list.ordering', $this->name, $query, trim($attr), $this->value, $id ? 0 : 1);
		}

		return implode($html);
	}
}
