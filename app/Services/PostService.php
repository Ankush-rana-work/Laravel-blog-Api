<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;

class PostService
{
    /**
     * savePost
     *
     * @param  mixed $request
     * @return void
     */
    public function savePost($request)
    {
        $user = Auth::user();
        $post = new Post();
        $post->title        = $request->title;
        $post->content      = $request->content;
        $post->user_id      = $user->id;
        $post->cat_id       = $user->cat_id;
        $post->status       = 'active';

        // saving post
        if ($post->save()) 
        {
            $total_tags = [];

            if ($request->tags)
            {
                // looping total tags
                foreach ($request->tags as $tag) {

                    $tags = Tag::firstOrNew(['name' =>  trim($tag)]);
                    $tags->name = trim($tag);
                    $tags->save();
                    $total_tags[] = $tags->id;
                }

                // adding all tags to pviot table of post and tags
                $post->tags()->sync($total_tags);
            }

            if ($request->hasFile('media')) 
            {
                $post->addMediaFromRequest('media')
                    ->toMediaCollection('posts');
            }

            return $post;
        }

        return [];
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $post_id
     * @return void
     */
    public function updatePost($request, $post_id)
    {
        $user               = Auth::user();
        $post               = Post::findOrFail($post_id);
        $post->title        = $request->title;
        $post->content      = $request->content;
        $post->user_id      = $user->id;

        // updateting post data 
        if ($post->save()) {

            $total_tags = [];

            if ($request->tags)
            {
                // looping total tags
                foreach ($request->tags as $tag) {

                    $tags = Tag::firstOrNew(['name' =>  trim($tag)]);
                    $tags->name = trim($tag);
                    $tags->save();
                    $total_tags[] = $tags->id;
                }

                // adding all tags to pviot table of post and tags
                $post->tags()->sync($total_tags);
            }
            
            // checking if request has file
            if ($request->hasFile('media')) {
                // this will remove previous images
                $post->clearMediaCollection('posts');
                // this will upload new media 
                $post->addMediaFromRequest('media')
                    ->toMediaCollection('posts');
            }

            return $post;
        }

        return [];
    }

    public function deletePost($request, $post_id)
    {
        $post = Post::findOrFail($post_id);

        // updateting post data 
        if ($post->delete()) {
            // deleting all assosicated tags with post
            $post->tags()->detach();
            // this will remove previous images
            $post->clearMediaCollection('posts');
            // once everything goes perefect it will commit data to database
            return true;
        }

        return false;
    }
}
