<?php

namespace HandlerCore\Tests;

class ExampleTest extends BaseTestCase
{
    /**
     * A simple example test that always passes.
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    /**
     * Test that demonstrates how to use assertions.
     */
    public function testAssertions()
    {
        $this->assertEquals(4, 2 + 2);
        $this->assertNotEquals(5, 2 + 2);
        $this->assertStringContainsString('world', 'hello world');
    }
}