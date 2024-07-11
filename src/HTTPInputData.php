<?php
namespace RestRouter;

class HTTPInputData
{
	const BLOCKED_VARIABLES = [ '_id' ];
    public static function process()
    {
        $content_parts = explode(';', $_SERVER['CONTENT_TYPE'] ?? 'application/x-www-form-urlencoded');

        $boundary = '';
        $encoding = '';

        $content_type = array_shift($content_parts);

        foreach ($content_parts as $part) {
            if (strpos($part, 'boundary') !== false) {
                $part = explode('=', $part, 2);
                if (!empty($part[1])) {
                    $boundary = '--' . $part[1];
                }
            } elseif (strpos($part, 'charset') !== false) {
                $part = explode('=', $part, 2);
                if (!empty($part[1])) {
                    $encoding = $part[1];
                }
            }
            if ($boundary !== '' && $encoding !== '') {
                break;
            }
        }

        if ($content_type == 'multipart/form-data') {
            return self::fetchFromMultipart($boundary);
        }

        // can be handled by built in PHP functionality
        $content = file_get_contents('php://input');
        $variables = json_decode($content,true);
        if (empty($variables)) {
            parse_str($content, $variables);
        }

				$variables = self::cleanBlockedVariables($variables);

        return ['variables' => $variables, 'files' => []];
    }

		private static function cleanBlockedVariables($variables){
			foreach(HTTPInputData::BLOCKED_VARIABLES as $key){
				if(isset($variables[ $key ])){
					die(new Exceptions\InvalidRequestException('Cannot update key: '.$key));
				}
			}
			return $variables;
		}

    private static function fetchFromMultipart(string $boundary)
    {
        $result = ['variables' => [], 'files' => []];

        $stream = fopen('php://input', 'rb');

        $sanity = fgets($stream, strlen($boundary) + 5);

        // malformed file, boundary should be first item
        if (rtrim($sanity) !== $boundary) {
            return $result;
        }

        $raw_headers = '';

        while (($chunk = fgets($stream)) !== false) {
            if ($chunk === $boundary) {
                continue;
            }

            if (!empty(trim($chunk))) {
                $raw_headers .= $chunk;
                continue;
            }

            $result = self::parseRawHeader($stream, $raw_headers, $boundary, $result);
            $raw_headers = '';
        }

        fclose($stream);

        return $result;
    }

    private static function parseRawHeader($stream, string $raw_headers, string $boundary, array $result)
    {
        $variables = $result['variables'];
        $files     = $result['files'];

        $headers = [];


        foreach (explode("\r\n", $raw_headers) as $header) {
            if (strpos($header, ':') === false) {
                continue;
            }
            list($name, $value) = explode(':', $header, 2);
            $headers[strtolower($name)] = ltrim($value, ' ');
        }

        if (!isset($headers['content-disposition'])) {
            return ['variables' => $variables, 'files' => $files];
        }

        if (!preg_match('/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/', $headers['content-disposition'], $matches)) {
            return ['variables' => $variables, 'files' => $files];
        }

        $name     = $matches[2];
        $filename = $matches[4] ?? '';

        if (!empty($filename)) {
            $files[$name] = self::fetchFileData($stream, $boundary, $headers, $filename);
            return ['variables' => $variables, 'files' => $files];
        } else {
            $variables = self::fetchVariables($stream, $boundary, $name, $variables);
        }

        return ['variables' => $variables, 'files' => $files];
    }

    private static function fetchFileData($stream, string $boundary, array $headers, string $filename)
    {
        $error = UPLOAD_ERR_OK;

        if (isset($headers['content-type'])) {
            $tmp = explode(';', $headers['content-type']);
            $contentType = $tmp[0];
        } else {
            $contentType = 'unknown';
        }

        $tmpnam     = tempnam(ini_get('upload_tmp_dir'), 'php');
        $fileHandle = fopen($tmpnam, 'wb');

        if ($fileHandle === false) {
            $error = UPLOAD_ERR_CANT_WRITE;
        } else {
            $lastLine = NULL;
            while (($chunk = fgets($stream, 8096)) !== false && strpos($chunk, $boundary) !== 0) {
                if ($lastLine !== NULL) {
                    if (fwrite($fileHandle, $lastLine) === false) {
                        $error = UPLOAD_ERR_CANT_WRITE;
                        break;
                    }
                }
                $lastLine = $chunk;
            }

            if ($lastLine !== NULL && $error !== UPLOAD_ERR_CANT_WRITE) {
                if (fwrite($fileHandle, rtrim($lastLine, "\r\n")) === false) {
                    $error = UPLOAD_ERR_CANT_WRITE;
                }
            }
        }

        return [
            'name'     => $filename,
            'type'     => $contentType,
            'tmp_name' => $tmpnam,
            'error'    => $error,
            'size'     => filesize($tmpnam)
        ];
    }

    private static function fetchVariables($stream, string $boundary, string $name, array $variables)
    {
        $fullValue = '';
        $lastLine = NULL;

        while (($chunk = fgets($stream)) !== false && strpos($chunk, $boundary) !== 0) {
            if ($lastLine !== NULL) {
                $fullValue .= $lastLine;
            }

            $lastLine = $chunk;
        }

        if ($lastLine !== NULL) {
            $fullValue .= rtrim($lastLine, "\r\n");
        }

        if (isset($headers['content-type'])) {
            $encoding = '';

            foreach (explode(';', $headers['content-type']) as $part) {
                if (strpos($part, 'charset') !== false) {
                    $part = explode($part, '=', 2);
                    if (isset($part[1])) {
                        $encoding = $part[1];
                    }
                    break;
                }
            }

            if ($encoding !== '' && strtoupper($encoding) !== 'UTF-8' && strtoupper($encoding) !== 'UTF8') {
                    $tmp = mb_convert_encoding($fullValue, 'UTF-8', $encoding);
                    if ($tmp !== false) {
                        $fullValue = $tmp;
                    }
            }
        }

        $fullValue = $name . '=' . $fullValue;
        $tmp = [];
        parse_str($fullValue, $tmp);

        return self::expandVariables(explode('[', $name), $variables, $tmp);
    }

    private static function expandVariables(array $names, $variables, array $values)
    {
        if (!is_array($variables)) {
            return $values;
        }

        $name = rtrim(array_shift($names), ']');
        if ($name !== '') {
            $name = $name . '=p';
            $tmp = [];
            parse_str($name, $tmp);

            $tmp  = array_keys($tmp);
            $name = reset($tmp);
        }

        if ($name === '') {
            $variables[] = reset($values);
        } elseif (isset($variables[$name]) && isset($values[$name])) {
            $variables[$name] = self::expandVariables($names, $variables[$name], $values[$name]);
        } elseif (isset($values[$name])) {
            $variables[$name] = $values[$name];
        }

				$variables = self::cleanBlockedVariables($variables);
        return $variables;
    }
}
