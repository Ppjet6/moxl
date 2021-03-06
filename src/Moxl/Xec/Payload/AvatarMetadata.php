<?php

namespace Moxl\Xec\Payload;

use Moxl\Xec\Action\Avatar\Get;

class AvatarMetadata extends Payload
{
    public function handle($stanza, $parent = false)
    {
        $jid = current(explode('/', (string)$parent->attributes()->from));

        $evt = new \Event;

        $cd = new \Modl\ContactDAO;
        $c = $cd->get($jid);

        if(isset($stanza->items->item->metadata->info)) {
            $info = $stanza->items->item->metadata->info->attributes();

            if($info->id != $c->avatarhash) {
                $c->avatarhash = $info->id;
                $cd->set($c);

                $g = new Get;
                $g->setTo($jid)
                  ->request();
            }
        }
    }
}
