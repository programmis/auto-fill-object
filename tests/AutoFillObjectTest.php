<?php
/**
 * Created by PhpStorm.
 * User: alfred
 * Date: 05.11.16
 * Time: 17:49
 */

namespace tests;

use lib\AutoFillObject;

/**
 * Class AutoFillObjectTest
 * @package tests
 */
class AutoFillObjectTest extends \PHPUnit_Framework_TestCase
{
    use AutoFillObject;

    /** @var int $test */
    private $test;
    /** @var string $test2 */
    private $test2;
    /** @var DummyObject $dummy */
    private $dummy;
    /** @var DummyObject[] $dummies */
    private $dummies = [];

    public function objectFields()
    {
        return [
            'dummy'   => '\tests\DummyObject',
            'dummies' => [
                'class'  => '\tests\DummyObject',
                'method' => 'addDummy'
            ]
        ];
    }

    public function setDummy($dummy)
    {
        $this->dummy = $dummy;
    }

    public function addDummy($dummy)
    {
        $this->dummies[] = $dummy;
    }

    public function testFillByJson()
    {
        $dummy_text = 'im_test';

        $json = json_encode([
            'test'    => 1,
            'test2'   => $dummy_text,
            'dummy'   => [
                'test'  => 2,
                'test2' => $dummy_text,
                'dummy' => [
                    'test'  => 9,
                    'test2' => $dummy_text
                ]
            ],
            'dummies' => [
                ['test' => 0, 'test2' => $dummy_text],
                ['test' => 1, 'test2' => $dummy_text]
            ]
        ]);
        $this->fillByJson($json);
        $this->assertEquals($this->test, 1, 'Check fill current object field int');
        $this->assertEquals($this->test2, $dummy_text, 'Check fill current object field text');
        $this->assertEquals(
            $this->dummy->getTest(),
            2,
            'Check fill first dummy object field int'
        );
        $this->assertEquals(
            $this->dummy->getTest2(),
            $dummy_text,
            'Check fill first dummy object field string'
        );
        $this->assertEquals(
            $this->dummy->getDummy()->getTest(),
            6,
            'Check fill first child dummy object in dummy object field int from setter'
        );
        $this->assertEquals(
            $this->dummy->getDummy()->getTest2(),
            $dummy_text,
            'Check fill first child dummy object in dummy object field string'
        );
        foreach ($this->dummies as $key => $dummy) {
            $this->assertEquals(
                $dummy->getTest(),
                $key,
                'Check fill second dummy object array field int'
            );
            $this->assertEquals(
                $dummy->getTest2(),
                $dummy_text,
                'Check fill second dummy object array field int'
            );
        }
    }

    public function testAutoFillNotFoundObject()
    {
        try {
            $dummy = new DummyObject2();
            $dummy->fillByJson(json_encode(['dummy' => 'test']));
            $result = '';
        } catch (\Exception $ex) {
            $result = $ex->getMessage();
        }
        $this->assertContains('TestObject', $result);
    }

    public function testAutoFillNotFoundMethod()
    {
        try {
            $dummy = new DummyObject3();
            $dummy->fillByJson(json_encode(['dummy' => 'test']));
            $result = '';
        } catch (\Exception $ex) {
            $result = $ex->getMessage();
        }
        $this->assertContains('failMethod', $result);
    }

    public function testAutoFillFieldNotObjectAndArray()
    {
        try {
            $dummy = new DummyObject4();
            $dummy->fillByJson(json_encode(['dummy' => 'test']));
            $result = '';
        } catch (\Exception $ex) {
            $result = $ex->getMessage();
        }

        $this->assertContains('is not object', $result);
    }
}

// @codingStandardsIgnoreStart
class DummyObject
{
    use AutoFillObject;

    private $test;
    private $test2;
    private $dummy;

    public function setTest($test)
    {
        if ($test == 9) {
            $this->test = 6;
        } else {
            $this->test = $test;
        }
    }

    /** @return DummyObject */
    public function getDummy()
    {
        return $this->dummy;
    }

    public function getTest()
    {
        return $this->test;
    }

    public function getTest2()
    {
        return $this->test2;
    }

    public function objectFields()
    {
        return [
            'dummy' => '\tests\DummyObject',
        ];
    }
}

class DummyObject2
{
    use AutoFillObject;

    private $dummy;

    public function objectFields()
    {
        return [
            'dummy' => 'TestObject',
        ];
    }
}

class DummyObject3
{
    use AutoFillObject;

    private $dummy;

    public function objectFields()
    {
        return [
            'dummy' => [
                'class'  => '\tests\DummyObject',
                'method' => 'failMethod'
            ]
        ];
    }
}

class DummyObject4
{
    use AutoFillObject;

    private $dummy;

    public function objectFields()
    {
        return [
            'dummy' => '\tests\DummyObject'
        ];
    }
}
// @codingStandardsIgnoreEnd