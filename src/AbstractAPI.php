<?php
/**
 * author: draguo
 */

namespace Draguo\Opensearch;

use Draguo\Opensearch\Exceptions\Exception;
use Draguo\Opensearch\Supports\Config;
use Draguo\Opensearch\Supports\HasHttpRequest;
use Psr\Http\Message\RequestInterface;

abstract class AbstractAPI
{
    use HasHttpRequest;

    protected $config;

    public function __construct(array $config)
    {
        $this->config = new Config($config);
    }

    protected function get($api, $query, $options = [])
    {
        $options['query'] = $query;
        return $this->requestJSON('GET', $api, $options);
    }

    protected function requestJSON($method, $api, $options)
    {
        $api = $this->config->get('host') . $api;
        $this->pushMiddleware($this->addAuthorization($this->config->get('app_id'), $this->config->get('secret')));

        $response = $this->request($method, $api, $options)->getBody();
        return $this->checkHasErrors(json_decode(strval($response), true));
    }

    private function checkHasErrors($result)
    {
        if (isset($result['errors'])) {
            throw new Exception(json_encode($result['errors']), 500);
        }
        return $result;
    }

    protected function addAuthorization($id, $secret)
    {
        return function (callable $handler) use ($id, $secret) {
            return function (
                RequestInterface $request,
                array $options
            ) use ($handler, $id, $secret) {
                $params['method'] = $request->getMethod();
                $params['request_path'] = $request->getUri()->getPath();
                $params['query'] = $request->getUri()->getQuery();
                $headers = $this->signature($id, $secret, $params);
                foreach ($headers as $key => $header) {
                    $request = $request->withHeader($key, $header);
                }
                return $handler($request, $options);
            };
        };
    }

    private function signature($id, $secret, $params)
    {
        // 签名过程
        $params['date'] = gmdate('Y-m-d\TH:i:s\Z');
        $params['content_md5'] = "";
        $params['opensearch_headers']['X-Opensearch-Nonce'] = intval(microtime(true) * 1000) . mt_rand(10000, 99999);
        $params['content_type'] = 'application/json';

        $string = '';
        $string .= strtoupper($params['method']) . "\n";
        $string .= $params['content_md5'] . "\n";
        $string .= $params['content_type'] . "\n";
        $string .= $params['date'] . "\n";

        // todo 过滤参数 key 为 Signature 或 null 的
        $headers = $params['opensearch_headers'];
        foreach ($headers as $key => $value) {
            $string .= strtolower($key) . ":" . $value . "\n";
        }

        $resource = str_replace('%2F', '/', rawurlencode($params['request_path']));
        parse_str($params['query'], $query);
        uksort($query, 'strnatcasecmp');
        $queryString = http_build_query($query);
        $canonicalizedResource = $resource;

        if (!empty($queryString)) {
            $canonicalizedResource .= '?' . $queryString;
        }

        $string .= $canonicalizedResource;

        $signature = base64_encode(hash_hmac('sha1', $string, $secret, true));

        $headers = [
            'Content-Type' => 'application/json',
            'Date' => gmdate('Y-m-d\TH:i:s\Z'),
            'Content-Md5' => $params['content_md5'],
            'Authorization' => "OPENSEARCH {$id}:{$signature}",
            'X-Opensearch-Nonce' => $params['opensearch_headers']['X-Opensearch-Nonce'],
        ];

        return $headers;
    }
}