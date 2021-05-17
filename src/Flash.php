<?php

namespace Kzu\Web;

use Kzu\Storage\Session;

Trait Flash {
    static public function all() {
        return Session::get('flash') ?? ['errors' => [], 'success' => [], 'warnings' => []];
    }
    
    static public function get(string $type) {
        $flash = Flash::all();
        if (!empty($flash[$type])):
            $messages = $flash[$type];
            unset($flash[$type]);
            Session::set('flash', $flash);
            return $messages;
        else: return null; endif;
    }
    
    static public function add(string $type, string $message) {
        $flash = Flash::all();
        $flash[$type][] = $message;
        return Session::set('flash', $flash);
    }
}