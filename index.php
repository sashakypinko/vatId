<?php

function runScript() {
    $xml = makeApiRequest($_REQUEST);

    $parameters = parseXmlResponse($xml);

    storeXstVatIdCheck($parameters);
    storeXstVatIdCheckRequestLogs($parameters);
}

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

function initDBConnection(): PDO
{
    try {
        $dbConfig = [
            'host' => 'localhost',
            'port' => '3306',
            'name' => 'vat',
            'user' => 'root',
            'password' => '',
        ];

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

function storeXstVatIdCheck($data)
{
    try {
        $db = initDBConnection();

        $stm = $db->prepare(
            'INSERT INTO xst_vat_id_check 
                   (UstId_1, UstId_2, Firmenname, Ort, PLZ, Strasse)
                   VALUES (?, ?, ?, ?, ?, ?)'
        );

        $stm->execute([
            $data['UstId_1'],
            $data['UstId_2'],
            $data['Firmenname'],
            $data['Ort'],
            $data['PLZ'],
            $data['Strasse']
        ]);
    } catch (\PDOException $e) {
        throw new PDOException($e->getMessage());
    }
}

function storeXstVatIdCheckRequestLogs($data)
{
    try {
        $db = initDBConnection();

        $stm = $db->prepare(
            'INSERT INTO xst_vat_id_check_request_logs
                   (UstId_1, UstId_2, ErrorCode, Druck, Erg_PLZ, Ort, Datum, PLZ, Erg_Ort,
                   Uhrzeit, Erg_Name, Gueltig_ab, Gueltig_bis, Strasse, Firmenname, Erg_Str)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $stm->execute([
            $data['UstId_1'],
            $data['UstId_2'],
            $data['ErrorCode'],
            $data['Druck'],
            $data['Erg_PLZ'],
            $data['Ort'],
            $data['Datum'],
            $data['PLZ'],
            $data['Erg_Ort'],
            $data['Uhrzeit'],
            $data['Erg_Name'],
            $data['Gueltig_ab'],
            $data['Gueltig_bis'],
            $data['Strasse'],
            $data['Firmenname'],
            $data['Erg_Str']
        ]);
    } catch (\PDOException $e) {
        throw new PDOException($e->getMessage());
    }
}

runScript();