<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempGame extends Model
{
    protected $table = 'temp_games'; // Confirma o nome da tabela

    protected $fillable = [
        'name',
        'password',
        'word',
        'chances',
    ];

    // Se quiser timestamps automáticos
    public $timestamps = true;

    // Opcional: Regras de validação como função estática
    public static function rules()
    {
        return [
            'name'     => 'required|string|max:16',
            'password' => 'required|string|min:3|max:8',
            'word'     => 'required|string|max:50',
            'chances'  => 'required|integer|min:1|max:20',
        ];
    }
}