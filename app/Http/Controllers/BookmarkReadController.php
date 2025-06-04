<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

    //
    class BookmarkReadController extends Controller
{
    // Add or remove a bookmark
    public function toggleBookmark(Request $request)
    {
       $validator = Validator::make($request->all(), [
                'product_type' => 'required|string|in:news,movie,shopping',
                'product_id' => 'required',
                'user_id' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validator->errors()->first(),
                    'code' => 422
                ], 422);
            }
             $user_id = $request->user_id;
            $product_type = $request->product_type;
            $product_id = $request->product_id;

        $bookmark = DB::table('bookmarks')
            ->where('user_id', $user_id)
            ->where('product_type', $product_type)
            ->where('product_id', $product_id)
            ->first();

        if ($bookmark) {
            // Remove bookmark
            DB::table('bookmarks')->where('id', $bookmark->id)->delete();

            return response()->json(['error' => false, 'message' => 'Bookmark removed', 'code' => 200]);
        } else {
            // Add bookmark
            DB::table('bookmarks')->insert([
                'user_id' => $user_id,
                'product_type' => $product_type,
                'product_id' => $product_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json(['error' => false, 'message' => 'Bookmark added', 'code' => 201]);
        }
    }

    // Log a read/hit action for a product
    public function logRead(Request $request)
    {
       $validator = Validator::make($request->all(), [
                'product_type' => 'required|string|in:news,movie,shopping',
                'product_id' => 'required',
                'user_id' => 'required',
            ]);
    
            if ($validator->fails()) {
                return response()->json([
                    'error' => true,
                    'message' => $validator->errors()->first(),
                    'code' => 422
                ], 422);
            }
            $user_id = $request->user_id;
            $product_type = $request->product_type;
            $product_id = $request->product_id;

        // Check if already logged
        $exists = DB::table('jarp_log')
            ->where('user_id', $user_id)
            ->where('product_type', $product_type)
            ->where('product_id', $product_id)
            ->exists();

        if ($exists) {
            return response()->json(['error' => false, 'message' => 'Already read', 'code' => 200]);
        }

        DB::table('jarp_log')->insert([
            'user_id' => $user_id,
            'product_type' => $product_type,
            'product_id' => $product_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'hit_at' => now(),
        ]);

        return response()->json(['error' => false, 'message' => 'Read logged', 'code' => 201]);
    }
}
