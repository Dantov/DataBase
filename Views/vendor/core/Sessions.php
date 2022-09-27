<?php

namespace Views\vendor\core;


class Sessions
{
    const SESSION_STARTED = TRUE;
    const SESSION_NOT_STARTED = FALSE;

    private bool $sessionState = self::SESSION_NOT_STARTED;

    public function __construct()
    {
        $this->startSession();
    }

    /**
     * @throws \Exception
     */
    private function __clone()
    {
        throw new \Exception('You can\'t clone sessions', 888);
    }

    /**
     * @param $name
     */
    public function __get(string $name)
    {
        if ( $this->hasKey($name) ) {
            return $_SESSION[$name];
        }
    }

    /**
     * @param $name
     * @param $value
     * @throws \Exception
     */
    public function __set(string $name, $value)
    {
        if ( empty($name) ) {
            throw new \Exception('Session name must be string type and not empty', 589);
        }
        
        $_SESSION[$name] = $value;
    }

    protected function is_session_started()
    {
        if ( php_sapi_name() !== 'cli' ) {
            if ( version_compare(phpversion(), '5.4.0', '>=') ) {
                return session_status() === PHP_SESSION_ACTIVE ? self::SESSION_STARTED : self::SESSION_NOT_STARTED;
            } else {
                return session_id() === '' ? self::SESSION_NOT_STARTED : self::SESSION_STARTED;
            }
        }
        
        return self::SESSION_NOT_STARTED;
    }
    
    protected function startSession() : bool
    {
        if ( $this->is_session_started() === false ) {
            session_start();
            return $this->sessionState = self::SESSION_STARTED;
        }
        
        return self::SESSION_NOT_STARTED;
    }

    public function destroySession() : bool
    {
        if ( $this->sessionState === self::SESSION_STARTED )
        {
            session_destroy();
            $this->sessionState = self::SESSION_NOT_STARTED;
            unset( $_SESSION );

            return !$this->sessionState;
        }

        return FALSE;
    }

    public function setKey( string $name, $value )
    {
        $_SESSION[$name] = $value;
    }

    public function getKey( string $name )
    {
        if ( $this->hasKey($name) ) 
        {
            return $_SESSION[$name];
        }
        return null;
    }

    public function hasKey($name) : bool
    {
        return array_key_exists($name, $_SESSION);
    }

    public function getAll()
    {
        return $_SESSION;
    }

    public function dellKey($name)
    {
        if ( isset( $_SESSION[$name] ) )
        {
            unset($_SESSION[$name]);
            return true;
        }
        return false;
    }

    public function setFlash($key, $value)
    {
        if ( isset($key) && !empty($key) )
        {
            return $this->setKey("_flash_".$key, $value);
        }
        return false;
    }

    public function hasFlash($key)
    {
        if ( !empty($key) )
        {
            $this->startSession();

            if ( isset( $_SESSION["_flash_".$key] ) ) return true;
        }
        return false;
    }

    public function getFlash($key)
    {
        if ( isset($key) && !empty($key) )
        {
            if ($res = $this->getKey("_flash_".$key))
            {
                $this->dellKey("_flash_".$key);
                return $res;
            }
        }
        return false;
    }

}