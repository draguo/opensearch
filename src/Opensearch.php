<?php
/**
 * author: draguo
 */

namespace Draguo\Opensearch;

use Draguo\Opensearch\Supports\Config;

class Opensearch extends AbstractAPI
{

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function suggest($query, $hits = 10, $suggestName = null)
    {
        $app_name = $this->config->get('app_name');
        $suggestName = $suggestName ? $suggestName : $this->config->get('suggest_name');
        return $this->requestJSON('get', "/v3/openapi/apps/{$app_name}/suggest/{$suggestName}/search", [
            'query' => $query,
            'hits' => $hits,
        ]);
    }
}