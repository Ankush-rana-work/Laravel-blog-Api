<?php

namespace App\Services;

use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentService
{
    /**
     * saveCategory
     *
     * @param  mixed $request
     * @return void
     */
    public function saveComment($request)
    {
        $user               = Auth::user();
        $comment = new Comment();
        $comment->user_id = $user->id;
        $comment->post_id = $request->post_id;
        $comment->text    = $request->text;  
        $comment->parent_id = $request->parent_id?? null;

        // save category
        if ($comment->save()) {
                
            if ($request->hasFile('media')) {
                $comment->addMediaFromRequest('media')
                    ->toMediaCollection('comment');
            }

            return $comment;
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
    public function updateComment($request, $comment_id)
    {
        $user          = Auth::user();
        $comment       = Comment::findOrFail($comment_id);
        $comment->user_id = $user->id;
        $comment->text    = $request->text;  

        // updateting post data 
        if ($comment->save()) {

            if ($request->hasFile('media')) {
                // this will remove previous images
                $comment->clearMediaCollection('comment');

                $comment->addMediaFromRequest('media')
                    ->toMediaCollection('comment');
            }

            return $comment;
        }

        return [];
    }

    public function deleteComment($request, $cat_id)
    {
        $comment = Comment::findOrFail($cat_id);

        // updateting post data 
        if ($comment->delete()) {
            // this will remove previous images
            $comment->clearMediaCollection('comment');
            // once everything goes perefect it will commit data to database
            return true;
        }

        return false;
    }

}
