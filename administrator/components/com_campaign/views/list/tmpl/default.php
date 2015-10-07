<?php
// no direct access
defined('_JEXEC') or die;
$campaign_id = JRequest::getVar("campaign_id");
$db = JFactory::getDBO();
$q = "SELECT number_of_winners FROM #__campaign WHERE id = ".$campaign_id;
$db->setQuery($q);
$nofw = $db->loadResult();

$limit = 50;
$page = JRequest::getVar('page', 1);
$limitstart = ($page - 1) * $limit;
$q = "SELECT u.id, cu.rank, u.name, cu.viewed_time FROM #__campaign_users cu INNER JOIN #__users u ON cu.user_id = u.id WHERE cu.campaign_id = ".$campaign_id." AND viewed = 1 ORDER BY cu.rank ASC LIMIT ".$limitstart.", ".$limit;
$db->setQuery($q);
$users = $db->loadObjectList();

$db->setQuery("SELECT count(id) FROM #__campaign_users WHERE campaign_id = ".$campaign_id." AND viewed = 1");
$total = $db->loadResult();
$page_total = ceil($total/$limit);
?>
<a class="btn btn-small" href="index.php?option=com_campaign&view=campaigns">Back</a>
<table class="table table-striped">
	<thead>
		<tr>
			<th>ID</th>
			<th>Name</th>
			<th>Rank</th>
			<th>Time</th>
			<th></th>
		</tr>
	</thead>
	
		<?php foreach($users as $user){?>
		<tr>
			<td><?php echo $user->id;?></td>
			<td><?php echo $user->name;?></td>
			<td><?php echo $user->rank;?></td>
			<td><?php echo JHtml::_('date', $user->viewed_time, 'H:i:s d-m-Y'); ?></td>
			<td><?php if($user->rank<=$nofw) echo "<strong>Win</strong>";?></th>
		</tr>
		<?php }?>
</table>
<?php if($page_total > 1){
	if($page == 1){
	?>
<a class="btn btn-small" href="index.php?option=com_campaign&view=list&campaign_id=<?php echo $campaign_id;?>&page=<?php echo $page + 1;?>">Next</a>
	<?php 
	}
	if($page > 1 && $page < $page_total){
	?>
	<a class="btn btn-small" href="index.php?option=com_campaign&view=list&campaign_id=<?php echo $campaign_id;?>&page=<?php echo $page - 1;?>">Prev</a>
	<a class="btn btn-small" href="index.php?option=com_campaign&view=list&campaign_id=<?php echo $campaign_id;?>&page=<?php echo $page + 1;?>">Next</a>
	<?php }
	if($page == $page_total){?>
	<a class="btn btn-small" href="index.php?option=com_campaign&view=list&campaign_id=<?php echo $campaign_id;?>&page=<?php echo $page - 1;?>">Prev</a>
	<?php
	}
	}?>