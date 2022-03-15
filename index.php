<?php

$xml = makeApiRequest($_REQUEST);
$parameters = parseXmlResponse($xml);

var_dump($parameters);

$db = initDBConnection([
    'host' => 'localhost',
    'port' => '3306',
    'name' => 'vat',
    'user' => 'root',
    'password' => '',
]);

/**
 * @param array $params
 * @return SimpleXMLElement
 * @throws Exception
 */
function makeApiRequest(array $params): SimpleXMLElement
{
    $cURLConnection = curl_init('https://evatr.bff-online.de/evatrRPC');
    curl_setopt($cURLConnection, CURLOPT_POSTFIELDS, $params);
    curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);

    $apiResponse = curl_exec($cURLConnection);
    curl_close($cURLConnection);

    return new SimpleXMLElement($apiResponse);
}

/**
 * @param stdClass $xml
 * @return array
 */
function parseXmlResponse(SimpleXMLElement $xml): array
{
    $parsedParams = [];

    foreach ($xml->param as $param) {
        $parsedParams[(string)$param->value->array->data->value[0]->string] = (string)$param->value->array->data->value[1]->string;
    }

    return $parsedParams;
}

function initDBConnection(array $dbConfig): PDO
{
    try {
        $instance = new PDO(
            "mysql:host=" . $dbConfig['host']
            . ';port=' . $dbConfig['port']
            . ';dbname=' . $dbConfig['name'],
            $dbConfig['user'],
            $dbConfig['password']
        );
        $instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $instance->query('SET NAMES utf8');
        $instance->query('SET CHARACTER SET utf8');

        return $instance;
    } catch (PDOException $error) {
        echo $error->getMessage();
        die;
    }
}