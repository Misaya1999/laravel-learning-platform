<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use Illuminate\Support\Facades\Storage;


class ProfileController extends Controller
{
    public function profile()
    {
        return view('Admin.Profile');
    }

    public function profile_update(UserRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        // Xử lý password
        if (empty($data['password'])) {
            unset($data['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')
                ->store('uploads/user/avatar', 'public');
        }

        $user->update($data);

        return back()->with('success', 'Cập nhật profile thành công.');
    }
}
