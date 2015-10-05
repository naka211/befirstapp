<?php

/**
 * @version     1.0.0
 * @package     com_campaign
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Nguyen Thanh Trung <nttrung211@yahoo.com> - 
 */
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View class for a list of Campaign.
 */
class CampaignViewCampaigns extends JViewLegacy {

    protected $items;
    protected $pagination;
    protected $state;

    /**
     * Display the view
     */
    public function display($tpl = null) {
        $this->state = $this->get('State');
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }

        CampaignHelper::addSubmenu('campaigns');

        $this->addToolbar();

        $this->sidebar = JHtmlSidebar::render();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since	1.6
     */
    protected function addToolbar() {
        require_once JPATH_COMPONENT . '/helpers/campaign.php';

        $state = $this->get('State');
        $canDo = CampaignHelper::getActions($state->get('filter.category_id'));

        JToolBarHelper::title(JText::_('COM_CAMPAIGN_TITLE_CAMPAIGNS'), 'campaigns.png');

        //Check if the form exists before showing the add/edit buttons
        $formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/campaign';
        if (file_exists($formPath)) {

            if ($canDo->get('core.create')) {
                JToolBarHelper::addNew('campaign.add', 'JTOOLBAR_NEW');
            }

            if ($canDo->get('core.edit') && isset($this->items[0])) {
                JToolBarHelper::editList('campaign.edit', 'JTOOLBAR_EDIT');
            }
        }

        if ($canDo->get('core.edit.state')) {

            if (isset($this->items[0]->state)) {
                JToolBarHelper::divider();
                JToolBarHelper::custom('campaigns.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
                JToolBarHelper::custom('campaigns.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
            } else if (isset($this->items[0])) {
                //If this component does not use state then show a direct delete button as we can not trash
                JToolBarHelper::deleteList('', 'campaigns.delete', 'JTOOLBAR_DELETE');
            }

            if (isset($this->items[0]->state)) {
                JToolBarHelper::divider();
                JToolBarHelper::archiveList('campaigns.archive', 'JTOOLBAR_ARCHIVE');
            }
            if (isset($this->items[0]->checked_out)) {
                JToolBarHelper::custom('campaigns.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
            }
        }

        //Show trash and delete for components that uses the state field
        if (isset($this->items[0]->state)) {
            if ($state->get('filter.state') == -2 && $canDo->get('core.delete')) {
                JToolBarHelper::deleteList('', 'campaigns.delete', 'JTOOLBAR_EMPTY_TRASH');
                JToolBarHelper::divider();
            } else if ($canDo->get('core.edit.state')) {
                JToolBarHelper::trash('campaigns.trash', 'JTOOLBAR_TRASH');
                JToolBarHelper::divider();
            }
        }

        if ($canDo->get('core.admin')) {
            JToolBarHelper::preferences('com_campaign');
        }

        //Set sidebar action - New in 3.0
        JHtmlSidebar::setAction('index.php?option=com_campaign&view=campaigns');

        $this->extra_sidebar = '';
        
    }

	protected function getSortFields()
	{
		return array(
		'a.`id`' => JText::_('JGRID_HEADING_ID'),
		'a.`name`' => JText::_('COM_CAMPAIGN_CAMPAIGNS_NAME'),
		'a.`created_time`' => JText::_('COM_CAMPAIGN_CAMPAIGNS_CREATED_TIME'),
		'a.`end_date`' => JText::_('COM_CAMPAIGN_CAMPAIGNS_END_DATE'),
		'a.`from_zipcode`' => JText::_('COM_CAMPAIGN_CAMPAIGNS_FROM_ZIPCODE'),
		'a.`to_zipcode`' => JText::_('COM_CAMPAIGN_CAMPAIGNS_TO_ZIPCODE'),
		'a.`reward`' => JText::_('COM_CAMPAIGN_CAMPAIGNS_REWARD'),
		'a.`number_of_winners`' => JText::_('COM_CAMPAIGN_CAMPAIGNS_NUMBER_OF_WINNERS'),
		'a.`published`' => JText::_('COM_CAMPAIGN_CAMPAIGNS_PUBLISHED'),
		);
	}

}
