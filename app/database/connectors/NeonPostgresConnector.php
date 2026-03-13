<?php

namespace App\Database\Connectors;

use Illuminate\Database\Connectors\PostgresConnector;

class NeonPostgresConnector extends PostgresConnector
{
    protected function getDsn(array $config)
    {
        $dsn = parent::getDsn($config);

        $endpoint = $config['neon_endpoint'] ?? env('NEON_ENDPOINT');

        if (!empty($endpoint)) {
            $dsn .= ";options='endpoint={$endpoint}'";
        }

        return $dsn;
    }
}
