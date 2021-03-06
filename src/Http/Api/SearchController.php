<?php

namespace Haxibiao\Breeze\Http\Api;

use App\Http\Controllers\Controller;
use App\Query;
use App\Querylog;

class SearchController extends Controller
{
    public function hotQueries()
    {
        $queries = Query::where('status', '>=', 0)
            ->where('created_at', '>=', now()->subDays(30)) //1个月内的热搜
            ->orderBy('hits', 'desc')->paginate(10);
        foreach ($queries as $query) {
            $query->q = str_limit($query->query, 14, '');
        }
        return $queries;
    }

    public function latestQuerylog()
    {
        $user      = request()->user();
        $querylogs = $user->querylogs()->orderBy('updated_at', 'desc')->take(5)->get();
        return $querylogs;
    }

    public function clearQuerylogs()
    {
        $user = request()->user();
        $user->querylogs()->orderBy('updated_at', 'desc')->delete();
        return [];
    }

    public function removeQuerylog($id)
    {
        return Querylog::destroy($id);
    }
}
