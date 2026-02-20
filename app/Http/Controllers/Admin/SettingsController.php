<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Option;
use App\Models\Post;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;

class SettingsController extends Controller
{
    private null|string $currentThemeName = null;

    public function __construct()
    {
        $currentThemeName = Option::find('currentThemeName');
        $this->currentThemeName = empty($currentThemeName) ? null : $currentThemeName->value;
    }

    public function sidebars(Request $request)
    {
        $sidebars = Option::where('name', 'like', "ts_{$this->currentThemeName}_sidebar_%")
            ->get()
            ->pluck('value', 'name');

        return view(
            'admin.settings.sidebars',
            compact('sidebars')
        );
    }

    public function sidebarsUpdate(Request $request)
    {
        $validationRules = config('theme.validation_rules.options.sidebars');
        $keys = collect($validationRules)->keys()->all();
        $params = collect($request->request->all())->only($keys);
        $validated = $request->validate($validationRules);

        foreach($keys as $sidebarName) {
            $optionName = "ts_{$this->currentThemeName}_{$sidebarName}_content";

            Option::updateOrCreate(
                ['name'=>$sidebarName],
                ['value'=>$validated[$sidebarName]]
            );
        }

        return redirect()->route("admin.settings.sidebars")->with('success', 'Összes beállítás sikeresen elmentve!');
    }

    public function website(Request $request)
    {
        $pages = Post::where('post_type_name', 'page')->where('status', 'published')->get()->all();
        $maintenanceStatus = File::exists(storage_path('framework/.maintenance'));

        $validationRules = config('validation_rules.options.website_settings');
        $keys = collect($validationRules)->keys()->all();
        $data = array_fill_keys($keys, null);
        $defaults = Option::getDefaults();

        //$websiteSettings = Option::where('name', 'like', 'website_setting_%')->get()->keyBy('name');
        $websiteSettings = Option::whereIn('name', $keys)->get()->pluck('value', 'name')->toArray();
        $websiteSettings = array_merge($data, $defaults, $websiteSettings);

        return view(
            'admin.settings.website', array_merge(
                compact('pages', 'maintenanceStatus'),
                $websiteSettings
            )
        );
    }

    public function websiteSettingsUpdate(Request $request)
    {
        $validationRules = config('validation_rules.options.website_settings');
        $keys = array_keys($validationRules);
        $params = $request->only($keys);

        // Egyéni validator létrehozása
        $validator = Validator::make($params, $validationRules);

        // Valid elemek kinyerése
        $validated = $validator->valid();

        // Valid elemek mentése
        foreach ($validated as $key => $value) {
            Option::updateOrCreate(
                ['name' => $key],
                ['value' => $value]
            );
        }

        // Hibák kezelése
        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->with('error', 'Néhány beállítás nem mentődött el. A hibás mezőket javítsd!')
                ->with('success', count($validated) > 0 ? 'A hibátlan beállítások sikeresen elmentve!' : null)
                ->withInput();
        }

        return back()->with('success', 'Összes beállítás sikeresen elmentve!');
    }
}