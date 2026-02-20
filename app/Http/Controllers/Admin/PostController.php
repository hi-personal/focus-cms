<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\UserMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Method index
     *
     * @param Request $request [explicite description]
     * @param string $postType [explicite description]
     *
     * @return void
     */
    public function index(Request $request, string $postType)
    {
        $query = Post::query();

        // Jelenlegi felhasználó ID lekérése
        $userId = auth()->id();

        // Mentett `per_page` érték betöltése az adatbázisból
        $perPage = UserMeta::where('user_id', $userId)->where('name', 'per_page')->first()?->value;

        // Ellenőrizzük, hogy az érték a megadott listában szerepel-e
        $perPage = in_array($perPage, [2,3,20, 40, 60, 100, 500]) ? $perPage : 20;

        // Post típus szűrése
        $query->where('post_type_name', $postType);

        // Szerző szűrés
        if ($request->filled('author')) {
            $query->whereHas('author', function ($q) use ($request) {
                $q->where('id', $request->author);
            });
        }

        // Kategória szűrés a `terms` táblából
        if ($request->filled('category')) {
            $query->whereHas('terms', function ($q) use ($request) {
                $q->where('post_taxonomy_name', 'categories')
                ->where('id', $request->category);
            });
        }

        // Állapot szűrés
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Dátumtartomány szűrés
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Rendezés
        if ($request->filled('sort_order')) {
            $query->orderBy('created_at', $request->sort_order);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Lapozás beállítása
        $posts = $query->paginate($perPage)->withQueryString();

        // Felhasználók és kategóriák betöltése
        $users = \App\Models\User::orderBy('name')->get();
        $categories = \App\Models\PostTerm::where('post_taxonomy_name', 'categories')->get()->all();

        return view('admin.posts', compact('posts', 'users', 'categories', 'perPage', 'postType'));
    }

    /**
     * Method createNewPost
     *
     * @param Request $request [explicite description]
     * @param string $postType [explicite description]
     *
     * @return void
     */
    public function createNewPost(Request $request, string $postType)
    {
        // Új bejegyzés létrehozása
        $post = Post::create([
            'title' => 'Új '.$postType,
            'name' => 'uj-'.$postType,
            'user_id' => auth()->id(),
            'status' => 'draft',
            'post_type_name' => $postType,
            'content' => '',
        ]);

        // Cím és slug frissítése
        $post->update([
            'title' => "Új {$postType} - {$post->id}",
            'name' => Str::slug("uj-{$postType}-{$post->id}"),
        ]);

        // Átirányítás a szerkesztő oldalra
        return redirect()->route("post.edit", ['post_type' => $postType, 'post' => $post]);
    }

    /**
     * Method savePerPageSetting
     *
     * @param Request $request [explicite description]
     *
     * @return void
     */
    public function savePerPageSetting(Request $request)
    {
        $userId = auth()->id();
        $perPage = $request->input('per_page');

        if (!in_array($perPage, [2,3,20, 40, 60, 100, 500])) {
            return response()->json(['error' => 'Invalid per_page value'], 400);
        }

        // `user_metas` tábla frissítése vagy létrehozása
        UserMeta::updateOrCreate(
            [
                'user_id' => $userId,
                'name' => 'per_page'
            ],
            ['value' => $perPage]
        );

        return response()->json(['success' => true]);
    }
}