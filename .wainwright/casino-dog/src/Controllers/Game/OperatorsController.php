<?php
namespace Wainwright\CasinoDog\Controllers\Game;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Wainwright\CasinoDog\Models\OperatorAccess;
use Illuminate\Support\Str;
use Wainwright\CasinoDog\Controllers\Game\SessionsHandler;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
class OperatorsController
{
	public static function createOperatorKey($data) {
		$operator = new OperatorAccess();
		$operator->operator_key = Str::orderedUuid();
		$operator->operator_secret = Str::random(12);
		$operator->operator_access = $data['operator_access'];
		$operator->callback_url = $data['callback_url'];
		$operator->ownedBy = $data['ownedBy'];
		$operator->active = 1;
        $operator->last_used_at = now();
		$operator->timestamps = true;
		$operator->save();
        return $operator;
	}

	public static function operatorByKey($key)
	{
		$operator_query = Cache::get('operatorByKey:'.$key);
        if (!$operator_query) {
			$operator_query = OperatorAccess::where('operator_key', $key)->first();
			if(!$operator_query) {
				return false;
			} else {
				Cache::put($cacheKey, $operator_query, now()->addMinutes(15));
			}
		}
		$response = array('status' => 'success', 'data' => $operator_query);
		return $response;
	}

	public static function verifyKey($key, $ip) {
		$find = OperatorAccess::where('operator_key', $key)
			->where('active', 1)
			->first();

		if(!$find) {
			return false;
		}

		if($find->operator_access === 'internal') {
			return array('status' => 'success', 'data' => $find);
		}

		if(!filter_var($find->operator_access, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			return false;
		}

		if((string) $find->operator_access !== (string) $ip) {
			return false;
		}

		return array('status' => 'success', 'data' => $find);
	}

	public static function operatorPing($key, $ip) {
		$debug = app()->hasDebugModeEnabled();
		$find = OperatorAccess::where('operator_key', $key)->first();
		if(!$find) {
			return false;
		}
		try {
		if($find->operator_access !== 'internal') {
			$salt_sign = Str::random(12);
			$query = [
				'action' => 'ping',
				'salt_sign' => $salt_sign,
			];
			$http = Http::connectTimeout(3)->timeout(8)->retry(1, 150)->get($find->callback_url, $query);
			$pong_hash = hash_hmac('md5', $find->operator_secret, $salt_sign);
            if (!$http->successful()) {
                return false;
            }

            $pong_hash_return = data_get($http->json(), 'data.pong');
			if(!is_string($pong_hash_return) || !hash_equals($pong_hash, $pong_hash_return)) {
				save_log('OperatorsController()', 'Error ping, secret hash has does not allign', json_encode(array('Ping' => $pong_hash, 'Pong return' => $pong_hash_return)));
				return false;
			}
		}
		} catch(\Exception $e) {
			save_log('OperatorsController()', 'Error ping: '.$e->getMessage());
			return false;
		}
		$response = array('status' => 'success', 'data' => $find);
		return $response;
	}

	public static function operatorCallbacks($session_key, $action, $game_data = NULL)
	{
		$debug = app()->hasDebugModeEnabled();
		if($debug) {
			$casino_dog = new \Wainwright\CasinoDog\CasinoDog();
		}

		$session = SessionsHandler::sessionData($session_key);
		if($session === false) {
			save_log('OperatorsController()', 'Session not found while being asked to perform operator callback: '.json_encode($game_data));
			return false;
		}
		$operator_details = self::operatorByKey($session['data']['operator_id']);
		if($operator_details === false) {
			save_log('OperatorsController()', 'Operator not found while gameplay is active.', $session['data']);
			return false;
		}
		$callback = $operator_details['data']['callback_url'];
		if($action === 'balance') {
			$salt_sign = Str::random(12);
			$query = [
				'player_operator_id' => $session['data']['player_operator_id'],
				'currency' => $session['data']['currency'],
				'action' => 'balance',
				'sign' => hash_hmac('md5', $operator_details['data']['operator_secret'], $salt_sign),
				'salt_sign' => $salt_sign,
			];
			$http = Http::connectTimeout(3)
                ->timeout(8)
                ->retry(1, 150)
                ->get($callback, $query);

			if(!$http->successful()) {
				Log::warning('Operator balance callback failed.', [
                    'status' => $http->status(),
                    'operator_id' => $session['data']['operator_id'],
                ]);
				return false;
			}

            $balance = data_get($http->json(), 'data.balance');

            if (!is_numeric($balance)) {
                Log::warning('Operator balance callback returned invalid schema.', [
                    'operator_id' => $session['data']['operator_id'],
                ]);
                return false;
            }

			return (int) $balance;
		} elseif($action === 'game') {
			$salt_sign = Str::random(12);
			$query = [
				'player_operator_id' => $session['data']['player_operator_id'],
				'currency' => $session['data']['currency'],
				'action' => 'game',
				'sign' => hash_hmac('md5', $operator_details['data']['operator_secret'], $salt_sign),
				'salt_sign' => $salt_sign,
				'game' => $session['data']['game_id'],
				'bet' => $game_data['bet'],
				'win' => $game_data['win'],
				'currency' => $session['data']['currency'],
			];
			$http = Http::connectTimeout(3)
                ->timeout(8)
                ->retry(1, 150)
                ->get($callback, $query);

			if(!$http->successful()) {
				Log::warning('Operator game callback failed.', [
                    'status' => $http->status(),
                    'operator_id' => $session['data']['operator_id'],
                ]);
				return false;
			}

            $balance = data_get($http->json(), 'data.balance');

            if (!is_numeric($balance)) {
                Log::warning('Operator game callback returned invalid schema.', [
                    'operator_id' => $session['data']['operator_id'],
                ]);
                return false;
            }

			return (int) $balance;
		}
		return $callback;
	}


}
