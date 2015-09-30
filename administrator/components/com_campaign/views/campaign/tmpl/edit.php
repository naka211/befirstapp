<?php
/**
 * @version     1.0.0
 * @package     com_campaign
 * @copyright   Copyright (C) 2015. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Nguyen Thanh Trung <nttrung211@yahoo.com> - 
 */
// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.keepalive');

// Import CSS
$document = JFactory::getDocument();
$document->addStyleSheet('components/com_campaign/assets/css/campaign.css');
?>
<script type="text/javascript">
    js = jQuery.noConflict();
    js(document).ready(function() {
        
    });

    Joomla.submitbutton = function(task)
    {
        if (task == 'campaign.cancel') {
            Joomla.submitform(task, document.getElementById('campaign-form'));
        }
        else {
            
            if (task != 'campaign.cancel' && document.formvalidator.isValid(document.id('campaign-form'))) {
                
                Joomla.submitform(task, document.getElementById('campaign-form'));
            }
            else {
                alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
            }
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_campaign&layout=edit&id=' . (int) $this->item->id); ?>" method="post" enctype="multipart/form-data" name="adminForm" id="campaign-form" class="form-validate">

    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_CAMPAIGN_TITLE_CAMPAIGN', true)); ?>
        <div class="row-fluid">
            <div class="span10 form-horizontal">
                <fieldset class="adminform">

                    				<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('name'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('name'); ?></div>
			</div>

				<?php echo $this->form->getInput('created_time'); ?>			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('end_date'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('end_date'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('end_hour'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('end_hour'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('end_minute'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('end_minute'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('gender'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('gender'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('from_age'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('from_age'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('to_age'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('to_age'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('from_zipcode'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('from_zipcode'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('to_zipcode'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('to_zipcode'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('image'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('image'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('video'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('video'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('minimum_seconds'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('minimum_seconds'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('reward'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('reward'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('number_of_winners'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('number_of_winners'); ?></div>
			</div>
			<div class="control-group">
				<div class="control-label"><?php echo $this->form->getLabel('instruction'); ?></div>
				<div class="controls"><?php echo $this->form->getInput('instruction'); ?></div>
			</div>
				<input type="hidden" name="jform[active]" value="<?php echo $this->item->active; ?>" />


                </fieldset>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>
        
        

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>

        <input type="hidden" name="task" value="" />
        <?php echo JHtml::_('form.token'); ?>

    </div>
</form>