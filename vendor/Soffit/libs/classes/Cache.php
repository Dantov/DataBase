<?php
namespace libs\classes;

/*
 * класс кеширования
 */
class Cache
{

    protected $path;

    public function __construct()
    {
        $path = AppProperties::getConfig()['cachePath'];
        if ( isset($path) )
        {
            $this->path = _rootDIR_ . $path;
        } else {
            $this->path =  _rootDIR_ . "/cache";
        }
        if ( !file_exists($this->path) ) mkdir($this->path,777);
    }

    /*
     * @var string name - имя файла ( хешируется )
     * @var object $data - данные для хеширования
     * @var object $time - время на которое кешируютяс данные
     * return true|false
     * создает файл кеша данных
     */
    public function set($name, $data, $time=3600)
    {

        $name = md5($name);
        $data['data'] = $data;
        $data['end_time'] = time() + $time;

        if ( file_put_contents($this->path . "/$name.txt", serialize($data) ) ) return true;

        return false;
    }

    /*
     * @var string name - имя файла
     * выдает файл кеша данных
     */
    public function get($name)
    {
        $filename = $this->path ."/". md5($name) . ".txt";
        if ( !file_exists($filename) ) return false;

        $data = unserialize( file_get_contents($filename) );
        if ( time() < $data['end_time'] )
        {
            return $data;
        } else {
            $this->delete($name);
        }

        return false;
    }

    /*
     * @var string name - имя файла
     * удаляет файл кеша
     */
    public function delete($name)
    {
        $filename = $this->path ."/". md5($name) . ".txt";
        if ( file_exists($filename) )
        {
            unlink($filename);
            return true;
        } else {
            return false;
        }
    }

}