<?php
/**
 * JEvents Component for Joomla! 3.x
 *
 * @version     $Id: overview.php 3576 2012-05-01 14:11:04Z geraintedwards $
 * @package     JEvents
 * @copyright   Copyright (C)  2008-2016 GWE Systems Ltd
 * @license     GNU/GPLv2, see http://www.gnu.org/licenses/gpl-2.0.html
 * @link        http://www.jevents.net
 */
defined('_JEXEC') or die('Restricted access');

use Joomla\String\StringHelper;

// we would use this to add custom data to the output here
//JEVHelper::onDisplayCustomFieldsMultiRow($this->rows);

JHTML::_('behavior.tooltip');
JHTML::_('behavior.modal');

$db = JFactory::getDBO();
$user = JFactory::getUser();

$params = JComponentHelper::getParams( JEV_COM_COMPONENT );


// get configuration object
$cfg = JEVConfig::getInstance();
$this->_largeDataSet = $cfg->get('largeDataSet', 0);
$orderdir = JFactory::getApplication()->getUserStateFromRequest("eventsorderdir", "filter_order_Dir", 'asc');
$order = JFactory::getApplication()->getUserStateFromRequest("eventsorder", "filter_order", 'start');
$pathIMG = JURI::root() . 'administrator/images/';

// use JRoute to preseve language selection
$action = JFactory::getApplication()->isAdmin() ? "index.php" : JRoute::_("index.php?option=" . JEV_COM_COMPONENT . "&Itemid=" . JEVHelper::getItemid());

$user = JFactory::getUser();
$accesslevels = $user->getAuthorisedViewLevels();
$accesslevels = "jeval".implode(" jeval", array_unique($accesslevels));


$version = JEventsVersion::getInstance();

JEVHelper::stylesheet('jev_cp.css', 'administrator/components/' . JEV_COM_COMPONENT . '/assets/css/');

$bar = JToolBar::getInstance('newtoolbar');
$toolbar = $bar->getItems() ? $bar->render() : "";
?>

<div id="jev_adminui" class="jev_adminui skin-blue sidebar-mini">
	<header class="main-header">
		<?php echo JEventsHelper::addAdminHeader($items = array(), $toolbar); ?>
	</header>
	<!-- =============================================== -->
	<!-- Left side column. contains the sidebar -->
	<aside class="main-sidebar">
		<!-- sidebar: style can be found in sidebar.less -->
		<?php echo JEventsHelper:: addAdminSidebar($toolbar); ?>
		<!-- /.sidebar -->
	</aside>
	<!-- =============================================== -->
	<!-- Content Wrapper. Contains page content -->
	<div class="content-wrapper" style="min-height: 1096px;">
		<!-- Content Header (Page header) -->
		<section class="content-header">
			<h1>
				<?php echo JText::_("JEV_ADMIN_EVENTS_MANAGEMENT"); ?>
				<small><?php echo JText::_("JEV_ADMIN_EVENTS_MANAGEMENT_STRAPLINE"); ?></small>
			</h1>
		</section>

		<!-- Main content -->
		<section class="content ov_info">

			<!-- Default box -->
			<div class="box">
				<form action="index.php" method="post" name="adminForm" id="adminForm">
					<div class="box">
						<div id="jevents" <?php
						echo (!JFactory::getApplication()->isAdmin() && $params->get("darktemplate", 0)) ? "class='jeventsdark $accesslevels'" : "class='$accesslevels' ";
						?> >
							<table cellpadding="4" cellspacing="0" border="0" >
								<tr>
									<?php if (!$this->_largeDataSet)
									{ ?>
										<td align="right" width="100%"><?php echo JText::_('JEV_HIDE_OLD_EVENTS'); ?> </td>
										<td align="right"><?php echo $this->plist; ?></td>
									<?php } ?>
									<td align="right"><?php echo $this->clist; ?> </td>
									<?php if (!JevJoomlaVersion::isCompatible("3.0"))
									{ ?>
										<td align="right"><?php echo $this->icsList; ?> </td>
										<td align="right"><?php echo $this->statelist; ?> </td>
										<td align="right"><?php echo $this->userlist; ?> </td>
									<?php } ?>
									<td><?php echo JText::_('JEV_SEARCH'); ?>&nbsp;</td>
									<td>
										<input type="text" name="search" value="<?php echo $this->search; ?>" class="inputbox" onChange="document.adminForm.submit();" />
									</td>
									<?php if (JevJoomlaVersion::isCompatible("3.0"))
									{ ?>
										<td align="right">
											<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
											<?php echo $this->pageNav->getLimitBox(); ?>
										</td>
									<?php }
									?>
								</tr>
							</table>

							<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist  table table-striped">
								<tr>
									<th nowrap="nowrap">
										<?php echo JHtml::_('grid.checkall'); ?>
									</th>
									<th nowrap="nowrap"><?php echo JText::_('REPEATS'); ?></th>
									<th  nowrap="nowrap" class="hidden-phone"><?php echo JText::_('STATUS'); ?></th>
									<th style="width:40%" class="title" width="50%" nowrap="nowrap">
										<?php echo JHTML::_('grid.sort', 'JEV_EVENT', 'title', $orderdir, $order, "icalevent.list"); ?>
									</th>
									<?php
									if (count($this->languages)>1) {
										?>
										<th style="width:20%" nowrap="nowrap"><?php echo JText::_('JEV_EVENT_TRANSLATION'); ?></th>
									<?php }
									/*
									if (count ($this->rows)>0 && isset($this->rows[0]->customfields["danceLevel"])) {
									?>
									<th width="10%" nowrap="nowrap"><?php echo $this->rows[0]->customfields["danceLevel"]["label"] ?></th>
									<?php
									}
									 */
									?>
									<th nowrap="nowrap" class="hidden-phone">
										<?php echo JHTML::_('grid.sort', 'JEV_TIME_SHEET', 'starttime', $orderdir, $order, "icalevent.list"); ?>
									</th>
									<th nowrap="nowrap" class="hidden-phone">
										<?php echo JHTML::_('grid.sort', 'JEV_FIELD_CREATIONDATE', 'created', $orderdir, $order, "icalevent.list"); ?>
									</th>
									<th  nowrap="nowrap" class="hidden-phone">
										<?php echo JHTML::_('grid.sort', 'JEV_MODIFIED', 'modified', $orderdir, $order, "icalevent.list"); ?>
									</th>
									<th  nowrap="nowrap" class="hidden-phone"><?php echo JText::_('JEV_ACCESS'); ?></th>
								</tr>

								<?php
								$k = 0;
								$nullDate = $db->getNullDate();
								JHtml::_('bootstrap.tooltip');

								for ($i = 0, $n = count($this->rows); $i < $n; $i++)
								{

									$row = &$this->rows[$i];

									//We need to load the onDisplayCustomFields to get things like images
									$dispatcher = JEventDispatcher::getInstance();
									JPluginHelper::importPlugin('jevents');
									$dispatcher->trigger('onDisplayCustomFields', array(&$row));

                                    $color = JEV_CommonFunctions::setColor($row);
									?>
									<tr class="row<?php echo $k; ?>">
										<td width="20" style="background-color:<?php echo $color; ?>">
											<?php echo JHtml::_('grid.id', $i, $row->ev_id()); ?>
										</td>

										<td>
											<?php
											if ($row->hasrepetition())
											{
												?>
												<a href="javascript: void(0);" onclick="return listItemTask('cb<?php echo $i; ?>','icalrepeat.list')" class=" btn-micro repeatslist">
													<span class="icon-list"> </span>
												</a>
											<?php } ?>
										</td>
										<td align="center" class="hidden-phone">
											<?php
											if ($row->state()==1){
												$img = JHTML::_('image', 'admin/tick.png', '', array('title' => ''), true) ;
											}
											else  if ($row->state()==0){
												$img =  JHTML::_('image', 'admin/publish_x.png', '', array('title' => ''), true) ;
											}
											else {
												$img =  JHTML::_('image', 'admin/trash.png', '', array('title' => ''), true) ;
											}
											?>
											<a href="javascript: void(0);" onclick="return listItemTask('cb<?php echo $i; ?>','<?php echo $row->state() ? 'icalevent.unpublish' : 'icalevent.publish'; ?>')" class=" btn-micro" >
												<?php echo $img; ?>
											</a>
										</td>
										<td class="title">
											<a href="#edit" onclick="return listItemTask('cb<?php echo $i; ?>','icalevent.edit')" title="<?php echo JText::_('JEV_CLICK_TO_EDIT'); ?>"><?php echo $row->title(); ?></a>
											<?php
											if ($row->_thumburl1 && $row->_thumburl1 > '') {

												$tooltip = htmlentities($row->thumbimage1);
												echo '<a href="'.$row->imageurl1.'" title="'.$tooltip.'" class="hasTooltip modal"  rel="{size: {x: 600, y: 400}, handler:\'iframe\'}"><i class="fa fa-picture-o"></i></a>';
												echo '';
											}
											?>
                                            <div class="row_info">
                                                <?php
                                                if ($params->get('jev_manage_show_id', 0) == 1) {
                                                   echo  '<span class="event_id"><strong>' . JText::_("JEV_EVENT_ID") . '</strong> ' . $row->ev_id . '</span>';
                                                }

                                                if ($params->get('jev_manage_show_rpid', 0) == 1)
                                                {
	                                                echo '<span class="event_rp_id"><strong>' . JText::_("JEV_RP_ID") . '</strong> ' . $row->rp_id . '</span>';
                                                }
                                                if ($params->get('jev_manage_show_calendar', 0) == 1)
                                                {
	                                                echo  '<span class="event_cal"><strong>' . JText::_("JEV_EVENT_CALENDAR") . '</strong> ' . $row->getCalendarName(). '</span>';
                                                }

                                                ?>
											    <span class="creator"><strong><?php echo JText::_('JEV_CREATED_BY') . '</strong>' . $row->creatorName(); ?></span>
                                                <span class="category"><strong><?php echo JText::_('JEV_CATEGORY') . '</strong><span class="category_colored" >' . strip_tags($row->getCategoryName()) . '</span>'; ?></span>
                                            </div>
                                            <div class="row_info">
                                                <?php if (isset($row->_jevlocation->title) && $row->_jevlocation->title > '') { ?>
                                                    <span class="location"><?php echo '<strong>'.JText::_('JEV_LOCATION') . '</strong> ' . $row->_jevlocation->title; ?></span>
                                                <?php } else { ?>
                                                    <span class="location"><?php echo ($row->_location  > '' ? '<strong>' . JText::_('JEV_LOCATION') . '</strong>' . $row->_location  : '') ?></span>
                                                <?php } ?>
                                            </div>
										</td>

										<?php  if (count($this->languages)>1) { ?>
											<td align="center"><?php	 echo $this->translationLinks($row); ?>	</td>
										<?php }
										/*
										if (isset($this->rows[0]->customfields["danceLevel"])) {
											if (isset($row->customfields["danceLevel"])){
												?>
										<td align="center"><?php	 echo $row->customfields["danceLevel"]["value"]; ?>	</td>
												<?php
											}
											else {
												?>
										<td/>
												<?php
											}
										}
										 */
										?>
										<td class="hidden-phone">
											<?php
											if ($this->_largeDataSet)
											{
												echo JText::_('JEV_FROM') . ' : ' . $row->publish_up();
											}
											else
											{
												$times = '<div class="time_frame">';
												$times .= '<span class="from"><strong>' . JText::_('JEV_FROM') . ':</strong> ' . ($row->alldayevent() ? JString::substr($row->publish_up(), 0, 10) : JString::substr($row->publish_up(),0,16)) . '</span>';
												$times .= '<span class="to"><strong>' . JText::_('JEV_TO') . ':</strong> ' . (($row->noendtime() || $row->alldayevent()) ? JString::substr($row->publish_down(), 0, 10) : JString::substr($row->publish_down(),0,16)) . '</span>';
												$times .="</div>";
												echo $times;
											}
											?>
										</td>
										<td align="center" class="hidden-phone"><?php echo JString::substr($row->created(), 0, -3); ?></td>
										<td align="center" class="hidden-phone"><?php echo JString::substr($row->modified, 0, -3); ?></td>
										<td align="center" class="hidden-phone"><?php echo $row->_groupname; ?></td>
									</tr>
									<?php
									$k = 1 - $k;
								}
								?>
								<tr>
									<th align="center" colspan="10"><?php echo $this->pageNav->getListFooter(); ?></th>
								</tr>
							</table>
							<input type="hidden" name="option" value="<?php echo JEV_COM_COMPONENT; ?>" />
							<input type="hidden" name="task" value="icalevent.list" />
							<input type="hidden" name="boxchecked" value="0" />
							<input type="hidden" name="filter_order" value="<?php echo $order; ?>" />
							<input type="hidden" name="filter_order_Dir" value="<?php echo $orderdir; ?>" />


						</div>
					</div><!-- /.box-body -->
					<div class="box-footer">

					</div><!-- /.box-footer-->
				</form>
			</div><!-- /.box -->

		</section><!-- /.content -->
	</div>
	<!-- /.content-wrapper -->
	<footer class="main-footer">
		<?php echo JEventsHelper::addAdminFooter(); ?>
	</footer>
	<!-- /.control-sidebar -->
	<!-- Add the sidebar's background. This div must be placed
		   immediately after the control sidebar -->
	<div class="control-sidebar-bg" style="position: fixed; height: auto;"></div>
</div>


<?php
$app = JFactory::getApplication();
if ($app->isSite()) {
	if ($params->get('com_edit_toolbar', 0) == 1 || $params->get('com_edit_toolbar', 0) == 2 ) {
		//Load the toolbar at the bottom!
		$bar = JToolBar::getInstance('newtoolbar');
		$barhtml = $bar->render();
		echo $barhtml;
	}
}
?>

<!--
<form action="index.php" method="post" name="adminForm" id="adminForm">
<?php if (!empty($this->sidebar)) : ?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
 <?php endif; ?>

	<div id="j-main-container" class="span<?php echo (!empty($this->sidebar)) ? $mainspan : $fullspan; ?>  ">
			<table cellpadding="4" cellspacing="0" border="0" >
				<tr>
<?php if (!$this->_largeDataSet)
{ ?>
						<td align="right" width="100%"><?php echo JText::_('JEV_HIDE_OLD_EVENTS'); ?> </td>
						<td align="right"><?php echo $this->plist; ?></td>
					<?php } ?>
					<td align="right"><?php echo $this->clist; ?> </td>
<?php if (!JevJoomlaVersion::isCompatible("3.0"))
{ ?>
						<td align="right"><?php echo $this->icsList; ?> </td>
						<td align="right"><?php echo $this->statelist; ?> </td>
						<td align="right"><?php echo $this->userlist; ?> </td>
<?php } ?>
					<td><?php echo JText::_('JEV_SEARCH'); ?>&nbsp;</td>
					<td>
						<input type="text" name="search" value="<?php echo $this->search; ?>" class="inputbox" onChange="document.adminForm.submit();" />
					</td>
						<?php if (JevJoomlaVersion::isCompatible("3.0"))
{ ?>
						<td align="right">
							<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
	<?php echo $this->pageNav->getLimitBox(); ?>
						</td>
	<?php }
?>
				</tr>
			</table>

			<table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist  table table-striped">
				<tr>
					<th width="20" nowrap="nowrap">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th class="title" width="40%" nowrap="nowrap">
						<?php echo JHTML::_('grid.sort', 'JEV_ICAL_SUMMARY', 'title', $orderdir, $order, "icalevent.list"); ?>
					</th>
					<th width="10%" nowrap="nowrap"><?php echo JText::_('REPEATS'); ?></th>
					<th width="10%" nowrap="nowrap"><?php echo JText::_('JEV_EVENT_CREATOR'); ?></th>
					<?php
if (count($this->languages)>1) {
	?>
					<th width="10%" nowrap="nowrap"><?php echo JText::_('JEV_EVENT_TRANSLATION'); ?></th>
					<?php }
/*
if (count ($this->rows)>0 && isset($this->rows[0]->customfields["danceLevel"])) {
?>
<th width="10%" nowrap="nowrap"><?php echo $this->rows[0]->customfields["danceLevel"]["label"] ?></th>
<?php
}
 */
?>
					<th width="10%" nowrap="nowrap"><?php echo JText::_('JEV_PUBLISHED'); ?></th>
					<th width="20%" nowrap="nowrap">
<?php echo JHTML::_('grid.sort', 'JEV_TIME_SHEET', 'starttime', $orderdir, $order, "icalevent.list"); ?>
					</th>
					<th width="20%" nowrap="nowrap">
<?php echo JHTML::_('grid.sort', 'JEV_FIELD_CREATIONDATE', 'created', $orderdir, $order, "icalevent.list"); ?>
					</th>
					<th width="20%" nowrap="nowrap">
				<?php echo JHTML::_('grid.sort', 'JEV_MODIFIED', 'modified', $orderdir, $order, "icalevent.list"); ?>
					</th>
					<th width="10%" nowrap="nowrap"><?php echo JText::_('JEV_ACCESS'); ?></th>
				</tr>

				<?php
$k = 0;
$nullDate = $db->getNullDate();

for ($i = 0, $n = count($this->rows); $i < $n; $i++)
{
	$row = &$this->rows[$i];
	?>
					<tr class="row<?php echo $k; ?>">
						<td width="20" style="background-color:<?php echo JEV_CommonFunctions::setColor($row); ?>">
							<?php echo JHtml::_('grid.id', $i, $row->ev_id()); ?>
						</td>
						<td >
							<a href="#edit" onclick="return listItemTask('cb<?php echo $i; ?>','icalevent.edit')" title="<?php echo JText::_('JEV_CLICK_TO_EDIT'); ?>"><?php echo $row->title(); ?></a>
						</td>
						<td align="center">
							<?php
	if ($row->hasrepetition())
	{
		?>
								<a href="javascript: void(0);" onclick="return listItemTask('cb<?php echo $i; ?>','icalrepeat.list')" class="btn btn-micro">
									<span class="icon-list"> </span>
								</a>
								<?php } ?>
						</td>
						<td align="center"><?php echo $row->creatorName(); ?></td>
						<?php  if (count($this->languages)>1) { ?>
						<td align="center"><?php	 echo $this->translationLinks($row); ?>	</td>
						<?php }
	/*
	if (isset($this->rows[0]->customfields["danceLevel"])) {
		if (isset($row->customfields["danceLevel"])){
			?>
	<td align="center"><?php	 echo $row->customfields["danceLevel"]["value"]; ?>	</td>
			<?php
		}
		else {
			?>
	<td/>
			<?php
		}
	}
	 */
	?>
						<td align="center">
							<?php
	if ($row->state()==1){
		$img = JHTML::_('image', 'admin/tick.png', '', array('title' => ''), true) ;
	}
	else  if ($row->state()==0){
		$img =  JHTML::_('image', 'admin/publish_x.png', '', array('title' => ''), true) ;
	}
	else {
		$img =  JHTML::_('image', 'admin/trash.png', '', array('title' => ''), true) ;
	}
	?>
							<a href="javascript: void(0);" onclick="return listItemTask('cb<?php echo $i; ?>','<?php echo $row->state() ? 'icalevent.unpublish' : 'icalevent.publish'; ?>')" class="btn btn-micro" >
							<?php echo $img; ?>
							</a>
						</td>
						<td >
							<?php
	if ($this->_largeDataSet)
	{
		echo JText::_('JEV_FROM') . ' : ' . $row->publish_up();
	}
	else
	{
		$times = '<table style="border: 1px solid #666666; width:100%;">';
		$times .= '<tr><td>' . JText::_('JEV_FROM') . ' : ' . ($row->alldayevent() ? JString::substr($row->publish_up(), 0, 10) : JString::substr($row->publish_up(),0,16)) . '</td></tr>';
		$times .= '<tr><td>' . JText::_('JEV_TO') . ' : ' . (($row->noendtime() || $row->alldayevent()) ? JString::substr($row->publish_down(), 0, 10) : JString::substr($row->publish_down(),0,16)) . '</td></tr>';
		$times .="</table>";
		echo $times;
	}
	?>
						</td>
						<td align="center"><?php echo $row->created(); ?></td>
						<td align="center"><?php echo $row->modified; ?></td>
						<td align="center"><?php echo $row->_groupname; ?></td>
					</tr>
	<?php
	$k = 1 - $k;
}
?>
				<tr>
					<th align="center" colspan="10"><?php echo $this->pageNav->getListFooter(); ?></th>
				</tr>
			</table>
			<input type="hidden" name="option" value="<?php echo JEV_COM_COMPONENT; ?>" />
			<input type="hidden" name="task" value="icalevent.list" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $order; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $orderdir; ?>" />
		</div>
</form>
-->
<br />
