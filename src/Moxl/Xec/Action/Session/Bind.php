<?php
/*
 * Bind.php
 *
 * Copyright 2012 edhelas <edhelas@edhelas-laptop>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA 02110-1301, USA.
 *
 *
 */

namespace Moxl\Xec\Action\Session;

use Moxl\Xec\Action;
use Moxl\Stanza\Stream;
use Moxl\Utils;

class Bind extends Action
{
    private $_to;

    public function request()
    {
        $this->store();
        Stream::bindSet($this->_resource);
    }

    public function setResource($resource)
    {
        $this->_resource = $resource;
        return $this;
    }

    public function handle($stanza, $parent = false) {
        $session = \Session::start();

        list($jid, $resource) = explode('/', (string)$stanza->bind->jid);

        list($username, $host) = explode('@',$jid);

        $session->set('username', $username);
        $session->set('host', $host);

        if($resource) {
            $session->set('resource', $resource);
        }

        $ss = new Start;
        $ss->setTo($session->get('host'))
           ->request();
    }
}
