<?php
namespace Wainwright\CasinoDog\Controllers\Game\Netent;

use Wainwright\CasinoDog\Controllers\Game\SessionsHandler;
use Wainwright\CasinoDog\CasinoDog;
use Wainwright\CasinoDog\Controllers\Game\GameKernelTrait;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Wainwright\CasinoDog\Controllers\Game\GameKernel;
use Wainwright\CasinoDog\Models\Gameslist;

class NetentSessions extends NetentMain
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
            $parsed_game_id = $this->in_between("netent\\/", "\\/", $demo_link);

            if (!$parsed_game_id) {
                $parsed_game_id = $this->in_between('netent/', '/', $demo_link);
            }

            if (!$parsed_game_id) {
                $parsed_game_id = $game_id;
            }

            $build_url = rtrim(env('APP_URL'), '/')
                .'/prelauncher_netent?game='.urlencode($parsed_game_id)
                .'&token='.urlencode($token_internal);

            return [
                'link' => $build_url,
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

        $response = [
            'link' => $game['link'],
        ];
        return $response;
    }


}
