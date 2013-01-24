<?php
    // Current version && schema signature (Changes from version to version)
    define('THIS_VERSION','1.7-RC4'); //Shown on admin panel
    define('SCHEMA_SIGNATURE','9f3b454c06dfd5ee96003eae5182ac13'); //MD5 signature of the db schema. (used to trigger upgrades)
    
    // Tables being used sytem wide
    define('CONFIG_TABLE',TABLE_PREFIX.'config');
    define('SYSLOG_TABLE',TABLE_PREFIX.'syslog');
    define('SESSION_TABLE',TABLE_PREFIX.'session');
    define('FILE_TABLE',TABLE_PREFIX.'file');
    define('FILE_CHUNK_TABLE',TABLE_PREFIX.'file_chunk');

    define('STAFF_TABLE',TABLE_PREFIX.'staff');
    define('DEPT_TABLE',TABLE_PREFIX.'department');
    define('TOPIC_TABLE',TABLE_PREFIX.'help_topic');
    define('GROUP_TABLE',TABLE_PREFIX.'groups');
    define('GROUP_DEPT_TABLE', TABLE_PREFIX.'group_dept_access');
    define('TEAM_TABLE',TABLE_PREFIX.'team');
    define('TEAM_MEMBER_TABLE',TABLE_PREFIX.'team_member');

    define('FAQ_TABLE',TABLE_PREFIX.'faq');
    define('FAQ_ATTACHMENT_TABLE',TABLE_PREFIX.'faq_attachment');
    define('FAQ_TOPIC_TABLE',TABLE_PREFIX.'faq_topic');
    define('FAQ_CATEGORY_TABLE',TABLE_PREFIX.'faq_category');
    define('CANNED_TABLE',TABLE_PREFIX.'canned_response');
    define('CANNED_ATTACHMENT_TABLE',TABLE_PREFIX.'canned_attachment');

    define('TICKET_TABLE',TABLE_PREFIX.'ticket');
    define('TICKET_THREAD_TABLE',TABLE_PREFIX.'ticket_thread');
    define('TICKET_ATTACHMENT_TABLE',TABLE_PREFIX.'ticket_attachment');
    define('TICKET_PRIORITY_TABLE',TABLE_PREFIX.'ticket_priority');
    define('PRIORITY_TABLE',TICKET_PRIORITY_TABLE);
    define('TICKET_LOCK_TABLE',TABLE_PREFIX.'ticket_lock');
    define('TICKET_EVENT_TABLE',TABLE_PREFIX.'ticket_event');
    define('TICKET_EMAIL_INFO_TABLE',TABLE_PREFIX.'ticket_email_info');
  
    define('EMAIL_TABLE',TABLE_PREFIX.'email');
    define('EMAIL_TEMPLATE_TABLE',TABLE_PREFIX.'email_template');

    define('FILTER_TABLE',TABLE_PREFIX.'filter');
    define('FILTER_RULE_TABLE',TABLE_PREFIX.'filter_rule');
    
    define('BANLIST_TABLE',TABLE_PREFIX.'email_banlist'); //Not in use anymore....as of v 1.7

    define('SLA_TABLE',TABLE_PREFIX.'sla');

    define('API_KEY_TABLE',TABLE_PREFIX.'api_key');
    define('TIMEZONE_TABLE',TABLE_PREFIX.'timezone'); 

    define('TBL_USERS','users');
    // Run After config.inc.php
    // This is to support old installations. with no secret salt.
    if(!defined('SECRET_SALT')) define('SECRET_SALT',md5(TABLE_PREFIX.ADMIN_EMAIL));

    // Session related
    define('SESSION_SECRET', MD5(SECRET_SALT)); //Not that useful anymore...
    define('SESSION_TTL', 86400); // Default 24 hours
   
    define('DEFAULT_MAX_FILE_UPLOADS',ini_get('max_file_uploads')?ini_get('max_file_uploads'):5);
    define('DEFAULT_PRIORITY_ID',1);

    define('EXT_TICKET_ID_LEN',6); //Ticket create. when you start getting collisions. Applies only on random ticket ids.
?>
