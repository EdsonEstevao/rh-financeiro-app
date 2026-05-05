<?php

namespace App\Http\Controllers\RH;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserSearchController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        $users = User::query()
            ->select(['id', 'name', 'email'])
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            })
            ->orderBy('name', 'asc')
            ->limit(12)
            ->get();

        return response()->json($users);
    }
}
