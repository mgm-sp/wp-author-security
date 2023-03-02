<?php

namespace WP_Author_Security;

class WPASData
{

    public static $dbVersion = "1.0";
    const TYPE_GENERAL = 99;
    const TYPE_AUTHOR_REQUEST = 1;
    const TYPE_REST_API_USER = 2;
    const TYPE_LOGIN_PWRESET = 3;
    const TYPE_FEED = 4;
    const TYPE_OEMBED = 5;
    const TYPE_SITEMAP_AUTHOR = 6;
    const KEY_LAST_ACTION = 'last_action';
    const KEY_ALL_COUNT = 'all';

    public function __construct() {
        global $wpdb;

        // Registering meta table
        $wpdb->wpas_statisticmeta = $wpdb->prefix . 'wpas_statisticmeta';
        //$this->cleanUp();
    }

    /**
     * Setup of the database
     * @return void
     */
    public static function createDB() {
        global $wpdb;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $table_name = $wpdb->prefix . "wpas_statisticmeta";
        $charset_collate = $wpdb->get_charset_collate();

        $schema = "CREATE TABLE  {$table_name} (
		meta_id bigint(20) NOT NULL AUTO_INCREMENT,
		wpas_statistic_id bigint(20) NOT NULL DEFAULT '0',
		meta_key varchar(255) DEFAULT NULL,
		meta_value longtext,
		PRIMARY KEY meta_id (meta_id),
		KEY myplugin_product_id (wpas_statistic_id),
		KEY meta_key (meta_key)
		) {$charset_collate };";

        dbDelta( $schema );

        add_option( "wpas_db_version", WPASData::$dbVersion );
    }

    public static function uninstall() {
        global $wpdb;
        $table_name = $wpdb->prefix . "wpas_statisticmeta";
        $sql = "DROP TABLE IF EXISTS {$table_name}";
        $wpdb->query($sql);

        delete_option('wpas_db_version');
        delete_option( 'protectAuthor');
        delete_option( 'protectAuthorName');
        delete_option( 'disableLoggedIn');
        delete_option( 'disableRestUser');
        delete_option( 'customLoginError');
        delete_option( 'wpas_filterFeed');
        delete_option( 'wpas_filterEmbed');
        delete_option( 'wpas_filterAuthorSitemap');
    }

    /**
     * Checks whether the db schema needs to be updated
     * @return void
     */
    public static function updateDbCheck() {
        if ( get_site_option( 'wpas_db_version' ) !== WPASData::$dbVersion ) {
            WPASData::createDB();
        }
    }

    public function getMeta( $id, $meta_key, $single = true ) {
        return get_metadata( 'wpas_statistic', $id, $meta_key, $single );
    }

    public function updateMeta( $id, $meta_key, $value ='' ) {
        return update_metadata( 'wpas_statistic', $id, $meta_key, $value );
    }

    public function addMeta( $id, $meta_key, $value ='' ) {
        return add_metadata( 'wpas_statistic', $id, $meta_key, $value, true );
    }

    public function deleteMeta( $id, $meta_key = '' ) {
        return delete_metadata( 'wpas_statistic', $id, $meta_key );
    }

    public function addOrUpdate($id, $meta_key, $value = '' ) {
        $count = $this->getMeta($id, $meta_key);
        if(empty($count) && $count !== '0') {
            return $this->addMeta($id, $meta_key, "1");
        } else {
            return $this->updateMeta($id, $meta_key, (empty($value) ? ++$count : $value));
        }
    }

    /**
     * Returns the number of days between two days
     * @param DateTimeInterface $start
     * @param DateTimeInterface $end
     * @return string
     */
    private function getDateDiffDays(\DateTimeInterface $start, \DateTimeInterface $end) {
        $interval = date_diff($start, $end);

        // return number of days
        return $interval->format('%a');
    }

    /**
     * Returns the total number of malicious requests
     * @return string
     */
    public function getCountAll() : string {
        $count = $this->getMeta(WPASData::TYPE_GENERAL, WPASData::KEY_ALL_COUNT);
        return (empty($count) ? '0' : $count);
    }

    /**
     * Calculates the number of malicious requests in the past 7 days
     * @return int
     */
    public function getCountLastDays() : int {
        $count = 0;
        for($i=1; $i<=7; $i++) {
            $dayCount = $this->getMeta(WPASData::TYPE_GENERAL, 'weekday_' . $i);
            if(!empty($dayCount)) {
                $count += $dayCount;
            }
        }
        return $count;
    }

    /**
     * Reset counter for days of previous week(s)
     * @return void
     * @throws Exception
     */
    public function cleanUp() {
        $lastAction = $this->getMeta(WPASData::TYPE_GENERAL, WPASData::KEY_LAST_ACTION);
        if(empty($lastAction)) {
            return;
        }
        $today = new \DateTime();
        $lastDate = new \DateTime($lastAction);
        $days = $this->getDateDiffDays($lastDate, $today);

        // do nothing when last action is within one week
        if($days <= 6) {
            return;
        }
        // loop over days that need to be reset
        for($i = 1; $i <= 7; $i++) {
            // reset for all possible types
            $this->updateMeta(WPASData::TYPE_GENERAL, 'weekday_' . $i, 0);
        }
    }
}
