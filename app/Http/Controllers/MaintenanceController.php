<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\App;

class MaintenanceController extends Controller
{
    public function enableMaintenance()
    {
        Artisan::call('cms:maintenance', ['action' => 'on']);

        return back()->with('status', 'Karbantartás mód aktiválva');
    }

    public function disableMaintenance()
    {
        Artisan::call('cms:maintenance', ['action' => 'off']);

        return back()->with('status', 'Karbantartás mód deaktiválva');
    }

    public function checkStatus()
    {
        $isDown = App::isDownForMaintenance();

        return response()->json([
            'maintenance_mode' => $isDown
        ]);
    }
}