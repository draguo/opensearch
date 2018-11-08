<?php

namespace Draguo\Opensearch\Supports;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;

trait HasHttpRequest
{

    protected $middlewares = [];
    protected $handlerStack;

    /**
     * Add a middleware.
     *
     * @param callable $middleware
     * @param null|string $name
     *
     * @return $this
     */
    public function pushMiddleware(callable $middleware, $name = null)
    {
        if (!is_null($name)) {
            $this->middlewares[$name] = $middleware;
        } else {
            array_push($this->middlewares, $middleware);
        }
        return $this;
    }

    /**
     * @param $endpoint
     * @param array $query
     * @param array $headers
     * @return array|string
     */
    protected function get($endpoint, $query = [], $headers = [])
    {
        return $this->request('get', $endpoint, [
            'headers' => $headers,
            'query' => $query,
        ]);
    }

    /**
     *
     * @param string $endpoint
     * @param string|array $data
     * @param array $options
     *
     * @return array|string
     */
    protected function post($endpoint, $data, $options = [])
    {
        if (!is_array($data)) {
            $options['body'] = $data;
        } else {
            $options['form_params'] = $data;
        }

        return $this->request('post', $endpoint, $options);
    }

    protected function postJson($endpoint, $data, $options = [])
    {
        $options['json'] = $data;
        return $this->request('post', $endpoint, $options);
    }

    /**
     * send request.
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return array|string
     */
    protected function request($method, $uri, $options = [])
    {
        $options = array_merge(self::$defaults, $options, ['handler' => $this->getHandlerStack()]);
        $response = $this->getHttpClient()->request($method, $uri, $options);
        $response->getBody()->rewind();

        return $response;
    }

    /**
     * @return Client
     */
    protected function getHttpClient()
    {
        return new Client();
    }

    /**
     * Build a handler stack.
     *
     * @return \GuzzleHttp\HandlerStack
     */
    public function getHandlerStack()
    {
        if ($this->handlerStack) {
            return $this->handlerStack;
        }
        $this->handlerStack = HandlerStack::create();
        foreach ($this->middlewares as $name => $middleware) {
            $this->handlerStack->push($middleware, $name);
        }
        return $this->handlerStack;
    }

}