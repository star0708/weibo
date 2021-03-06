<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{

    public function __construct()
    {
        $this->middleware('guest', [
            'only' => ['create', 'index']
        ]);

        $this->middleware('throttle:10,10', [
            'only' => ['store']
        ]);
    }

    public function create()
    {
        return view('session.create');
    }

    public function store(Request $request)
    {
        $validator = $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);

        if (Auth::attempt($validator, $request->has('remember'))) {
            if (Auth::user()->activated){
                session()->flash('success', '欢迎回来！');
                // 跳转至用户上次访问的页面
                $fallback = route('users.show', Auth::user());
                return redirect()->intended($fallback);
            } else {
                Auth::logout();
                session()->flash('warning', '你的账号未激活，请检查邮箱中的注册邮件进行激活。');
                return redirect('/');
            }
        } else {
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            return redirect()->back()->withInput();
        }
    }

    public function destroy()
    {
        Auth::logout();
        session()->flash('success', '您已成功退出');
        return redirect('login');
    }
}
