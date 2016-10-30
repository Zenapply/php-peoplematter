<?php

namespace Zenapply\PeopleMatter\Tests;

use Zenapply\PeopleMatter\PeopleMatter;

class PeopleMatterTest extends TestCase
{
    protected $request;

    public function testItCreatesAnInstanceOfHttpRequest()
    {
        $r = new PeopleMatter("username", "password", "alias");
        $this->assertInstanceOf(PeopleMatter::class, $r);
    }
}
