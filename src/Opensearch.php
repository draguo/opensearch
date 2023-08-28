<?php

/**
 * author: draguo
 */

namespace Draguo\Opensearch;

class Opensearch extends AbstractAPI
{

    /**
     * 
     */
    public function search($query)
    {
        $app_name = $this->config->get('app_name');

        return $this->get("/v3/openapi/apps/{$app_name}/search", [
            'query' => $query,
        ]);
    }

    /**
     * 搜索下拉提示
     * @param $query
     * @param int $hit 条数
     * @param string|null $suggestName
     * @return json
     */
    public function suggest($query, $hit = 10, $suggestName = null)
    {
        $app_name = $this->config->get('app_name');
        $suggestName = $suggestName ? $suggestName : $this->config->get('suggest_name');
        return $this->get("/v3/openapi/apps/{$app_name}/suggest/{$suggestName}/search", [
            'query' => $query,
            'hit' => $hit,
        ]);
    }
}
