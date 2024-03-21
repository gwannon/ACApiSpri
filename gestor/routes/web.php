<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\LanguageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('lang/{lang}', [LanguageController::class, 'swap'])->name('lang.swap');
Route::group(['middleware' => 'auth'], function() {
    Route::get('/home', function () {
        return view('home');
    })->name('home');

    //Paneles de información
    Route::get('/info', function () {
        return view('info-campanas');
    })->name('info.campanas');

    //Boletines --------------------------
    Route::get('/boletines/been-basquetrade', function () {
        return view('boletin-basquetrade');
    })->name('boletines.been-basquetrade');

    Route::get('/boletines/bdih-activos', function () {
        return view('boletin-bdih-activos');
    })->name('boletines.bdih-activos');

    //Administración ---------------------
    Route::get('/admin/usuarios/borrar/{id}', function ($id) {
        //if(Auth::user()->superadmin != 1) return redirect('/home');
        if($res=User::where('id',$id)->delete()) return redirect(route('admin.usuarios', ['delete' => 'ok']).'#users');
        else return redirect(route('admin.usuarios', ['delete' => 'error']).'#users');
    })->name('admin.usuarios.borrar');

    Route::post('/admin/usuarios/editar/{id}', function ($id, Request $request) {
        //if(Auth::user()->superadmin != 1) return redirect('/');
        User::where('id', $id)->update([ 
            'name' => $request->input('username'),
            'email' => $request->input('useremail'),
            'perms' => $request->input('userperms'),
        ]);
        if($request->input('userpassword') != '' && strlen($request->input('userpassword')) >= 8 ) {
            User::where('id', $id)->update([
                'password' => Hash::make($request->input('userpassword'))
            ]);
        }
        return redirect(route('admin.usuarios.editar',  ["id" => $id, 'edit' => 'ok']));
    })->name('admin.usuarios.editar');

    Route::get('/admin/usuarios/editar/{id}', function ($id) {
        //if(Auth::user()->superadmin != 1) return redirect('/');
        $user = User::where('id', $id)->first();
        return view('admin_user_edit', [
            'user' => $user
        ]);
    })->name('admin.usuarios.editar');

    Route::post('/admin/usuarios', function (Request $request) {
        //if(Auth::user()->superadmin != 1) return redirect('/home');
        $user_created = false;
        $users = User::where('email', $request->input('useremail'))->get();
        if(sizeof($users) == 0){
            $dataUser = new User();
            $dataUser->name = $request->input('username');
            $dataUser->password = Hash::make($request->input('userpassword'));
            $dataUser->email = $request->input('useremail');
            $dataUser->perms = $request->input('userperms');
            $dataUser->save();
            $user_created = true;
        }
        $users = User::orderBy('name', 'asc')->get();
        return view('admin_users', [
            'user_created' => $user_created,
            'users' => $users
        ]);
    })->name('admin.usuarios.crear');

    Route::get('/admin/usuarios', function (Request $request) {
        //if(Auth::user()->superadmin != 1) return redirect('/home');
        $users = User::orderBy('name', 'asc')->get();
        return view('admin_users', [
            'users' => $users
        ]);
    })->name('admin.usuarios');

    Route::get('/change-password', [App\Http\Controllers\HomeController::class, 'changePassword'])->name('change-password');
    Route::post('/change-password', [App\Http\Controllers\HomeController::class, 'updatePassword'])->name('update-password');
});

Route::get('/', function (Request $request) {
    return redirect('/login');
})->name('user.home');

Auth::routes(['register' => false]);
