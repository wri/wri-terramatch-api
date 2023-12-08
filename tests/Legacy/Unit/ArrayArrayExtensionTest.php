<?php

namespace Tests\Legacy\Unit;

use App\Validators\ArrayArrayTestValidator;
use Tests\Legacy\LegacyTestCase;

final class ArrayArrayExtensionTest extends LegacyTestCase
{
    private function define(): Void
    {
        if (! class_exists('App\\Validators\\ArrayArrayTestValidator')) {
            eval(<<<HEREDOC
                namespace App\Validators {
                    class ArrayArrayTestValidator extends Validator
                    {
                        public const TEST = [
                            "foo" => "required|array|array_array",
                            "foo.*" => "required|string"
                        ];
                    }
                }
HEREDOC);
        }
    }

    public function testArraysPass(): void
    {
        $this->define();
        ArrayArrayTestValidator::validate('TEST', ['foo' => ['bar', 'baz', 'qux']]);
        $this->assertTrue(true);
    }

    public function testOrderedStringKeysPass(): void
    {
        $this->define();
        ArrayArrayTestValidator::validate('TEST', ['foo' => ['0' => 'bar', '1' => 'baz', '2' => 'qux']]);
        $this->assertTrue(true);
    }

    public function testUnorderedKeysFail(): void
    {
        $this->define();
        $this->expectException('Illuminate\\Validation\\ValidationException');
        ArrayArrayTestValidator::validate('TEST', ['foo' => [0 => 'bar', 1 => 'baz', 3 => 'qux']]);
    }

    public function testStringKeysFail(): void
    {
        $this->define();
        $this->expectException('Illuminate\\Validation\\ValidationException');
        ArrayArrayTestValidator::validate('TEST', ['foo' => ['a' => 'bar', 'b' => 'baz', 'c' => 'qux']]);
    }
}
