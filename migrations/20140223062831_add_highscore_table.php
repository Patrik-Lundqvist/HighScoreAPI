<?php

use Phinx\Migration\AbstractMigration;

class AddHighscoreTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
            $users = $this->table('highscores');
            $users->addColumn('username', 'string', array('limit' => 20))
                  ->addColumn('float', 'integer')
                  ->addColumn('created', 'datetime')
                  ->addColumn('updated', 'datetime', array('default' => null))
                  ->addIndex(array('username'), array('unique' => true))
                  ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}