<?php

namespace lib;

/**
 * Auto fill current object from json data
 *
 * Class AutoFillObject
 * @package lib
 */
trait AutoFillObject
{
    public function fillByJson($json)
    {
        $var = get_object_vars($this);
        foreach ($var as $key => $value) {
            if (isset($json->$key)) {
                //fill json objects array
                $objectFields = $this->objectFields();
                if (is_array($json->$key)
                    && isset($json->$key[0])
                    && is_object($json->$key[0])
                    && array_key_exists($key, $objectFields)
                    && is_array($objectFields[$key])
                    && array_key_exists('class', $objectFields[$key])
                    && array_key_exists('method', $objectFields[$key])
                ) {
                    $class  = $objectFields[$key]['class'];
                    $method = $objectFields[$key]['method'];
                    if (class_exists($class)
                        && method_exists($this, $method)
                        && method_exists($class, 'fillByJson')
                    ) {
                        foreach ($json->$key as $item) {
                            $objectClass = new $class();
                            $objectClass->fillByJson($item);
                            $this->$method($objectClass);
                        }
                    }
                } elseif (is_object($json->$key)
                    && array_key_exists($key, $objectFields)
                ) {
                    //fill json object
                    $class  = $objectFields[$key];
                    $method = UpperCase::makeSetterMethodByField($key);
                    if (class_exists($class)
                        && method_exists($this, $method)
                        && method_exists($class, 'fillByJson')
                    ) {
                        $objectClass = new $class();
                        $objectClass->fillByJson($json->$key);
                        $this->$method($objectClass);
                    }
                } else {
                    $this->$key = $json->$key;
                }
            } else {
                continue;
            }
        }
    }

    /**
     * mapping field names objects
     *
     * @return [
     *      'field' => 'name\space\path\to\class',
     *      'fields' => [
     *          'class' => 'name\space\path\to\class2',
     *          'method' => 'addField'
     *      ]
     * ]
     */
    public function objectFields()
    {
        return [];
    }
}
