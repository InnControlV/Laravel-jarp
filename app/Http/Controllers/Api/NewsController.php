<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
class NewsController extends Controller
{
    //
    public function index(Request $request)
{
    $user_id = $request->user_id;
    $limit = (int) $request->input('limit', 20);
    $lastId = $request->input('cursor');

    // ✅ If news_id is passed, return just that one news item
    if ($request->filled('news_id')) {
        $newsItem = News::find($request->news_id);

        if (!$newsItem) {
            return response()->json([
                'error' => true,
                'message' => 'News not found',
                'code' => 404,
            ]);
        }

        $created = Carbon::parse($newsItem->created_at);
        $diff = $created->diff(Carbon::now());
        $return_date = $this->dateformatesmall($diff);

        $isRead = DB::table('jarp_log')
            ->where('user_id', $user_id)
            ->where('product_id', $newsItem->id)
            ->exists();

        $isBookmarked = DB::table('bookmarks')
            ->where('user_id', $user_id)
            ->where('product_id', $newsItem->id)
            ->exists();

        return response()->json([
            'error' => false,
            'data' => [[
                'category'       => $newsItem->category,
                'title'          => $newsItem->title,
                'image'          => $newsItem->image ? asset('storage/' . $newsItem->image) : '',
                'short_description' => $newsItem->short_description,
                'details'        => $newsItem->details,
                'language'       => $newsItem->language,
                'location'       => $newsItem->location,
                'created_at'     => $created,
                'upload_time'    => $return_date,
                'tag'            => $newsItem->tag,
                'id'             => $newsItem->id,
                'read'           => $isRead,
                'bookmark'       => $isBookmarked,
                'metadata'       => [
                    'author'    => $newsItem->added_by,
                    'source'    => $newsItem->refer_from,
                    'sourceURL' => $newsItem->link,
                ],
            ]],
            'next_cursor' => "",
            'code' => 200,
        ]);
    }

    // ✅ Paginated News List with Filters
	$query = News::query();

    if ($request->filled('language')) {
        $query->where('language', $request->language);
    }

    if ($request->filled('location')) {
        $query->where('location', $request->location);
    }

    if ($request->filled('category')) {
        $query->where('category', $request->category);
    }

    if ($request->filled('referFrom')) {
        $query->where('refer_from', $request->referFrom);
    }

    if ($request->filled('keyword')) {
        $keyword = $request->keyword;
        $query->where(function ($q) use ($keyword) {
            $q->where('title', 'like', "%$keyword%")
              ->orWhere('short_description', 'like', "%$keyword%")
              ->orWhere('details', 'like', "%$keyword%")
              ->orWhere('category', 'like', "%$keyword%")
              ->orWhere('location', 'like', "%$keyword%")
              ->orWhere('refer_from', 'like', "%$keyword%")
              ->orWhere('language', 'like', "%$keyword%")
              ->orWhere('added_by', 'like', "%$keyword%")
              ->orWhere('updated_by', 'like', "%$keyword%");
        });
    }

    if ($lastId) {
        $query->where('id', '>', $lastId);
    }

    $newsList = $query->orderBy('id')->limit($limit)->get();

    $newsIds = $newsList->pluck('id')->toArray();

    $readProducts = DB::table('jarp_log')
        ->where('user_id', $user_id)
        ->whereIn('product_id', $newsIds)
        ->pluck('product_id')
        ->toArray();

    $bookmarkedProducts = DB::table('bookmarks')
        ->where('user_id', $user_id)
        ->whereIn('product_id', $newsIds)
        ->pluck('product_id')
        ->toArray();

    $newsData = $newsList->map(function ($item) use ($readProducts, $bookmarkedProducts) {
        $created = Carbon::parse($item->created_at);
        $diff = $created->diff(Carbon::now());
        $return_date = $this->dateformatesmall($diff);

        return [
            'category'       => $item->category,
            'title'          => $item->title,
            'image'          => $item->image ? asset('storage/' . $item->image) : '',
            'short_description' => $item->short_description,
            'details'        => $item->details,
            'language'       => $item->language,
            'location'       => $item->location,
            'created_at'     => $created,
            'upload_time'    => $return_date,
            'tag'            => $item->tag,
            'id'             => $item->id,
            'read'           => in_array($item->id, $readProducts),
            'bookmark'       => in_array($item->id, $bookmarkedProducts),
            'metadata'       => [
                'author'    => $item->added_by,
                'source'    => $item->refer_from,
                'sourceURL' => $item->link,
            ],
        ];
    });

    $nextCursor = $newsList->count() === $limit ? $newsList->last()->id : null;

    return response()->json([
        'error' => false,
        'data' => $newsData,
        'next_cursor' => $nextCursor,
        'code' => 200,
    ]);
}

  public function dateformatesmall($diff){
        if ($diff->y > 0) {
            $created_diff = $diff->y . 'y ago';
        } elseif ($diff->m > 0) {
            $created_diff = $diff->m . 'mo ago';
        } elseif ($diff->d > 0) {
            $created_diff = $diff->d . 'd ago';
        } elseif ($diff->h > 0) {
            $created_diff = $diff->h . 'h ago';
        } elseif ($diff->i > 0) {
            $created_diff = $diff->i . 'm ago';
        } else {
            $created_diff = 'just now';
        }
        return $created_diff;
    }
}
