<?php
if(!defined('OSTSCPINC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$action=$upgrader->getNextAction();
?>
<h2>osTicket Upgrade</h2>
<div id="upgrader">
    <div id="main">
            <div id="intro">
             <p>Thank you for taking the time to upgrade your osTicket intallation!</p>
             <p>Please don't cancel or close the browser, any errors at this stage will be fatal.</p>
            </div>
            <h2><?php echo $action ?></h2>
            <p>The upgrade wizard will now attempt to upgrade your database and core settings!</p>
            <ul>
                <li>Database enhancements</li>
                <li>New and updated features</li>
                <li>Enhance settings and security</li>
            </ul>
            <div id="bar">
                <form method="post" action="upgrade.php" id="upgrade">
                    <?php csrf_token(); ?>
                    <input type="hidden" name="s" value="upgrade">
                    <input type="hidden" name="sh" value="<?php echo $upgrader->getSchemaSignature(); ?>">
                    <input class="btn"  type="submit" name="submit" value="Do It Now!">
                </form>
            </div>
    </div>
    <div id="sidebar">
            <h3>Upgrade Tips</h3>
            <p>1. Be patient the process will take a couple of minutes.</p>
            <p>2. If you experience any problems, you can always restore your files/database backup.</p>
            <p>3. We can help, feel free to <a href="http://osticket.com/support/" target="_blank">contact us </a> for professional help.</p>
    </div>
    <div class="clear"></div>
    <div id="upgrading">
        <h4><?php echo $action; ?></h4>
        Please wait... while we upgrade your osTicket installation!
        <div id="msg" style="font-weight: bold;padding-top:10px;">Smile!</div>
    </div>
</div>
<div class="clear"></div>`
