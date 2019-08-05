<?php
/**
 * Created by PhpStorm.
 * Author: wxuns <wxuns@wxuns.cn>
 * Link: http://wxuns.cn
 * Date: 2019/7/17
 * Time: 21:44
 */

namespace Wxuns\Yunmianqian\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Wxuns\Yunmianqian\Exceptions\InvalidArgumentException;
use Wxuns\Yunmianqian\Exceptions\MissArgumentException;
use Wxuns\Yunmianqian\Yunmianqian;

class YunmianqianTest extends TestCase
{
    /**
     * 测试签名参数
     * @throws MissArgumentException
     */
    public function testSignWithInvalidArgument()
    {
        $yunmianqian = new Yunmianqian('app_id','app_serect');
        $this->expectException(MissArgumentException::class);
        $this->expectExceptionMessage('Invalid options.');
        $yunmianqian->sign([]);
        $this->fail('Failed to assert sign throw exception with invalid argument.');
    }

    /**
     * 测试order参数，options参数已在签名中测试
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testOrderWithInvalidArgument()
    {
        $yunmianqian = new Yunmianqian('app_id','app_serect');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid response cache:foo');
        $yunmianqian->order([
            'out_order_sn'=>1111,
            'name'=>'测试商品',
            'pay_way'=>'alipay',
            'price'=>10,
            'notify_url'=>'https://...'
        ],'foo');
        $this->fail('Failed to assert cache throw exception with invalid argument.');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid response price_type:foo');
        $yunmianqian->order([
            'out_order_sn'=>1111,
            'name'=>'测试商品',
            'pay_way'=>'alipay',
            'price'=>1,
            'notify_url'=>'https://...'
        ],true,'foo');
        $this->fail('Failed to assert pricetype throw exception with invalid argument.');
    }

    /**
     * 模拟下订单测试
     */
    public function testOrder()
    {
        //模拟返回值
        $response = new Response(200, [], '{"success": true}');
        //客户端模拟，代替order方法中的客户端，不是真的请求
        $client = \Mockery::mock(Client::class);
        $cache = true;$price_type = 'floor';
        $client->shouldReceive('post')
            ->andReturn($response);
        $ymq = \Mockery::mock(Yunmianqian::class,['app_id','app_secret'])->makePartial();
        $ymq->allows()->getHttpClient()->andReturn($client);
        //检测返回值
        $this->assertSame('{"success": true}', $ymq->order([
            'out_order_sn'=>1111,
            'name'=>'测试商品',
            'pay_way'=>'alipay',
            'price'=>1,
            'notify_url'=>'https://...',
            'sign'=>'sign'
        ],$cache,$price_type));
    }

    /**
     * 订单号不可为空
     * @throws InvalidArgumentException
     * @throws \Wxuns\Yunmianqian\Exceptions\HttpException
     */
    public function testQuerySignWithInvalidArgument()
    {
        $yunmianqian = new Yunmianqian('app_id','app_serect');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid response order_sn.');
        $yunmianqian->queryOrder('');
        $this->fail('Failed to assert order_sn throw exception with invalid argument.');
    }
    /**
     * 模拟查询订单测试
     */
    public function testQueryOrder()
    {
        //模拟返回值
        $response = new Response(200, [], '{"success": true}');
        //客户端模拟，代替queryOrder方法中的客户端，不是真的请求
        $client = \Mockery::mock(Client::class);
        $client->shouldReceive('post')
            ->andReturn($response);
        $ymq = \Mockery::mock(Yunmianqian::class,['app_id','app_secret'])->makePartial();
        $ymq->allows()->getHttpClient()->andReturn($client);
        //检测返回值
        $this->assertSame('{"success": true}', $ymq->queryOrder(11111111));
    }
}
