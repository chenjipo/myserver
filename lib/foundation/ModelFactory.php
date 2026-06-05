<?php
namespace YXLib\foundation;

class ModelFactory {
    
    public static $models;

    /**
     * Undocumented function
     *
     * @param string $conn
     * @return Model
     */
    public static function getInstance($conn = ''){
        if(!$conn) return null;
        if(!self::$models[$conn]){
            self::$models[$conn] = new Model($conn);
        }
        return self::$models[$conn];
    }
}