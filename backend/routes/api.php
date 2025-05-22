<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TempGameController;

Route::prefix('home')->group(function () {
    //Listar jogos
    Route::get('/games', [TempGameController::class, 'index']);
    //Buscar jogo
    Route::get('/search', [TempGameController::class, 'search']);
    //Criar jogo
    Route::post('/create-game', [TempGameController::class, 'store']);
    //Jogo
    Route::prefix('play')->group(function(){
        //Pegar jogo
        Route::post('{id}', [TempGameController::class, 'getGame']);
        //Jogar jogo
        Route::post('{id}/guess', [TempGameController::class, 'guess']);
    });
});