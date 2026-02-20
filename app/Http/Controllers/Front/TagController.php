<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Post;
use App\Models\PostTerm;
use App\Models\PostTermMeta;
use App\Models\PostTermRelationship;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    protected $currentThemeName;

    public function __construct()
    {
        $this->currentThemeName = Option::find('currentThemeName')->value;
    }

    public function index(Request $request): View
    {
        $categories = PostTerm::where('post_taxonomy_name', 'categories')->get();

        return view("theme::categories", [
            'categories'   => $categories,
            'currentTheme' => $this->currentThemeName
        ]);
    }

    public function show(Request $request, $tag): View
    {
        $tagName = $tag;
        $tag = PostTerm::where('post_taxonomy_name', 'tags')->where('name', $tagName)->first();
        $description = PostTermMeta::where('post_term_id', $tag->id)->where('name', 'description')->first()?->value;

        return view("theme::tag", [
            'tag'     => $tag,
            'description' => $description,
            'currentTheme' => $this->currentThemeName
        ]);

    }
}