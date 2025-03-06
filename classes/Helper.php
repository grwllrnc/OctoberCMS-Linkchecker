<?php

namespace Bombozama\LinkCheck\Classes;

class Helper
{
    public function scanForUrls(string $string): array
    {
        $urls = array();
        $expression = '#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#';
        
        preg_match_all($expression, $string, $matches);
        
        foreach($matches[0] as $match) {
            try {
                $url = parse_url($match);
                
                // Use URL without fragment
                $parsed_url = $url['scheme'] . '://' . $url['host'];

                if (isset($url['path'])) {
                    $parsed_url .= $url['path'];
                }
                
                if (isset($url['query'])) {
                    $parsed_url .= '?' . $url['query'];
                }
                
                $urls[] = $parsed_url;

            } catch (\Exception $ex) {
                // If URL is invalid / malformed, log an error
                \Log::info('LinkCheck Error: Could not parse URL: ' . $match . ' due to the following error: ' . $ex);
            }
        }

        return $urls;
    }

    public static function getFullClassNameFromFile($pathToFile)
    {
        $fp = fopen($pathToFile, 'r');
        $class = $namespace = $buffer = '';
        $i = 0;
        while (!$class) {
            if (feof($fp)) {
                break;
            }

            $buffer.= fread($fp, 512);
            $tokens = token_get_all($buffer);

            if (strpos($buffer, '{') === false) {
                continue;
            }

            for (; $i < count($tokens); $i++) {
                if ($tokens[$i][0] === T_NAMESPACE) {
                    for ($j = $i+1; $j < count($tokens); $j++) {
                        if ($tokens[$j][0] === 265) { // PHP token id 265 == string
                            $namespace .= '\\' . $tokens[$j][1];
                        } elseif ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }
                if ($tokens[$i][0] === T_CLASS) {
                    for ($j = $i+1; $j < count($tokens); $j++) {
                        if ($tokens[$j] === '{') {
                            if (is_array($tokens[$i+2]) && $tokens[$i+2][0] !== 392) { // PHP token id 392 == whitespace 
                                $class = $tokens[$i+2][1];
                            }
                        }
                    }
                }
            }
        }
        return $namespace . '\\' . $class;
    }

    public static function getResponseCode(string $url, mixed $userAgent): int
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch , CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT_MS, 30000); # Setting timeout to 30 seconds
        if ($userAgent) {
            curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
        }
        $data = curl_exec($ch);
        $headers = curl_getinfo($ch);
        curl_close($ch);
        # In case of timeouts, let's throw out a 408 error "Request timeout"
        if ($headers['http_code'] == 0) {
            $headers['http_code'] = 408;
        }
        return $headers['http_code'];
    }

    /**
     * Flatten an arbitrarily nested array
     * 
     * see https://www.lambda-out-loud.com/posts/flatten-arrays-php/
     * 
     * @param array
     * @return array
     */
    public function flattenArray(array $array): array
    {
        $recursiveArrayIterator = new \RecursiveArrayIterator(
            $array,
            \RecursiveArrayIterator::CHILD_ARRAYS_ONLY
        );
        $iterator = new \RecursiveIteratorIterator($recursiveArrayIterator);
    
        return iterator_to_array($iterator, false);
    }

}