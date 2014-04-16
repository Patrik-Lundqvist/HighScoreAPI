<?php

use Phinx\Migration\AbstractMigration;

class AddHighScoresTable extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
            $highscores = $this->table('highscores');
            $highscores->addColumn('username', 'string', array('limit' => 20))
                      ->addColumn('score', 'decimal')
                      ->addColumn('game_id', 'integer')
                      ->addColumn('ip_address', 'string')
                      ->addColumn('created_at', 'datetime')
                      ->addColumn('updated_at', 'datetime')
                      ->addForeignKey('game_id', 'games', 'id')
                      ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
            $this->dropTable('highscores');
    }
}