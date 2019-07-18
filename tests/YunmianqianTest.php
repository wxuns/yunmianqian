<?php
/**
 * Created by PhpStorm.
 * Author: wxuns <wxuns@wxuns.cn>
 * Link: http://wxuns.cn
 * Date: 2019/7/17
 * Time: 21:44
 */

namespace Wxuns\Yunmianqian\Tests;

use PHPUnit\Framework\TestCase;
use Wxuns\Yunmianqian\Yunmianqian;

class YunmianqianTest extends TestCase
{
    public function testOrderWithInvalidType()
    {
        $yunmianqian = new Yunmianqian('app_id','app_secret');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type value(true/false): foo');

        $yunmianqian->order([],true,'floor');
        $this->fail('Failed to assert Order throw exception with invalid argument.');
    }

    public function testGetHttpClient()
    {

    }
    public function testsetGuzzleOptions()
    {}
    public function testOrder()
    {}
}
