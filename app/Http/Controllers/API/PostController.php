<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Controllers\API\BaseController as BaseController;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data['posts'] = Post::all();

        return $this->sendResponse($data, 'All Posts Data');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|mimes:jpg,jpeg,gif,png',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Errors',
                'errors' => $validator->errors()->all()
            ], 401);

            return $this->sendError('Validation Errors', $validator->errors()->all(), 401);
        }

        $img = $request->file('image');
        $ext = $img->getClientOriginalExtension();
        $imageName = time() . '.' . $ext;
        $img->move(public_path() . '/uploads/', $imageName);

        $post = Post::create([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imageName
        ]);


        return $this->sendResponse($post, 'Post Created Successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data['post'] = Post::where(['id' => $id])->get();

        return $this->sendResponse($data, 'Single Post');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|mimes:jpg,jpeg,gif,png',
        ]);

        if ($validator->fails()) {

            return $this->sendError('Validation Errors', $validator->errors()->all(), 401);
        }
        $postImage = Post::select('id', 'image')->where(['id' => $id])->get();
        if ($request->image != '') {
            $path = public_path() . '/uploads/';
            if ($postImage[0]->image != '' && $postImage[0]->image != null) {
                $old_file = $path . $postImage[0]->image;
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
            $img = $request->file('image');
            $ext = $img->getClientOriginalExtension();
            $imageName = time() . '.' . $ext;
            $img->move(public_path() . '/uploads', $imageName);
        } else {
            $imageName = $postImage[0]->image;
        }


        $post = Post::where(['id' => $id])->update([
            'title' => $request->title,
            'description' => $request->description,
            'image' => $imageName
        ]);

        return $this->sendResponse($post, 'Post updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $imagePath = Post::select('image')->where(['id' => $id])->get();
        $file_path = public_path() . '/uploads/' . $imagePath[0]['image'];
        unlink($file_path);
        $post = Post::where(['id' => $id])->delete();

        return $this->sendResponse($post, 'Your Post has been removed');
    }
}
