<?php

namespace Tekook\CrawlPrntSc;

use GuzzleHttp\Client;
use Illuminate\Support\Str;

class Crawler
{
    const BASE    = 'https://prnt.sc';
    const IGNORES = [
        "//st.prntscr.com/2020/02/10/0334/img/0_173a7b_211be8ff.png",
    ];

    public function execute(int $max = 10) : void
    {
        $client = new Client([
            'base_uri' => self::BASE,
            'headers'  => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36 Edg/80.0.361.62',
            ],
        ]);
        $reg = '/\<meta property=\"og\:image\" content="([^"]+)\"\/\>/mi';
        for ($i = 0; $i < $max; $i++) {
            $rnd = Str::lower(Str::random('6'));
            //$rnd = "ab5540";
            $response = $client->get($rnd);
            $body = (string)$response->getBody();
            if (preg_match($reg, $body)) {
                preg_match_all($reg, $body, $matches, PREG_SET_ORDER, 0);
                $url = $matches[0][1];
                if (in_array($url, self::IGNORES)) {
                    $i--;
                    continue;
                }
                $path = '/app/output/' . time() . '_' . $rnd . '.png';
                $r = $client->get($url);
                file_put_contents($path, $r->getBody());
                echo('downloaded ' . $rnd . '\r\n');
                usleep(500);
            }
        }
    }
}
