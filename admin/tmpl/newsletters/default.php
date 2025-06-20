<?php
/*
 * @package Joomla
 * @copyright Copyright (C) Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * @component Phoca Gallery
 * @copyright Copyright (C) Jan Pavelka www.phoca.cz
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;

$r 			= $this->r;
$user		= Factory::getUser();
$userId		= $user->get('id');
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$canOrder	= $user->authorise('core.edit.state', $this->t['o']);
$saveOrder	= $listOrder == 'a.ordering';
$saveOrderingUrl = '';
if ($saveOrder && !empty($this->items)) {
	$saveOrderingUrl = $r->saveOrder($this->t, $listDirn);
}
$sortFields = $this->getSortFields();

echo $r->jsJorderTable($listOrder);

echo $r->startForm($this->t['o'], $this->t['tasks'], 'adminForm');

echo $r->startMainContainer();


echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));


echo $r->startTable('categoryList');

echo $r->startTblHeader();

echo $r->firstColumnHeader($listDirn, $listOrder);
echo $r->secondColumnHeader($listDirn, $listOrder);

echo '<th class="ph-title">'.HTMLHelper::_('searchtools.sort',  	$this->t['l'].'_TITLE', 'a.title', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-title">'.HTMLHelper::_('searchtools.sort',  	$this->t['l'].'_SUBJECT', 'a.subject', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-published">'.HTMLHelper::_('searchtools.sort',  $this->t['l'].'_PUBLISHED', 'a.published', $listDirn, $listOrder ).'</th>'."\n";
echo '<th class="ph-id">'.HTMLHelper::_('searchtools.sort',  		$this->t['l'].'_ID', 'a.id', $listDirn, $listOrder ).'</th>'."\n";

echo $r->endTblHeader();

echo $r->startTblBody($saveOrder, $saveOrderingUrl, $listDirn);

$originalOrders = array();
$parentsStr 	= "";
$j 				= 0;

if (is_array($this->items)) {
	foreach ($this->items as $i => $item) {
		//if ($i >= (int)$this->pagination->limitstart && $j < (int)$this->pagination->limit) {
			$j++;

		$urlEdit = 'index.php?option=' . $this->t['o'] . '&task=' . $this->t['task'] . '.edit&id=';
		$urlTask = 'index.php?option=' . $this->t['o'] . '&task=' . $this->t['task'];
		$orderkey = array_search($item->id, $this->ordering[0]);
		$ordering = ($listOrder == 'a.ordering');
		$canCreate = $user->authorise('core.create', $this->t['o']);
		$canEdit = $user->authorise('core.edit', $this->t['o']);
		$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
		$canChange = $user->authorise('core.edit.state', $this->t['o']) && $canCheckin;
		$linkEdit = Route::_($urlEdit . $item->id);


		echo $r->startTr($i, isset($item->catid) ? (int)$item->catid : 0);

		echo $r->firstColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $item->ordering);
		echo $r->secondColumn($i, $item->id, $canChange, $saveOrder, $orderkey, $item->ordering);

		$checkO = '';
		if ($item->checked_out) {
			$checkO .= HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, $this->t['tasks'] . '.', $canCheckin);
		}
		if ($canCreate || $canEdit) {
			$checkO .= '<a href="' . Route::_($linkEdit) . '">' . $this->escape($item->title) . '</a>';
		} else {
			$checkO .= $this->escape($item->title);
		}
		$checkO .= ' <span class="smallsub">';//(<span>'.Text::_($this->t['l'].'_FIELD_ALIAS_LABEL').':</span>'. $this->escape($item->alias).')</span>';
		echo $r->td($checkO, "small ");

		echo $r->td($this->escape($item->subject), "small ");

		echo $r->td(HTMLHelper::_('jgrid.published', $item->published, $i, $this->t['tasks'] . '.', $canChange), "small ");

		echo $r->td($item->id, "small ");

		echo $r->endTr();

		//}
	}
}
echo $r->endTblBody();

echo $r->tblFoot($this->pagination->getListFooter(), 6);
echo $r->endTable();

echo $r->formInputsXML($listOrder, $listDirn, $originalOrders);
echo $r->endMainContainer();
echo $r->endForm();