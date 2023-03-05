<?php

namespace Webi\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Webi\Http\Requests\WebiChangePasswordRequest;

class WebiPassChange extends Controller
{
	function index(WebiChangePasswordRequest $request)
	{
		$valid = $request->validated();

		if (Auth::check()) {
			if (Hash::check($valid['password_current'], Auth::user()->password)) {

				User::where([
					'email' => Auth::user()->email
				])->update([
					'password' => Hash::make($request->input('password')),
					'ip' => $request->ip()
				]);

				return response()->success('Password has been updated.');
			} else {
				return response()->errors('Invalid current password.');
			}
		}

		return response()->errors('Password has not been updated.');
	}
}
