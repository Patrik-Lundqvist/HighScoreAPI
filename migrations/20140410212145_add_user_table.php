<?php

use Phinx\Migration\AbstractMigration;

class AddUserTable extends AbstractMigration
{
    
    /**
     * Migrate Up.
     */
    public function up()
    {
            $highscores = $this->table('users');
            $highscores->addColumn('username', 'string', array('limit' => 50))
                        ->addColumn('role', 'string', array('limit' => 50))
                        ->addColumn('password', 'string', array('limit' => 255))
                        ->addColumn('created_at', 'datetime')
                        ->addColumn('updated_at', 'datetime')
                        ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
             $this->dropTable('users');
    }
}