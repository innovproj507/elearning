<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Setting extends Model
{
    protected string $table = 'settings';

    public function getByKey(string $key, $default = null)
    {
        $response = $this->api->get("/settings/{$key}");
        return ($response && $response['status'] === 'success') ? $response['data'] : $default;
    }
}
