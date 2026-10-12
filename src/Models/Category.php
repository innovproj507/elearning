<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

/**
 * Modelo Category - Versión API Refactorizada
 */
class Category extends Model
{
    protected string $table = 'categories';

    public function allWithCourseCount(): array
    {
        // Forzando el uso de ApiClient
        $client = \App\Core\ApiClient::getInstance();
        $response = $client->get('/categories', ['with_count' => true]);
        
        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return $response['data'];
        }

        return [];
    }

    public function featured(int $limit = 6): array
    {
        return $this->all(['featured' => true, 'limit' => $limit]);
    }
}
