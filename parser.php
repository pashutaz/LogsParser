<?php
if ($filePath = $argv[1]??false){
    if (file_exists($filePath)) {
        $text = file_get_contents($filePath);
        $patterns = [
            'urls' => '/\"(POST|GET) \/.+ HTTP\/[\d\.]+"/',
            'codes' => '/\" (\d{3}) /',
            'traffic' => '/[^\s]\w+ (\d+) \"/',
            'crowlers' => '/(Google|Baidu|Bing|Yandex)/'
        ];

        $outputData = [
            'views' => 0,
            'urls' => 0,
            'traffic' => 0,
            'crawlers' => [
                'Google' => 0,
                'Bing' => 0,
                'Baidu' => 0,
                'Yandex' => 0,
            ],
            'statusCodes' => 0,
        ];

        preg_match_all($patterns['urls'], $text, $matches);
        $outputData['views'] = count($matches[0]);
        $outputData['urls'] = count(array_count_values($matches[0]));

        preg_match_all($patterns['traffic'], $text, $matches);
        $outputData['traffic'] = array_sum($matches[1]);

        preg_match_all($patterns['crowlers'], $text, $matches);
        $outputData['crawlers']['Google'] = array_count_values($matches[1])['Google']??0;
        $outputData['crawlers']['Bing'] = array_count_values($matches[1])['Bing']??0;
        $outputData['crawlers']['Baidu'] = array_count_values($matches[1])['Baidu']??0;
        $outputData['crawlers']['Yandex'] = array_count_values($matches[1])['Yandex']??0;

        preg_match_all($patterns['codes'], $text, $matches);
        $outputData['statusCodes'] = array_count_values($matches[1]);
        
        header('Content-type: application/json');
        echo $outputData = json_encode($outputData);
    }else
        echo "There is no such file\n";
}else
    echo "Please specify path to logs file\n";