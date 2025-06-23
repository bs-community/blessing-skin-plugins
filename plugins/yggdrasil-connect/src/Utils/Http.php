<?php

namespace LittleSkin\YggdrasilConnect\Utils;

class Http
{
    public static function parseRequest()
    {
        /* PUT data comes in on the stdin stream */
        $putdata = fopen('php://input', 'r');

        $raw_data = '';

        /* Read the data 1 KB at a time and write to the file */
        while ($chunk = fread($putdata, 1024)) {
            $raw_data .= $chunk;
        }

        /* Close the streams */
        fclose($putdata);

        // Fetch content and determine boundary
        $boundary = substr($raw_data, 0, strpos($raw_data, "\r\n"));

        if (empty($boundary)) {
            parse_str($raw_data, $data);

            return $data;
        }

        // Fetch each part
        $parts = array_slice(explode($boundary, $raw_data), 1);
        $data = [];

        foreach ($parts as $part) {
            // If this is the last part, break
            if ($part == "--\r\n") {
                break;
            }

            // Separate content from headers
            $part = ltrim($part, "\r\n");
            [$raw_headers, $body] = explode("\r\n\r\n", $part, 2);

            // Parse the headers list
            $raw_headers = explode("\r\n", $raw_headers);
            $headers = [];
            foreach ($raw_headers as $header) {
                [$name, $value] = explode(':', $header);
                $headers[strtolower($name)] = ltrim($value, ' ');
            }

            // Parse the Content-Disposition to get the field name, etc.
            if (isset($headers['content-disposition'])) {
                $filename = null;
                $tmp_name = null;
                preg_match(
                    '/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
                    $headers['content-disposition'],
                    $matches
                );
                [, $type, $name] = $matches;

                // Parse File
                if (isset($matches[4])) {
                    // if labeled the same as previous, skip
                    if (isset($data[$matches[2]])) {
                        continue;
                    }

                    // get filename
                    $filename = $matches[4];

                    // get tmp name
                    $filename_parts = pathinfo($filename);
                    $tmp_name = tempnam(ini_get('upload_tmp_dir'), $filename_parts['filename']);

                    $data[$matches[2]] = [
                        'error' => 0,
                        'name' => $filename,
                        'tmp_name' => $tmp_name,
                        'size' => strlen($body),
                        'type' => $value,
                    ];

                    // place in temporary directory
                    file_put_contents($tmp_name, $body);
                } else {
                    // Parse Field
                    $data[$name] = substr($body, 0, strlen($body) - 2);
                }
            }
        }

        return $data;
    }
}
