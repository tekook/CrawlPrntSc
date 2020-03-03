<?php

namespace Tekook\CrawlPrntSc;

use GuzzleHttp\Client;
use Illuminate\Support\Str;

/**
 * Class Crawler
 *
 * @package Tekook\CrawlPrntSc
 */
class Crawler
{
    /**
     * Base Url of Prnt.Sc
     */
    const BASE = 'https://prnt.sc';
    /**
     * Url's to ignore since they are 404
     */
    const IGNORES = [
        "//st.prntscr.com/2020/02/10/0334/img/0_173a7b_211be8ff.png",
    ];
    /**
     * RegEx to grab image url
     */
    const REG = /** @lang text */
        '/\<meta property=\"og\:image\" content=\"([^\"]+)\"\/\>/mi';
    /**
     *  UserAgent to present to WebPage.
     *
     * @var string
     */
    public string $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.122 Safari/537.36 Edg/80.0.361.62';
    /**
     * @var string Directory to output to
     */
    public string $output = "/app/output";

    /**
     * Execute the crawler.
     *
     * @param int $max Maximum images to crawl. (404s will not be counted!)
     */
    public function execute(int $max = 10): void
    {
        $client = new Client([
            'base_uri' => self::BASE,
            'headers'  => [
                'User-Agent' => $this->userAgent,
            ],
        ]);
        for ($i = 0; $i < $max; $i++) {
            $name = Str::lower(Str::random('6'));
            $response = $client->get($name);
            $body = (string)$response->getBody();
            if (preg_match(self::REG, $body)) {
                preg_match_all(self::REG, $body, $matches, PREG_SET_ORDER, 0);
                $url = $matches[0][1];
                if (in_array($url, self::IGNORES)) {
                    $i--;
                    continue;
                }
                $response = $client->get($url);
                file_put_contents($this->getOutputPath($name), $response->getBody());
                $this->log('downloaded ' . $name);
                usleep(500);
            } else {
                $i--;
            }
        }
    }

    /**
     * Get output path for an image name.
     *
     * @param $name
     *
     * @return string
     */
    protected function getOutputPath($name)
    {
        return $this->output . DIRECTORY_SEPARATOR . time() . '_' . $name . '.png';
    }

    /**
     * log to STDOUT
     *
     * @param $text
     */
    protected function log($text)
    {
        echo $text . '\r\n';
    }
}
