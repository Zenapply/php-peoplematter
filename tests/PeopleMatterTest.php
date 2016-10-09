<?php

namespace Zenapply\PeopleMatter\Api\Tests;

use Zenapply\PeopleMatter\Api\PeopleMatter;

class PeopleMatterTest extends TestCase
{
    protected $request;

    public function testItCreatesAnInstanceOfHttpRequest()
    {
        $r = new PeopleMatter("token");
        $this->assertInstanceOf(PeopleMatter::class, $r);
    }
}
