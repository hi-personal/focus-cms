<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Models\Post;

class DynamicPostEditDispatcher extends Controller
{
    private null|string $controllerName;
    private null|object $controller;

    private function getController($postType)
    {
        $postType = match ($postType) {
            'image_container' => 'ImageContainer',
            default           => 'Post',
        };

        return app()->make('App\\Http\\Controllers\\Admin\\Edit' . $postType . 'Controller');
    }

    private function callController($postType, $method, $params)
    {
        return app()->call([$this->getController($postType), $method], $params);
    }

    public function show(Request $request, string $postType, Post $post)
    {
        return callController($postType, 'show', [
            'request' => $request,
            'postType' => $postType,
            'post' => $post
        ]);
    }

    public function update(Request $request, string $postType, Post $post)
    {
        return callController($postType, 'update', [
            'request' => $request,
            'postType' => $postType,
            'post' => $post
        ]);
    }

    public function delete(Request $request, string $postType, Post $post)
    {
        return callController($postType, 'delete', [
            'request' => $request,
            'postType' => $postType,
            'post' => $post
        ]);
    }

    public function preview(Request $request)
    {
        return callController($postType, 'preview', [
            'request' => $request
        ]);
    }

    public function saveTemp(Request $request)
    {
        return callController($postType, 'saveTemp', [
            'request' => $request
        ]);
    }
}
