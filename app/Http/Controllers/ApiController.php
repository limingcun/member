<?php

namespace App\Http\Controllers;

use Dingo\Api\Routing\Helpers;
use EasyWeChat;
use App\Support\Response;
use App\Support\Parameters;

abstract class ApiController extends Controller
{
    use Helpers;
    /**
     * API response helper.
     *
     * @var \App\Support\Response
     */
    protected $response;

    /**
     * API parameters helper.
     *
     * @var \App\Support\Parameters
     */
    protected $parameters;

    /**
     * 企业微信扫码应用
     *
     * @var EasyWeChat
     */
    protected $scanWork;

    /**
     * Creates a new class instance.
     *
     * @param Response   $response
     * @param Parameters $parameters
     */
    public function __construct(Response $response, Parameters $parameters)
    {
        $this->response = $response;
        $this->parameters = $parameters;

        $this->scanWork = EasyWeChat::work();
    }
}
