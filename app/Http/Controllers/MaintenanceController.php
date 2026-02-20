<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\App;

class MaintenanceController extends Controller
{
    // Karbantartás mód bekapcsolása
    public function enableMaintenance()
    {
        Artisan::call('maintenance on');

        return back()->with('status', 'Karbantartás mód aktiválva');
    }

    // Karbantartás mód kikapcsolása
    public function disableMaintenance()
    {
        Artisan::call('maintenance off');

        return back()->with('status', 'Karbantartás mód deaktiválva');
    }

    public function checkStatus()
    {
        $isDown = App::isDownForMaintenance();

        return response()->json(['maintenance_mode' => $isDown]);
    }
}