<?php
namespace Wainwright\CasinoDog\Controllers\Game\Mascot;
use Illuminate\Support\Facades\Http;
use Wainwright\CasinoDog\Controllers\Game\GameKernelTrait;
use Wainwright\CasinoDog\Models\Gameslist;

class MascotSessions extends MascotMain
{
    use GameKernelTrait;

    public function extra_game_metadata($gid)
    {
        return false;
    }

    public function fresh_game_session($game_id, $method, $token_internal = NULL)
    {
        if($method === 'redirect') {
            $game_identifier = explode('/', (string) $game_id);
            $product_id = end($game_identifier);

            if (!$product_id) {
                return false;
            }

            $url = 'https://demo.mascot.games/run/'.rawurlencode($product_id);
            $ch = curl_init($url);

            curl_setopt_array($ch, [
                CURLOPT_HEADER => false,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/140 Safari/537.36',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $html = curl_exec($ch);
            $curl_errno = curl_errno($ch);
            $redirectURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $http_code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (
                $curl_errno !== 0 ||
                $html === false ||
                !$redirectURL ||
                $http_code < 200 ||
                $http_code >= 400
            ) {
                return false;
            }

            $host = parse_url($redirectURL, PHP_URL_HOST);
            $session_id = $host ? explode('.', $host)[0] : null;

            if (!$session_id) {
                return false;
            }

            return [
                'html' => (string) $html,
                'link' => $redirectURL,
                'origin_session' => $session_id,
                'origin_game_id' => $game_identifier,
            ];
        }


        if($method === 'continued_session') {
            $game_identifier = explode('/', (string) $game_id);
            $select_session = $this->get_internal_session($token_internal)['data'];
            $link = 'https://'.$select_session['token_original'].'.mascot.games';
            $html = Http::connectTimeout(5)->timeout(15)->get($link);

            if(!$html->successful()) { //failed to continue old session, creating new
                $game = $this->fresh_game_session($game_id, 'redirect', $token_internal);
                $this->update_session($token_internal, 'token_original', $game['origin_session']); //update session table with the real game session
                return $game;
            }

            $data = [
                'html' => $html,
                'link' => $link,
                'origin_session' => $select_session['token_original'],
                'origin_game_id' => $game_identifier,
            ];
            return $data;
        }
        // Add in additional grey methods here, specify the method on the internal session creation when a session is requested, don't split this here
        return 'generateSessionToken() method not supported';
    }

    public function get_game_demolink($gid) {
        $select = Gameslist::where('gid', $gid)->first();
        return $select->demolink;
    }

    public function create_session(string $internal_token)
    {
        $select_session = $this->get_internal_session($internal_token);
        if($select_session['status'] !== 200) { //internal session not found
               return false;
        }

        $token_internal = $select_session['data']['token_internal'];
        $game_id = $select_session['data']['game_id_original'];

        if($select_session['data']['token_original'] === 0) {
            $game = $this->fresh_game_session($game_id, 'redirect', $token_internal);

            if ($game === false) {
                return false;
            }

            $this->update_session($internal_token, 'token_original', $game['origin_session']);
        } else {
            $game = $this->fresh_game_session($game_id, 'continued_session', $token_internal);

            if ($game === false) {
                return false;
            }
        }

        $html_content_modify = $this->modify_game($token_internal, $game['html']);


        $response = [
            'html' => $html_content_modify,
            'origin_session' => $game['origin_session'],
            'origin_game_id' => $game['origin_game_id'],
            'token' => $internal_token,
            'link' => $game['link'],
        ];
        return $response;
    }


}
