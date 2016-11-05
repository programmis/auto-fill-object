![alt tag](https://travis-ci.org/programmis/auto-fill-object.svg?branch=master)

1) Download composer :
<pre>
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === 'e115a8dc7871f15d853148a7fbac7da27d6c0030b848d9b3dc09e2a0388afed865e6a3d6b3c0fad45c48e2b5fc1196ae') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
</pre>
2) Install:
<pre>
php composer.phar require programmis/auto-fill-object
</pre>
_first find setMethodForField or method specified in objectFields array
and if not finding them then set value to object field_

```php
class Dummy
{
    use lib\AutoFillObject;

    private $i;
    private $str;
    private $dummy;

    public function getDummy()
    {
        return $this->dummy;
    }

    public function setI($i)
    {
        $this->i = $i == 2 ? 0 : $i;
    }

    public function getI()
    {
        return $this->i;
    }

    public function getStr()
    {
        return $this->str;
    }


    public function objectFields()
    {
        return [
            'dummy' => 'Dummy',
        ];
    }
}

$json  = json_encode([
    'i'     => 1,
    'str'   => 'dummy_text',
    'dummy' => [
        'i'   => 2,
        'str' => 'dummy_text_2'
    ]
]);
$dummy = new Dummy();
$dummy->fillByJson($json);

echo $dummy->getI() . "\n"; // 1
echo $dummy->getStr() . "\n"; // dummy_text
echo $dummy->getDummy()->getI() . "\n"; // 0 see in dummy setter
echo $dummy->getDummy()->getStr() . "\n"; // dummy_text_2
```
