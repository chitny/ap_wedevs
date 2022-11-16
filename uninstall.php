<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// drop applicant_submissions database table
global $wpdb;
$wpdb->query("DROP TABLE IF EXISTS applicant_submissions");
?>