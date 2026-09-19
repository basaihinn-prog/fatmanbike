<?php
namespace Wainwright\CasinoDog\Controllers\Game\Hacksaw;

use Wainwright\CasinoDog\Controllers\Game\SessionsHandler;
use Wainwright\CasinoDog\CasinoDog;
use Wainwright\CasinoDog\Controllers\Game\GameKernelTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Wainwright\CasinoDog\Controllers\Game\GameKernel;
use Wainwright\CasinoDog\Models\Gameslist;

class HacksawSessions extends HacksawMain
{
    use GameKernelTrait;

    public function extra_game_metadata($gid)
    {
        return false;
    }

    public function fresh_game_session($game_id, $method, $token_internal = NULL)
    {
        if($method === 'demo_method') {
            $demo_link = (string) $this->get_game_demolink($game_id);
            $query = $this->parse_query($demo_link);
            $origin_game_id = $query['gameid'] ?? null;

            if (!$demo_link || !$origin_game_id) {
                return false;
            }

            $base_url = rtrim(config('casino-dog.domain', env('APP_URL')), '/');
            $internal_api = $base_url
                .'/api/games/hacksaw/'
                .rawurlencode($token_internal)
                .'/'
                .rawurlencode($origin_game_id)
                .'/play';

            $url = str_replace(
                'https://rgs-demo.hacksawgaming.com/api',
                $internal_api,
                $demo_link
            );
            $url = str_replace('token=demo', 'token='.rawurlencode($token_internal), $url);
            $url = str_replace('mode=2', 'mode=0', $url);

            if ($url === $demo_link) {
                return false;
            }

            return [
                'link' => $url,
            ];
        }

        // Add in additional grey methods here, specify the method on the internal session creation when a session is requested, don't split this here
        return 'generateSessionToken() method not supported';
    }

    public function get_game_demolink($gid) {
        $select = Gameslist::where('gid', $gid)->first();
        return $select->demolink;
    }


    public function get_game_identifier($gid) {
        $select = Gameslist::where('gid', $gid)->first();
        return $select->gid_extra;
    }

    public function create_session(string $internal_token)
    {
        $select_session = $this->get_internal_session($internal_token);
        if($select_session['status'] !== 200) { //internal session not found
               return false;
        }

        $token_internal = $select_session['data']['token_internal'];
        $game_id = $select_session['data']['game_id_original'];

        $game = $this->fresh_game_session($game_id, 'demo_method', $token_internal);

        if ($game === false) {
            return false;
        }

        $response = [
            'link' => $game['link'],
        ];
        return $response;
    }


}
