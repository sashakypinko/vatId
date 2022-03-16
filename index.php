<?php

try {
    if (php_sapi_name() === 'cli') {
        runCliScript();
    } else {
        runHttpScript();
    }
} catch (Exception $e) {
    echo $e->getMessage();
}

/**
 * @return void
 * @throws Exception
 */
function runHttpScript()
{
    if (validateRequest($_REQUEST)) {
        $existingValidationJsonResult = getExistingValidationJsonResultByField('UstId_2', $_REQUEST['UstId_2']);

        if ($existingValidationJsonResult) {
            jsonResponse([
                'validationJsonResult' => json_decode($existingValidationJsonResult['validationJsonResult'], true),
                'valid' => (bool)$existingValidationJsonResult['validVatId'],
                'responseCode' => $existingValidationJsonResult['ErrorCode']
            ]);
        } else {
            $userID = storeXstVatIdCheck($_REQUEST);
            $response = requestVatIdCheck($_REQUEST);
            $isValid = storeXstVatIdCheckRequestLogs($response, $userID);

            jsonResponse([
                'validationJsonResult' => $response,
                'valid' => $isValid,
                'responseCode' => $response['ErrorCode']
            ]);
        }
    } else {
        jsonResponse([
            'error' => 'Request parameters are invalid!'
        ]);
    }
}

function runCliScript()
{
    $vatIds = getXstVatIdForReCheck();

    foreach ($vatIds as $vatId) {

        $existingValidationJsonResult = getExistingValidationJsonResultByField('userID', $vatId['id']);

        if($existingValidationJsonResult && $existingValidationJsonResult['validVatId']) {
            continue;
        }

        $response = requestVatIdCheck([
            'UstId_1' => $vatId['UstId_1'],
            'UstId_2' => $vatId['UstId_2'],
            'Firmenname' => $vatId['Firmenname'],
            'Ort' => $vatId['Ort'],
            'PLZ' => $vatId['PLZ'],
            'Strasse' => $vatId['Strasse']
        ]);

        storeXstVatIdCheckRequestLogs($response, $vatId['id']);
    }
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

function requestVatIdCheck($requestParams) {
    try {
        $xml = makeApiRequest($requestParams);

        return parseXmlResponse($xml);
    } catch (\Exception $e) {
        echo $e->getMessage();
    }
}

/**
 * @param array $data
 * @return void
 */
function jsonResponse(array $data)
{
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode($data);
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

/**
 * @param array $request
 * @return bool
 */
function validateRequest(array $request): bool
{

    return (
        validateUstId($request['UstId_1'])
        && validateUstId($request['UstId_2'])
        && validateSpecialChars($request['Firmenname'])
        && validateSpecialChars($request['Ort'])
        && validateSpecialChars($request['PLZ'])
        && validateSpecialChars($request['Strasse'])
        && validateEmptyField($request['Firmenname'])
        && validateEmptyField($request['Ort'])
    );
}

/**
 * @param $field
 * @return bool
 */
function validateEmptyField($field): bool
{
    return (bool)strlen($field);
}

/**
 * @param $field
 * @return bool
 */
function validateSpecialChars($field): bool
{
    preg_match("/[!$&\(\)?}{~]/", $field, $matches);

    return empty($matches);
}

/**
 * @param $UstId
 * @return bool
 */
function validateUstId($UstId): bool
{
    preg_match("/^[A-Za-z]{2}[\d]{9}$/", $UstId, $matches);

    return !empty($matches);
}

/**
 * @param $data
 * @return bool
 */
function validateResponse(array $data): bool
{
    return (
        $data['Erg_Name'] === 'A'
        && $data['Erg_Ort'] === 'A'
        && $data['Erg_PLZ'] === 'A'
        && $data['Erg_Str'] === 'A'
    );
}

/**
 * @return PDO
 */
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

/**
 * @param $UstId_2
 * @return mixed
 */
function getExistingValidationJsonResultByField($field, $value)
{
    try {
        $db = initDBConnection();

        $dateTime = (new DateTime())->modify("-1 day")->format('Y-m-d H:i:s');

        $stm = $db->prepare("SELECT * FROM xst_vat_id_check_request_logs WHERE $field = ? AND lastChange > ? ORDER BY id DESC;");

        $stm->execute([$value, $dateTime]);

        $result = $stm->fetch(PDO::FETCH_ASSOC);

        return $result;
    } catch (\PDOException $e) {
        throw new PDOException($e->getMessage());
    }
}

function getXstVatIdForReCheck()
{
    try {
        $db = initDBConnection();

        $stm = $db->prepare('SELECT * FROM xst_vat_id_check WHERE forceReCheck = 1;');

        $stm->execute();

        $result = $stm->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    } catch (\PDOException $e) {
        throw new PDOException($e->getMessage());
    }
}

/**
 * @param $data
 * @return false|string
 */
function storeXstVatIdCheck(array $data)
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

        return $db->lastInsertId();
    } catch (\PDOException $e) {
        throw new PDOException($e->getMessage());
    }
}

/**
 * @param array $data
 * @param int $userID
 * @return bool
 */
function storeXstVatIdCheckRequestLogs(array $data, int $userID): bool
{
    try {
        $db = initDBConnection();

        $stm = $db->prepare(
            'INSERT INTO xst_vat_id_check_request_logs
                   (UstId_1, UstId_2, ErrorCode, Druck, Erg_PLZ, Ort, Datum, PLZ, Erg_Ort, Uhrzeit, Erg_Name,
                    Gueltig_ab, Gueltig_bis, Strasse, Firmenname, Erg_Str, userID, validationJsonResult, validVatId)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        $isValid = validateResponse($data);

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
            $data['Erg_Str'],
            $userID,
            json_encode($data),
            $isValid
        ]);

        return $isValid;
    } catch (\PDOException $e) {
        throw new PDOException($e->getMessage());
    }
}

function dd($data) {
    var_dump($data);
//    die;
}