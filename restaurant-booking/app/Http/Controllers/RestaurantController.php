<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function show($id)
    {
        $restaurant = Restaurant::findOrFail($id);

        return view('restaurant.show', compact('restaurant'));
    }
}
