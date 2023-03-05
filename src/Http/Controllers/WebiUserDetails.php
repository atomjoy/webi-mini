<?php

namespace Webi\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebiUserDetails extends Controller
{
	function index(Request $request)
	{
		if (Auth::check()) {
			return response()->success([
				'message' => trans('User details.'),
				'user' => request()->user(),
				'ip' => request()->ip()
			]);
		}

		return response()->errors([
			'message' => trans('Not authenticated.'),
			'user' => null
		]);
	}
}
