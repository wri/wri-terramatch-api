<?php

namespace Tests\Unit;

use App\Validators\ArrayObjectTestValidator;
use Tests\TestCase;

class ArrayObjectExtensionTest extends TestCase
{
    private function define(): Void
    {
        if (!class_exists("App\\Validators\\ArrayObjectTestValidator")) {
            eval(<<<HEREDOC
                namespace App\Validators {
                    class ArrayObjectTestValidator extends Validator
                    {
                        public const TEST = [
                            "foo" => "required|array|array_object",
                            "foo.*" => "required|string"
                        ];
                    }
                }
HEREDOC);
        }
    }

    public function testObjectPass()
    {
        $this->define();
        ArrayObjectTestValidator::validate("TEST", ["foo" => ["a" => "bar", "b" => "baz", "c" => "qux"]]);
        $this->assertTrue(true);
    }

    public function testArraysFail()
    {
        $this->define();
        $this->expectException("Illuminate\\Validation\\ValidationException");
        ArrayObjectTestValidator::validate("TEST", ["foo" => ["bar", "baz", "qux"]]);
    }
}
