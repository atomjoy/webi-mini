<?php

namespace Webi\Http\Controllers;

use Exception;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WebiLogout extends Controller
{
	function index(Request $request)
	{
		try {
			if (Auth::check()) {
				Auth::logout();
			}

			$request->session()->flush();
			$request->session()->invalidate();
			$request->session()->regenerateToken();
			session(['locale' => config('app.locale')]);
		} catch (Exception $e) {
			report($e);
			return response()->errors('Logged out error.', 422);
		}

		return response()->success('Logged out.');
	}
}
