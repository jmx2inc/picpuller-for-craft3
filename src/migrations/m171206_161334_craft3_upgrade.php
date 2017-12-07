<?php

namespace jmx2\picpuller\migrations;

use Craft;
use craft\db\Migration;
use craft\helpers\MigrationHelper;
use jmx2\picpuller\fields\Imagebrowser;

/**
 * m171206_161334_craft3_upgrade migration.
 */
class m171206_161334_craft3_upgrade extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        // Place migration code here...

        // old fieldtype was called "PicPuller_ImageBrowser"

        // Auto-convert old PicPuller_ImageBrowser fields to the "Imagebrowser::class" from the new Pic Puller, i.e. jmx2\picpuller\fields\Imagebrowser
        $this->update('{{%fields}}', [
            'type' => Imagebrowser::class
        ], [
            'type' => 'PicPuller_ImageBrowser'
        ], [], false);


        // old table for authorizations was "picpuller_authorizations",
        // which, in most cases, included the prefix of "craft_"
        // Here's what needs to update
        // id -> int (11) primary key  - stays the same
        // user_id -> varchar (255), not null - rename to craft_user_id
        // instagram_id -> varchar (255) not null
        // oauth -> varchar (255) not null - rename to instagram_oauth
        // dateCreated -> datetime not null - stays the same
        // dateUpdated -> datetime not null - stays the same
        // uid -> char (36) not null - stays the same
        // there was no "siteId" column, but Craft 3 version should have one

        if ($this->db->columnExists('{{%picpuller_authorizations}}', 'user_id')) {
            MigrationHelper::renameColumn ( '{{%picpuller_authorizations}}' , 'user_id' , 'craft_user_id' );
        }
        if ($this->db->columnExists('{{%picpuller_authorizations}}', 'oauth')) {
            MigrationHelper::renameColumn ( '{{%picpuller_authorizations}}' , 'oauth' , 'instagram_oauth' );
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        echo "m171206_161334_craft3_upgrade cannot be reverted.\n";
        return false;
    }
}
