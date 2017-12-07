<?php
/**
 * Pic Puller plugin for Craft CMS 3.x
 *
 * Integrate Instagram into Craft CMS.
 *
 * @link      https://picpuller.com
 * @copyright Copyright (c) 2017 John F Morton
 */

namespace jmx2\picpuller\migrations;

use jmx2\picpuller\PicPuller;

use Craft;
use craft\db\Query;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * Pic Puller Install Migration
 *
 * If your plugin needs to create any custom database tables when it gets installed,
 * create a migrations/ folder within your plugin folder, and save an Install.php file
 * within it using the following template:
 *
 * If you need to perform any additional actions on install/uninstall, override the
 * safeUp() and safeDown() methods.
 *
 * @author    John F Morton
 * @package   PicPuller
 * @since     3.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    /**
     * @var string The database driver to use
     */
    public $driver;

    // Public Methods
    // =========================================================================

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->createIndexes();
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
            $this->insertDefaultData();
        }

        return true;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * @return boolean return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates the tables needed for the Records used by the plugin
     *
     * @return bool
     */
    protected function createTables()
    {
        $tablesCreated = false;

    // picpuller_authorizations table
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%picpuller_authorizations}}');
        if ($tableSchema === null) {
            $tablesCreated = true;
            $this->createTable(
                '{{%picpuller_authorizations}}',
                [
                    'id' => $this->primaryKey(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                // // Custom columns in the table
                    'siteId' => $this->integer()->notNull(),
                    'craft_user_id' => $this->integer(11)->notNull()->defaultValue('0'),
                    'instagram_id' => $this->string(255)->notNull()->defaultValue(''),
                    'instagram_oauth' => $this->string(255)->notNull()->defaultValue(''),
                ]
            );
        }

        return $tablesCreated;
    }

    /**
     * Creates the indexes needed for the Records used by the plugin
     *
     * @return void
     */
    protected function createIndexes()
    {
    // picpuller_authorizations table
        $this->createIndex(
            $this->db->getIndexName(
                '{{%picpuller_authorizations}}',
                'id',
                true
            ),
            '{{%picpuller_authorizations}}',
            'id',
            true
        );
        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    /**
     * Creates the foreign keys needed for the Records used by the plugin
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        // picpuller_authorizations table keys for multi site
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%picpuller_authorizations}}', 'siteId'),
            '{{%picpuller_authorizations}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
        // picpuller_authorizations table keys for users
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%picpuller_authorizations}}', 'craft_user_id'),
            '{{%picpuller_authorizations}}',
            'craft_user_id',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * Populates the DB with the default data.
     *
     * @return void
     */
    protected function insertDefaultData()
    {
    }

    /**
     * Removes the tables needed for the Records used by the plugin
     *
     * @return void
     */
    protected function removeTables()
    {
    // picpuller_authorizations table
        $this->dropTableIfExists('{{%picpuller_authorizations}}');
    }
}
