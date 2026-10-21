<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Application;
use App\Core\View;

class DocsController extends Controller {
    public function index(Request $request): void {
        $applications = Application::all('app_name ASC');

        $this->render('docs.index', [
            'pageTitle'    => 'Panduan Integrasi Developer Biru',
            'activeNav'    => 'docs',
            'applications' => $applications,
            'baseUrl'      => View::url(),
        ]);
    }
}

