<?php

use Zend\Permissions\Acl\Acl as ZendAcl;

class Acl extends ZendAcl
{
    public function __construct()
    {
        // APPLICATION ROLES
        $this->addRole('guest');
        $this->addRole('admin');

        // APPLICATION RESOURCES
        $this->addResource('/');
        $this->addResource('/login');
        $this->addResource('/logout');
        $this->addResource('/highscore/top');
        $this->addResource('/highscore/new');
        $this->addResource('/version');
        $this->addResource('/admin');
        $this->addResource('/admin/games');
        $this->addResource('/admin/game/:id');
        $this->addResource('/admin/game/:id/delete');
        $this->addResource('/admin/game/:id/edit');
        $this->addResource('/admin/game/:id/newkey');
        $this->addResource('/admin/game/:id/newsecret');

        // APPLICATION PERMISSIONS
        // Now we allow or deny a role's access to resources. The third argument
        // is 'privilege'. We're using HTTP method for resources.
        $this->allow('guest', '/', 'GET');
        $this->allow('guest', '/login', array('GET', 'POST'));
        $this->allow('guest', '/logout', 'GET');

        $this->allow('guest', '/version', array('GET'));

        $this->allow('guest', '/highscore/top', array('GET'));
        $this->allow('guest', '/highscore/new', array('POST'));

        // This allows admin access to everything
        $this->allow('admin');
    }
}