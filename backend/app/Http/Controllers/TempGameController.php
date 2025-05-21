<?php

namespace App\Http\Controllers;

use App\Models\TempGame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TempGameController extends Controller
{
    /**
     * Cria um novo jogo temporário.
     */
    public function store(Request $request)
    {
        \Log::info('Request recebida', $request->all());
        // Validação
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:16',
            'word'     => 'required|string|max:50',
            'password' => 'required|string|min:3|max:8',
            'chances'  => 'required|integer|min:1|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Criação do jogo com a senha hasheada
        $game = TempGame::create([
            'name'     => $request->input('name'),
            'word'     => $request->input('word'),
            'password' => Hash::make($request->input('password')),
            'chances'  => $request->input('chances'),
        ]);

        return response()->json([
            'success' => true,
            'game'    => $game,
        ], 201);
    }
}