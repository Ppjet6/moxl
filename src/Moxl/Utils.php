<?php
/**
 * @file Utils.php
 *
 * @brief Some stuff for Moxl
 *
 * Copyright © 2012 Timothée Jaussoin
 *
 * This file is part of Moxl.
 *
 * Moxl is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * Moxl is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Datajar.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace Moxl;

use Monolog\Logger;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\StreamHandler;

class Utils {
    // Display RAW XML in the browser
    public static function displayXML($xml) {
        echo '<pre>'.htmlentities(Utils::cleanXML($xml), ENT_QUOTES, 'UTF-8').'</pre>';
    }

    // A simple function which clean and reindent an XML string
    public static function cleanXML($xml) {
        if($xml != '') {
            $doc = new \DOMDocument();
            $doc->loadXML($xml);
            $doc->formatOutput = true;
            return $doc->saveXML();
        } else {
            return '';
        }
    }

    public static function explodeData($data) {
        $data = explode(',', $data);
        $pairs = array();
        $key = false;

        foreach($data as $pair) {
            $dd = strpos($pair, '=');
            if($dd) {
                $key = trim(substr($pair, 0, $dd));
                $pairs[$key] = trim(trim(substr($pair, $dd + 1)), '"');
            }
            else if(strpos(strrev(trim($pair)), '"') === 0 && $key) {
                $pairs[$key] .= ',' . trim(trim($pair), '"');
                continue;
            }
        }

        return $pairs;
    }

    public static function resolveHost($host) {
        $r = new \Net_DNS2_Resolver(array('timeout' => 1));
        try {
            $result = $r->query('_xmpp-client._tcp.'.$host, 'SRV');

            if(!empty($result->answer[0])) return $result->answer[0];
        } catch (\Net_DNS2_Exception $e) {
            error_log($e->getMessage());
        }

        return false;
    }

    public static function getDomain($host) {
        $result = Utils::resolveHost($host);

        if(isset($result->target) && $result->target != null)
            return $result->target;
        else {
            return $host;
        }
    }

    public static function implodeData($data) {
        $return = array();
        foreach($data as $key => $value)
            $return[] = $key . '="' . $value . '"';
        return implode(',', $return);
    }

    public static function generateNonce($binary = true) {
        $str = '';
        mt_srand((double) microtime()*10000000);
        for($i=0; $i<32; $i++)
            $str .= chr(mt_rand(0, 255));
        return $binary ? $str : base64_encode($str);
    }

    public static function getSupportedServices() {
        return array(
            'urn:xmpp:microblog:0',
            'urn:xmpp:microblog:0+notify',
            'urn:xmpp:inbox',
            'urn:xmpp:inbox+notify',
            'urn:xmpp:pubsub:subscription',
            'urn:xmpp:pubsub:subscription+notify',
            'urn:xmpp:attention:0',
            'urn:xmpp:vcard4',
            'urn:xmpp:vcard4+notify',
            'urn:xmpp:avatar:data',
            'urn:xmpp:avatar:metadata',
            'urn:xmpp:avatar:metadata+notify',
            'urn:xmpp:receipts',
            'urn:xmpp:carbons:2',
            'jabber:iq:version',
            'jabber:iq:last',
            'vcard-temp',
            'jabber:x:data',
            'urn:xmpp:ping',
            'urn:xmpp:message-correct:0',

            // Jingle
            /*'http://jabber.org/protocol/jingle',
            'urn:xmpp:jingle:1',
            'urn:xmpp:jingle:apps:rtp:1',
            'urn:xmpp:jingle:apps:rtp:audio',
            'urn:xmpp:jingle:apps:rtp:video',
            'urn:ietf:rfc:3264',
            'urn:ietf:rfc:5888',
            'urn:xmpp:jingle:apps:dtls:0',
            'urn:ietf:rfc:5576',
            'urn:xmpp:jingle:transports:ice-udp:0',
            'urn:xmpp:jingle:transports:ice-udp:1',
            'urn:xmpp:jingle:apps:rtp:rtcp-fb:0',*/

            'http://jabber.org/protocol/muc',
            'http://jabber.org/protocol/nick',
            'http://jabber.org/protocol/nick+notify',
            'http://jabber.org/protocol/mood',
            'http://jabber.org/protocol/mood+notify',
            'http://jabber.org/protocol/xhtml-im',
            'http://jabber.org/protocol/chatstates',
            'http://jabber.org/protocol/httpbind',
            'http://jabber.org/protocol/caps',
            'http://jabber.org/protocol/disco#info',
            'http://jabber.org/protocol/disco#items',
            'http://jabber.org/protocol/activity',
            'http://jabber.org/protocol/geoloc',
            'http://jabber.org/protocol/geoloc+notify',
            'http://jabber.org/protocol/http-bind',
            'http://jabber.org/protocol/pubsub',
            'http://jabber.org/protocol/tune',
            'http://jabber.org/protocol/tune+notify');
    }

    public static function generateCaps() {
        $s = '';
        $s .= 'client/web//Movim<';

        $support = Utils::getSupportedServices();

        asort($support);
        foreach($support as $sup ) {
            $s = $s.$sup.'<';
        }

        return base64_encode(sha1(utf8_encode($s),true));
    }

    public static function log($message, $priority = '')
    {
        if(LOG_LEVEL != null && LOG_LEVEL > 0) {
            $log = new Logger('moxl');

            $handler = new SyslogHandler('moxl');

            if(LOG_LEVEL > 1)
                $log->pushHandler(new StreamHandler(LOG_PATH.'/xmpp.log', Logger::DEBUG));

            $log->pushHandler($handler, Logger::DEBUG);

            $errlines = explode("\n",$message);
            foreach ($errlines as $txt) { $log->addDebug($txt); }
        }
    }
}
