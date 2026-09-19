<?php
namespace Wainwright\CasinoDogOperatorApi\Models;
use \Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Wainwright\CasinoDogOperatorApi\Models\OperatorTransactions;

class PlayerBalances extends Eloquent  {
    protected $table = 'wainwright_player_balances';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'player_id',
        'player_name',
        'currency',
        'balance',
    ];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    public function __construct()
    {
        $this->transactions_controller = new OperatorTransactions;
    }


    public function select_player($player_id, $currency)
    {
        return self::firstOrCreate(
            [
                'player_id' => (string) $player_id,
                'currency' => (string) $currency,
            ],
            [
                'player_name' => (string) $player_id.'-name',
                'balance' => (int) config('casino-dog-operator-api.test_settings.start_balance', 0),
            ]
        );
    }


    public function create_player($player_id, $currency)
    {
        return $this->select_player($player_id, $currency);
    }

    public function select_player_balance($player_id, $currency)
    {
        $player = $this->select_player($player_id, $currency);
        return (int) $player->balance;
    }


    public function process_game($player_id, $bet, $win, $currency, $game, $callback_request)
    {
        $bet = (int) $bet;
        $win = (int) $win;

        if ($bet < 0 || $win < 0) {
            abort(422, 'Bet and win must be non-negative.');
        }

        return DB::transaction(function () use ($player_id, $bet, $win, $currency, $game, $callback_request) {
            $player = self::where('player_id', $player_id)
                ->where('currency', $currency)
                ->lockForUpdate()
                ->first();

            if (!$player) {
                $player = $this->select_player($player_id, $currency);
                $player = self::whereKey($player->getKey())->lockForUpdate()->first();
            }

            $balance = (int) $player->balance;

            if ($bet > $balance) {
                abort(400, 'Bet bigger than balance');
            }

            $newBalance = $balance - $bet + $win;

            if ($newBalance < 0) {
                abort(400, 'Balance cannot become negative.');
            }

            $player->balance = $newBalance;
            $player->save();

            return (int) $newBalance;
        }, 3);
    }


    public function transfer_funds($player_id, $currency, $amount, $type)
    {
        $amount = (int) $amount;

        if ($amount < 0) {
            abort(422, 'Amount must be non-negative.');
        }

        return DB::transaction(function () use ($player_id, $currency, $amount, $type) {
            $player = self::where('player_id', $player_id)
                ->where('currency', $currency)
                ->lockForUpdate()
                ->first();

            if (!$player) {
                $player = $this->select_player($player_id, $currency);
                $player = self::whereKey($player->getKey())->lockForUpdate()->first();
            }

            $balance = (int) $player->balance;

            if ($type === 'credit') {
                $newBalance = $balance + $amount;
            } elseif ($type === 'debit') {
                if ($amount > $balance) {
                    abort(400, 'Debit bigger than balance');
                }
                $newBalance = $balance - $amount;
            } else {
                abort(422, 'Unsupported transfer type.');
            }

            $player->balance = $newBalance;
            $player->save();

            return (int) $newBalance;
        }, 3);
    }

    

}