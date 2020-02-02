<?php

namespace App\Http\Controllers;

use App\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecipeController extends Controller
{
    public function create(Request $request)
    {
        $this->validate($request, ['title' => 'required', 'procedure' => 'required|min:8']);
        $user = Auth::user();
        $recipe = Recipe::create($request->only(['title', 'procedure']));
        $user->recipes()->save($recipe);
        return $recipe->toJson();
    }

    public function all()
    {
        return Auth::user()->recipes;
    }

    public function update(Request $request, Recipe $recipe)
    {
        if ($recipe->publisher_id != Auth::id()) {
            abort(404);
            return;
        }
        //Update and return
        $recipe->update($request->only('title', 'procedure'));
        return $recipe->toJson();
    }

    public function show(Recipe $recipe)
    {
        if ($recipe->publisher_id != Auth::id()) {
            abort(404);
            return;
        }
        return $recipe->toJson();
    }

    public function delete(Recipe $recipe)
    {
        if ($recipe->publisher_id != Auth::id()) {
            abort(404);
            return;
        }
        $recipe->delete();
    }
}
