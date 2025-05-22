<?php

namespace App\Http\Controllers;

use App\Models\TempGame;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TempGameController extends Controller
{
    //Listar jogos ativos
    public function index()
    {
        $games = TempGame::all();
        return response()->json($games);
    }

    //Buscar jogo especifico
    public function search(Request $request)
    {
        $name = $request->query('name');

        if (empty($name)) {
            // Mostra todos os jogos se a busca estiver vazia
            $games = TempGame::all();
        } else {
            // Busca apenas jogos cujo nome COMEÇA com o termo digitado (prefixo)
            $games = TempGame::where('name', 'LIKE', $name . '%')->get();
        }

        return response()->json($games);
    }

    //Criar um novo jogo
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

    // Pegar dados do jogo
    public function getGame(Request $request, $id)
    {
        $game = \App\Models\TempGame::find($id);

        if (!$game) {
            return response()->json(['error' => 'Jogo não encontrado.'], 404);
        }

        // Recebe a senha enviada no JSON
        $password = $request->input('password');

        // Verifica a senha
        if (empty($password) || !Hash::check($password, $game->password)) {
            return response()->json(['error' => 'Senha incorreta.'], 401);
        }

        // Gera token temporário
        $sessionToken = Str::uuid()->toString();

        // Salva em cache por 1h
        cache()->put('game_session_' . $sessionToken, $game->id, now()->addHour());

        // Cria o array e remove campos sensíveis
        $gameArray = $game->toArray();
        unset($gameArray['word'], $gameArray['password']);

        // Retorna dados do jogo e token
        return response()->json([
            'game' => $gameArray,
            'session_token' => $sessionToken
        ]);
    }

    //Jogar o jogo
    public function guess(Request $request, $id)
    {
        //Checa se o token está correto antes de mais nada
        $sessionToken = $request->input('session_token');
        $cachedId = cache()->get('game_session_' . $sessionToken);
        if (!$cachedId || $cachedId != $id) {
            return response()->json(['error' => 'Você precisa da senha para jogar!'], 401);
        }

        $input = $request->input('guess');

        $game = TempGame::find($id);

        if (!$game) {
            return response()->json(['error' => 'Jogo não encontrado.'], 404);
        }

        $target = $game->word;

        if (empty($input)) {
            return response()->json(['error' => 'Palpite vazio.'], 400);
        }

        if (mb_strlen($input) !== mb_strlen($target)) {
            return response()->json([
                'error' => 'Tamanho da palavra incorreto',
                'expected_length' => mb_strlen($target),
                'chances_left' => $game->chances
            ], 400);
        }

        // Decrementa chances
        if ($game->chances > 0) {
            $game->chances -= 1;
            $game->save();
        }

        // Checagem das letras
        $result = [];
        $usedIndexes = [];
        for ($i = 0; $i < mb_strlen($input); $i++) {
            if (mb_substr($input, $i, 1) === mb_substr($target, $i, 1)) {
                $result[$i] = 'correct';
                $usedIndexes[$i] = true;
            }
        }
        for ($i = 0; $i < mb_strlen($input); $i++) {
            if (isset($result[$i])) {
                continue;
            }
            $found = false;
            for ($j = 0; $j < mb_strlen($target); $j++) {
                if (!isset($usedIndexes[$j]) &&
                    mb_substr($input, $i, 1) === mb_substr($target, $j, 1)
                ) {
                    $found = true;
                    $usedIndexes[$j] = true;
                    break;
                }
            }
            $result[$i] = $found ? 'present' : 'absent';
        }

        // Checagem de vitórias
        $isWin = ($input === $target);
        $isGameOver = ($game->chances <= 0);

        return response()->json([
            'result' => $result,
            'chances_left' => $game->chances,
            'win' => $isWin,
            'game_over' => $game->chances <= 0
        ]);
    }

}