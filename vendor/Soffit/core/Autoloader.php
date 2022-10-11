<?php
namespace soffit;

require_once dirname(__DIR__) . '/defines_core.php';
require_once dirname(__DIR__) . '/libs/functions.php';

class Autoloader
{

    protected static $aliases = [];

    public function __construct($aliases=[])
    {
        if ( is_array($aliases) )
        {
            foreach ( $aliases as $alias => $path )
            {
                static::$aliases[$alias] = $path;
            }
        }
        spl_autoload_register([$this,'autoload']);
    }

    /**
     * @param $class
     * @throws \Exception
     */
    protected function autoload( string $class )
    {
        //заменяет обратный слешь на прямой для unix систем
        $class = str_replace('\\','/', $class);
        
        $extractedPath = explode('/',$class);
        foreach( $extractedPath as &$pathElem )
        {
            if ( array_key_exists($pathElem,static::$aliases) )
            {
                // check if alias inside the alias
                //$pathElem = $this->getAlias(static::$aliases[$pathElem]);
                $pathElem = str_replace('\\','/', static::$aliases[$pathElem]);//static::$aliases[$pathElem];
            }
        }

        $realPath = implode('/',$extractedPath);
        $class = _rootDIR_ . $realPath . '.php';

        //debug($class,'$class');

        if ( !file_exists($class) ) throw new \Exception( "Autoloader Exception: class <i>" . $class . "</i> not found!", 777 );

        require_once $class;
    }

    public function setAlias($alias, $path)
    {
        if ( is_string($alias) && is_string($path) )
        {
            self::$aliases[$alias] = $path;
            return true;
        }
        return false;
    }

    protected function getAlias(string $alias)
    {
        $a = '';

        $alias = explode('/',$alias);

        foreach( $alias as $pathElem )
        {
            if ( array_key_exists($pathElem, self::$aliases) )
            {

                $a .= $this->getAlias(self::$aliases[$pathElem]);
                debug($a,'a');
            }
            $a .= '/'.$pathElem;
            //$pathElem = $this->getAlias($pathElem);
        }
        
        return $a;
    }


}