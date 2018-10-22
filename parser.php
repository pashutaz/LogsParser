<?php

class Parser
{
    private $filePath;

    // Regex patterns shortcuts
    private $patterns = [
        'request' => '/\"(POST|GET|HEAD|DELETE|PATCH|PUT|OPTIONS) (\/[^\"]*) HTTP\/\d\.\d\"/',//http request
        'url' => '/\"(https?:\/\/[^\"#]+)(#[^\"]*)?\"/', //part of url without request
        'code' => '/\" (\d{3}) /',//status codes
        'traffic' => '/[^\s]\w+ (\d+) \"/',//bytes of traffic
        'crawler' => '/(Googlebot|Baiduspider|bingbot|YandexBot)/',
    ];

    // Result data array
    private $outputData = [
        'views' => 0,
        'urls' => [],
        'traffic' => 0,
        'crawlers' => [
            'Google' => 0,
            'Bing' => 0,
            'Baidu' => 0,
            'Yandex' => 0,
        ],
        'statusCodes' => []
    ];


    function __construct($filePath = null)
    {
        global $argv;
        $this->filePath = $filePath ?? ($argv[1] ?? false);

        if ($this->filePath ?? false){

            if (file_exists($this->filePath)) {

                $this->parse();

            }else exit ("There is no such file\n");

        }else exit ("Please specify path to logs file\n");
    }

    // main parser logic
    private function parse(){

        // opening filestream for reading
        if ($file = fopen($this->filePath, 'r')) {

            // line by line data checking
            while (!feof($file)) {
                $line = fgets($file);

                // counting all views
                $this->outputData['views']++;

                // unique urls handeling
                preg_match($this->patterns['request'], $line, $matches);
                $url = '';
                if ($matches){
                    $url = $matches[2];
                }
                preg_match($this->patterns['url'], $line, $matches);
                if ($matches){
                    $url = $matches[1] . $url;
                    in_array($url, $this->outputData['urls']) ? true : $this->outputData['urls'][] = $url;
                }

                // evaluating data trafic
                preg_match($this->patterns['traffic'], $line, $matches);
                if ($matches) {
                    $this->outputData['traffic'] += $matches[1];
                }

                // Searchers crawler bots handeling
                preg_match($this->patterns['crawler'], $line, $matches);
                if ($matches) {
                    switch ($matches[1]) {
                        case 'Googlebot':
                            $this->outputData['crawlers']['Google']++;
                            break;
                        case 'bingbot':
                            $this->outputData['crawlers']['Bing']++;
                            break;
                        case 'Baiduspider':
                            $this->outputData['crawlers']['Baidu']++;
                            break;
                        case 'YandexBot':
                            $this->outputData['crawlers']['Yandex']++;
                            break;
                        default:
                            break;
                    }
                }

                // counting status codes of request
                preg_match($this->patterns['code'], $line, $matches);
                if ($matches){
                    $this->outputData['statusCodes'][$matches[1]] ?? $this->outputData['statusCodes'][$matches[1]] = 0;
                    $this->outputData['statusCodes'][$matches[1]]++;
                }
            }
            fclose($file);

            // couting unique urls
            $this->outputData['urls'] = count($this->outputData['urls']);
        }
    }

    // Json data output
    public function getData(){
        header('Content-type: application/json');
        return $this->outputData = json_encode($this->outputData);
    }

}

$logs = new Parser();
echo $logs->getData();
