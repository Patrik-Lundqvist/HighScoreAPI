<?php

use Phinx\Migration\AbstractMigration;

class AddGamesTable extends AbstractMigration
{
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        $games = $this->table('games');
        $games->addColumn('name', 'string', array('limit' => 20))
              ->addColumn('secret', 'string')
              ->addColumn('key', 'string')
              ->addColumn('version_latest', 'string')
              ->addColumn('version_required', 'string')
              ->addColumn('created_at', 'datetime')
              ->addColumn('updated_at', 'datetime')
              ->addIndex(array('key'), array('unique' => true))
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $this->dropTable('games');
    }
}