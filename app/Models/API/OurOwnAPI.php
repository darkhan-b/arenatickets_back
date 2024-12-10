<?php

namespace App\Models\API;

use App\Models\Specific\APIPartner;
use App\Models\Specific\Order;
use Illuminate\Support\Facades\Http;
use Spatie\Crypto\Rsa\PublicKey;

class OurOwnAPI {

    private $url = 'https://api.arenatickets.kz';

    public function getShows() {
        return $this->request('/partner/shows');
    }

    public function request($path, $method = 'get', $params = []) {
        [$timestamp, $signature] = $this->generateSignature($path);
        $res = Http::withHeaders([
            'X-PARTNER-TOKEN' => '826f918e-9f4e-4328-9378-4a396451e511',
            'TIMESTAMP' => $timestamp,
            'SIGNATURE' => base64_encode($signature)
        ])->{$method}($this->url.$path, $params);
        return $res->json();
    }

    public function generateSignature($path) {
        $timestamp = time();
//        $timestamp = 1661958467;
//        dd($timestamp);
        $user = APIPartner::first();
        $publicKey = '-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEAwj8AVFkUVEp04pla433t
lnDlCDkFA0cbrLioE4uxTgaekgfvGnbxQl5f939gVk9UThsR5yqMsQNBYnexObQ/
wWcoe/y6S8V375+ZPpbYzqiDzYVHCtwQFPx1ClbzBjTP0ageOUgXqExs8v43zz+w
8SlgDFLBhUG7ozv0dJ9FQop8rdLyeZ6r5fBCjV/n6TQwz0X9a1HmtcE9R6TKhNkn
pN5abjCgRF5vP16wVsv50sueKTZuTbCvXqwptJOrhlAn5MNm6/zIFGMZf/bUIsyB
AxDhuLVC97LPWXqhXuUpbakwl3NHkMPqw3PEzebrz1wfA0YR065N66x1qLIKuGiH
EQ8XKOJgZDRfyAEdWLRiRTk1/WLUBwKEDXbjhrgezYkgS3dUGpd1HmIqAV1V78II
uYv6exyOLQ6mro2al3SfNV89sqqwwZ6t4BmqjbkpNQYQFiNJc5MA1aZNykdsPPGh
E4/X3TDdTDKCBH52b6D7wvfnCL5KqWRsMfrgU654kyvRgDJ9ioosvdHRUNHhhM7R
25gUyRfc0OdmcekeVf0Jyh0Q52P6dZxoex1fAzy4LtaIvW2yaDq2jQCOlDJ5P2Uo
zZaCpUIRRMqca5DV+LkR1N/C4C8PS3l/kSsZTUwyINo8YtXBtzvSppVF3xYljtZq
RhQyhExWfLRUtGejbjQJOv0CAwEAAQ==
-----END PUBLIC KEY-----';
//        dd($publicKey);
//        dd($user->public_key);
        $publicKey = PublicKey::fromString($publicKey);
//        $publicKey = PublicKey::fromString($user->public_key);
        $signature = $publicKey->encrypt($path.$timestamp); // encrypted data contains something unreadable
        return [$timestamp, $signature];
    }

}
