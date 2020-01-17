<?php
namespace Artistic\Database;

class Mysql extends \PDO
{
    private $trace      = false;
    private $sql        = '';
    private $parameter  = array();
    private $account    = '';

    public function __construct()
    {
        $this->loadInfo();
        $this->connect();
    }

    private function loadInfo()
    {
        $account = config('database');

        if (!isset($account['host']) || !isset($account['dbname']) || !isset($account['username']) || !isset($account['passwd']))
            throw new \ArtisticExcepion('The database config is invalid and cannot be loaded.', 500);

        $account['port']    = isset($account['port']) ? $account['port'] : 3306;
        $account['charset'] = isset($account['charset']) ? $account['charset'] : 'utf8';

        $this->account = $account;
    }

    private function connect()
    {
        try {
            $dns = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=%s'
                , $this->account['host']
                , $this->account['port']
                , $this->account['dbname']
                , $this->account['charset']
                );

            parent::__construct($dns, $this->account['username'], $this->account['passwd']);

            $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
            $this->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

        } catch (\ArtisticException $AE) {
            $AE->getException();
        }
    }

    private function comparePlaceholder()
    {
        if (substr_count($this->sql, '?') != count($this->parameter)) 
            throw new \ArtisticException('The number of parameters does not match.', 500);
    }

    private function viewTrace(){
        echo sprintf('SQL : %s; PARAMETERS : Array(%s);'
                , $this->sql
                , implode(','
                    , array_map(
                    function($v, $k){
                    return sprintf('%s=>%s',$k, $v);
                    }
                    ,$this->parameter
                    , array_keys($this->parameter)
                    )
                )
             ) . PHP_EOL;
    }

    private function regexQuery($mode)
    {
        $regex = array(
            'insert' => '/^insert into(?:.*)values(?:.*)\;?/u'
            ,'update' => '/^update(?:.*)set(?:.*)[where]?/u'
            ,'select' => '/^select(?:.*)from(?:.*)[where]?/u'
            ,'delete' => '/^delete from(?:.*)[where]?/u'
            );

        if (!preg_match($regex[$mode], $this->sql)) throw new \ArtisticException('This query could not be processed.', 500);
    }

    private function removeEOL($sql)
    {
        return str_replace(PHP_EOL, '', $sql);
    }

    private function verifyQuery($sql, $parameter, $kind)
    {
        $this->sql = $this->removeEOL($sql);
        $this->parameter = $parameter;

        if (true === $this->trace) $this->viewTrace();
        $this->regexQuery($kind);
        $this->comparePlaceholder();
    }

    public function traceQuery()
    {
        $this->trace = true;
    }

    public function transaction()
    {
        return parent::beginTransaction();
    }

    public function commit()
    {
        return parent::commit();
    }

    public function rollback()
    {
        return parent::rollback();
    }

    public function escape($str)
    {
        return $this->quote($str);
    }

    public function rawQuery($sql)
    {
        return $this->exec($sql);
    }

    public function query($sql, $parameter = array(), $fatch = false)
    {
        $stmt = $this->prepare($this->removeEOL($sql));
        $exec = $stmt->execute($parameter);
        return (true === $fatch) ? $stmt->fetchAll() : $exec;
    }

    public function insert($sql, $parameter = array(), $lastid = false)
    {
        $this->verifyQuery($sql, $parameter, 'insert');
        $stmt = $this->prepare($sql);
        if (true !== $stmt->execute($parameter)) return false;
        return (false === $lastid) ? true : $this->lastInsertId();
    }

    public function select($sql, $parameter = array(), $fetchall = false)
    {
        $this->verifyQuery($sql, $parameter, 'select');
        $stmt = $this->prepare($sql);
        if (true !== $stmt->execute($parameter)) return false;
        else return (false === $fetchall ) ? $stmt->fetch() : $stmt->fetchAll();
    }

    public function update($sql, $parameter = array())
    {
        $this->verifyQuery($sql, $parameter, 'update');
        $stmt = $this->prepare($sql);
        return $stmt->execute($parameter);
    }

    public function delete($sql, $parameter = array())
    {
        $this->verifyQuery($sql, $parameter, 'delete');
        $stmt = $this->prepare($sql);
        return $stmt->execute($parameter);
    }
}