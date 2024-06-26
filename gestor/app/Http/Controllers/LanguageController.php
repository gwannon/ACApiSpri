<?php

namespace App\Http\Controllers;

class LanguageController extends Controller

{
 /**
 * @param $lang
 *
 * @return \Illuminate\Http\RedirectResponse
 */
    public function swap($lang)
    {
        // Almacenar el lenguaje en la session
        session()->put('locale', $lang);
        return redirect()->back();
    }
}
