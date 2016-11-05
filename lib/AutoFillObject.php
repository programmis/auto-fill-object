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
        if (is_string($json)) {
            $json = json_decode($json);
        }
        $var = get_object_vars($this);
        foreach ($var as $key => $value) {
            if (isset($json->$key)) {
                //fill json objects array
                $objectFields = $this->objectFields();
                if (array_key_exists($key, $objectFields)) {
                    if (!is_array($objectFields[$key])
                        && class_exists($objectFields[$key])
                    ) {
                        $class = $objectFields[$key];
                    } elseif (isset($objectFields[$key]['class'])
                        && class_exists($objectFields[$key]['class'])
                    ) {
                        $class = $objectFields[$key]['class'];
                    } else {
                        throw new \Exception(
                            'Class "'
                            . (is_array($objectFields[$key]) ? serialize($objectFields[$key]) : $objectFields[$key])
                            . '" is not found for field "' . $key . '"'
                        );
                    }
                    if (!method_exists($class, 'fillByJson')) {
                        throw new \Exception('Please use trait "AutoFillObject" in class "' . $class . '"');
                    }
                    $method = '';
                    if (isset($objectFields[$key]['method'])) {
                        $method = $objectFields[$key]['method'];
                        if (!method_exists($this, $method)) {
                            throw new \Exception(
                                'Method "' . $method . '" is not found in class "' . get_class() . '"'
                            );
                        }
                    }
                    if (is_array($json->$key)) {
                        foreach ($json->$key as $item) {
                            /** @var AutoFillObject $objectClass */
                            $objectClass = new $class();
                            $objectClass->fillByJson($item);
                            if ($method) {
                                $this->$method($objectClass);
                            } else {
                                array_push($this->$key, $objectClass);
                            }
                        }
                    } elseif (is_object($json->$key)) {
                        if (!$method) {
                            $method = UpperCase::makeSetterMethodByField($key);
                            if (!method_exists($this, $method)) {
                                $method = '';
                            }
                        }
                        $objectClass = new $class();
                        $objectClass->fillByJson($json->$key);
                        if ($method) {
                            $this->$method($objectClass);
                        } else {
                            $this->$key = $objectClass;
                        }
                    } else {
                        throw new \Exception(
                            'Field "' . $key . '" is not object and array, and present in "objectFields" array'
                        );
                    }
                } else {
                    $method = UpperCase::makeSetterMethodByField($key);
                    if (!method_exists($this, $method)) {
                        $method = '';
                    }
                    if ($method) {
                        $this->$method($json->$key);
                    } else {
                        $this->$key = $json->$key;
                    }
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
     *              'class' => 'name\space\path\to\class2',
     *              'method' => 'addField'
     *      ]
     * ]
     */
    public function objectFields()
    {
        return [];
    }
}
