<?php

class DB
{
    private static $adepter;

    public static function adepter()
    {
        if (is_null(static::$adepter)) static::$adepter = new Artistic\Database\Mysql;
        return static::$adepter;
    }

    public static function traceQuery()
    {
        return static::adepter()->traceQuery();
    }

    public static function rawQuery($sql)
    {
        return static::adepter()->rawQuery($sql);
    }

    public static function query($sql, $parameter = array(), $fetch = false)
    {
        return static::adepter()->query($sql, $parameter, $fetch);
    }

    public static function insert($sql, $parameter = array(), $lastid = false)
    {
        return static::adepter()->insert($sql, $parameter, $lastid);
    }

    public static function update($sql, $parameter = array())
    {
        return static::adepter()->update($sql, $parameter);
    }

    public static function select($sql, $parameter = array(), $fetchall = false)
    {
        return static::adepter()->select($sql, $parameter, $fetchall);
    }

    public static function delete($sql, $parameter = array())
    {
        return static::adepter()->delete($sql, $parameter);
    }

    public static function transaction()
    {
        return static::adepter()->_transaction();
    }

    public static function commit()
    {
        return static::adepter()->_commit();
    }

    public static function rollback()
    {
        return static::adepter()->_rollback();
    }

    public static function escape($str)
    {
        return static::adepter()->escape($str);
    }

}
