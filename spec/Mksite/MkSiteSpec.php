<?php

namespace spec\Mksite;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MkSiteSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Mksite\MkSite');
    }
}
