<?php
set_time_limit(0);
require_once __DIR__ . '/vendor/autoload.php';

$resource = 'https://www.tsum.ru';
$category = $resource . '/catalog/zhenskie_sumki-2419';

$client = new \Guzzle\Http\Client(['timeout' => 5, 'connect_timeout' => 5]);

$begs = [];
for ($i = 1; $i<=55; $i++) {
    $request = $client->createRequest('GET', $category.'?'.http_build_query(['page' => $i]));
    try {
        $response = $request->send();
    } catch (Exception $e) {
        echo 'П ри загрузке '.$i.' проблема: '.$e->getMessage(). PHP_EOL;
        continue;
    }

    if ($response->isSuccessful()) {
        $items = [];

        preg_match_all(
            '#class="product__info".+?href="(.+?)">.+?</a>#s',
            $response->getBody(true),
            $items
        );

        foreach ($items[1] as $item) {
            $begs[] = $resource.$item;
        }
    } else {
        echo 'Не удалось получить данные со страницы '. $i. PHP_EOL;
    }
}

$begs = array_unique($begs);

file_put_contents(__DIR__.'/items.txt', implode(PHP_EOL, $begs));
