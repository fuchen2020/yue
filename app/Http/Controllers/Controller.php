<?php

namespace App\Http\Controllers;

use App\Http\Components\Code;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    protected $request;
    public function __construct(Request $request)
    {
        $this->request   = $request;
    }
    /**
     * send error json string
     * @param int $code
     * @param string $message
     * @return $this|\Illuminate\Http\JsonResponse
     */
    public function sendError($code = 1, $message = '')
    {
        $method   = $this->request->input('method');
        $callback = $this->request->input('callback');
        if($method === 'jsonp' && $callback)
            return Response()->jsonp($callback, ['code' => $code, 'msg' => $message ? $message : Code::getError($code),'status' => false]);
        $headers = ['content-type' => 'application/json'];
        return Response()->json(['code' => $code, 'msg' => $message ? $message : Code::getError($code),'status' => false])
            ->withHeaders($headers);
    }
    /**
     * send success json string
     * @param array $data
     * @return $this|\Illuminate\Http\JsonResponse
     */
    public function sendJson($code = 0,$message = '',$data = [])
    {
        $method   = $this->request->input('method');
        $callback = $this->request->input('callback');
        if($method === 'jsonp' && $callback)
            return Response()->jsonp($callback, ['code' => $code, 'msg' => $message,'status' => true , 'data' => $data]);
        $headers = ['content-type' => 'application/json'];
        return Response()->json(['code' => $code, 'msg' => $message,'status' => true , 'data' => $data])
            ->withHeaders($headers);
    }


}
