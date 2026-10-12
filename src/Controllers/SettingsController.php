<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index(array $params = []): void
    {
        $model = new Setting();
        $this->view('admin.settings', [
            'title'    => 'Configuración',
            'layout'   => 'dashboard',
            'settings' => $model->all(),
        ]);
    }

    public function update(array $params = []): void
    {
        $this->verifyCsrf();
        $model = new Setting();
        $all   = $model->all();

        $data = [];
        foreach ($all as $group => $items) {
            foreach ($items as $key => $row) {
                if ($row['type'] === 'boolean') {
                    $data[$key] = isset($_POST[$key]) ? '1' : '0';
                } else {
                    $data[$key] = $this->sanitize($this->input($key, ''));
                }
            }
        }

        $model->setMany($data);
        $this->flashSuccess('Configuración guardada correctamente.');
        $this->redirect('/admin/configuracion');
    }
}
