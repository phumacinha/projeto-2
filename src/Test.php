<?php

/*
    sadsdasda
 * sadsad

 */

class Test {
    public function __construct() {
        throw new \Exception('Not implemented');
    }

    public function method1() {
        return $this;
    }

    public function method2() {
        return $this;
    }
}

$test = new Test();
$test->method1()
    ->method2();

echo "O test: {$test->method1()}";
$sample = <<<TEST
aaa {$test}
TEST;
