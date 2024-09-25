<?php
// Bind to a port for Render Web Service
$port = getenv('PORT'); // Render will provide the PORT environment variable

// Start a simple HTTP server
$socket = stream_socket_server("tcp://0.0.0.0:$port", $errno, $errstr);

if (!$socket) {
    echo "Error: Unable to create socket: $errstr ($errno)\n";
    exit(1);
}

echo "Server running on port $port\n";

// Function to handle incoming requests (even though we're not really serving anything)
function handleRequest($client) {
    fwrite($client, "HTTP/1.1 200 OK\r\n");
    fwrite($client, "Content-Type: text/plain\r\n");
    fwrite($client, "Connection: close\r\n");
    fwrite($client, "\r\n");
    fwrite($client, "This is a background process running as a web service!\n");
    fclose($client);
}

// Keep accepting incoming connections in the background
while ($client = @stream_socket_accept($socket, -1)) {
    handleRequest($client);
}

// --- Your existing background process code starts here ---

// Get environment variables for database connection
$servername = getenv('DB_SERVERNAME');  
$username = getenv('DB_USERNAME');         
$password = getenv('DB_PASSWORD');   
$dbname = getenv('DB_NAME');            
$port = getenv('DB_PORT');

// KoboToolbox API details
$kobo_api_url = 'https://eu.kobotoolbox.org/api/v2/assets/arijX3itvjmaPxmCKPgkqz/data/?format=json';
$kobo_token = 'ea97948efb2a6f133463d617277b69caff728630';

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the latest submission time from the database
$sql_last_update = "SELECT MAX(submission_time) as last_updated_time FROM kobo_data";
$result = $conn->query($sql_last_update);
$last_updated_time = null;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $last_updated_time = $row['last_updated_time'];
}

// Log the last updated time
file_put_contents('debug_log.txt', "Last Updated Time: $last_updated_time\n", FILE_APPEND);

// Append the last updated time to the KoboToolbox API URL
if ($last_updated_time) {
    $kobo_api_url .= '&_last_updated__gt=' . urlencode($last_updated_time);
}

// Log the final API URL
file_put_contents('debug_log.txt', "API URL: $kobo_api_url\n", FILE_APPEND);

// Set up the cURL request to fetch data from KoboToolbox
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $kobo_api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Token ' . $kobo_token,
]);

// Execute the cURL request
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Decode the JSON response
$data = json_decode($response, true);

// Log the response from KoboToolbox
file_put_contents('kobo_response_log.txt', print_r($data, true), FILE_APPEND);

// Check if data is received
if ($http_code == 200 && isset($data['results'])) {
    $counter = 0; // Optional counter to limit the number of records processed at once
    foreach ($data['results'] as $record) {
        if ($counter >= 100) break; // Optional limit to process only a certain number of records
        $counter++;

        // Retrieve fields from KoboToolbox JSON data, using isset to handle missing fields
        $submission_id = $record['_id'];
        $tstart = isset($record['Tstart']) ? $record['Tstart'] : null;
        $tend = isset($record['Tend']) ? $record['Tend'] : null;
        $ttoday = isset($record['Ttoday']) ? $record['Ttoday'] : null;
        $username = isset($record['username']) ? $record['username'] : null;
        $phonenumber = isset($record['phonenumber']) ? $record['phonenumber'] : null;
        $deviceid = isset($record['deviceid']) ? $record['deviceid'] : null;
        $date_interview = isset($record['g_location/date_interview']) ? $record['g_location/date_interview'] : null;
        $name_interview = isset($record['g_location/name_interview']) ? $record['g_location/name_interview'] : null;
        $sex_interview = isset($record['g_location/sex_interview']) ? $record['g_location/sex_interview'] : null;
        $name_respon = isset($record['g_location/name_respon']) ? $record['g_location/name_respon'] : null;
        $sex_respon = isset($record['g_location/sex_respon']) ? $record['g_location/sex_respon'] : null;
        $work_company = isset($record['g_location/work_company']) ? $record['g_location/work_company'] : null;
        $province = isset($record['g_location/province']) ? $record['g_location/province'] : null;
        $district = isset($record['g_location/district']) ? $record['g_location/district'] : null;
        $commune = isset($record['g_location/commune']) ? $record['g_location/commune'] : null;
        $village = isset($record['g_location/village']) ? $record['g_location/village'] : null;

        $q020101 = isset($record['g_q0201/q020101']) ? $record['g_q0201/q020101'] : null;
        $q020102 = isset($record['g_q0201/q020102']) ? $record['g_q0201/q020102'] : null;
        $q020103 = isset($record['g_q0201/q020103']) ? $record['g_q0201/q020103'] : null;
        $q020104 = isset($record['g_q0201/q020104']) ? $record['g_q0201/q020104'] : null;
        $q020105 = isset($record['g_q0201/q020105']) ? $record['g_q0201/q020105'] : null;
        $q020106 = isset($record['g_q0201/q020106']) ? $record['g_q0201/q020106'] : null;
        $q020201 = isset($record['q020201']) ? $record['q020201'] : null;
        $q020203 = isset($record['q020203']) ? $record['q020203'] : null;
        $q02020401 = isset($record['g_q020204/q02020401']) ? $record['g_q020204/q02020401'] : null;
        $q02020402 = isset($record['g_q020204/q02020402']) ? $record['g_q020204/q02020402'] : null;
        $q02020403 = isset($record['g_q020204/q02020403']) ? $record['g_q020204/q02020403'] : null;
        $q02020404 = isset($record['g_q020204/q02020404']) ? $record['g_q020204/q02020404'] : null;
        $q02020405 = isset($record['g_q020204/q02020405']) ? $record['g_q020204/q02020405'] : null;
        $q02020499 = isset($record['g_q020204/q02020499']) ? $record['g_q020204/q02020499'] : null;
        $q020205 = isset($record['q020205']) ? $record['q020205'] : null;

        $q030101 = isset($record['g_q0301/q030101']) ? $record['g_q0301/q030101'] : null;
        $q030101a = isset($record['g_q0301/q030101a']) ? $record['g_q0301/q030101a'] : null;
        $q030102 = isset($record['g_q030102/q030102']) ? $record['g_q030102/q030102'] : null;
        $q030102a = isset($record['g_q030102/q030102a']) ? $record['g_q030102/q030102a'] : null;
        $q030103 = isset($record['q030103']) ? $record['q030103'] : null;
        $q030201 = isset($record['g_q0302/q030201']) ? $record['g_q0302/q030201'] : null;
        $q030301 = isset($record['g_q0303/q030301']) ? $record['g_q0303/q030301'] : null;
        $q030401 = isset($record['g_q0304/q030401']) ? $record['g_q0304/q030401'] : null;
        $q030402 = isset($record['g_q0304/q030402']) ? $record['g_q0304/q030402'] : null;
        $q030403 = isset($record['g_q0304/q030403']) ? $record['g_q0304/q030403'] : null;
        $q030404 = isset($record['g_q0304/q030404']) ? $record['g_q0304/q030404'] : null;
        $q030501 = isset($record['g_q0305/q030501']) ? $record['g_q0305/q030501'] : null;
        $q030502 = isset($record['g_q0305/q030502']) ? $record['g_q0305/q030502'] : null;
        $q030503 = isset($record['g_q0305/q030503']) ? $record['g_q0305/q030503'] : null;
        $q030601 = isset($record['g_q0306/q030601']) ? $record['g_q0306/q030601'] : null;
        $q030602 = isset($record['g_q0306/q030602']) ? $record['g_q0306/q030602'] : null;

        $q040101 = isset($record['g_q0401/q040101']) ? $record['g_q0401/q040101'] : null;
        $q040102 = isset($record['g_q0401/q040102']) ? $record['g_q0401/q040102'] : null;
        $q040103 = isset($record['g_q0401/q040103']) ? $record['g_q0401/q040103'] : null;
        $q040104 = isset($record['g_q0401/q040104']) ? $record['g_q0401/q040104'] : null;
        $q040201 = isset($record['g_q0402/q040201']) ? $record['g_q0402/q040201'] : null;
        $q040202 = isset($record['g_q0402/q040202']) ? $record['g_q0402/q040202'] : null;
        $q040301 = isset($record['g_q0403/q040301']) ? $record['g_q0403/q040301'] : null;
        $q040302 = isset($record['g_q0403/q040302']) ? $record['g_q0403/q040302'] : null;

        $income6month_01 = isset($record['g_q0501/income6month_01']) ? $record['g_q0501/income6month_01'] : null;
        $income6month_02 = isset($record['g_q0501/income6month_02']) ? $record['g_q0501/income6month_02'] : null;
        $income6month_03 = isset($record['g_q0501/income6month_03']) ? $record['g_q0501/income6month_03'] : null;
        $income6month_04 = isset($record['g_q0501/income6month_04']) ? $record['g_q0501/income6month_04'] : null;
        $income6month_05 = isset($record['g_q0501/income6month_05']) ? $record['g_q0501/income6month_05'] : null;
        $income6month_06 = isset($record['g_q0501/income6month_06']) ? $record['g_q0501/income6month_06'] : null;
        $income6month99 = isset($record['g_q0501/income6month99']) ? $record['g_q0501/income6month99'] : null;
        $income6month_total = isset($record['g_q0501/income6month_total']) ? $record['g_q0501/income6month_total'] : null;
        $income_01 = isset($record['g_q0501/income_01']) ? $record['g_q0501/income_01'] : null;
        $income_02 = isset($record['g_q0501/income_02']) ? $record['g_q0501/income_02'] : null;
        $income_03 = isset($record['g_q0501/income_03']) ? $record['g_q0501/income_03'] : null;
        $income_04 = isset($record['g_q0501/income_04']) ? $record['g_q0501/income_04'] : null;
        $income_05 = isset($record['g_q0501/income_05']) ? $record['g_q0501/income_05'] : null;
        $income_06 = isset($record['g_q0501/income_06']) ? $record['g_q0501/income_06'] : null;
        $income99 = isset($record['g_q0501/income99']) ? $record['g_q0501/income99'] : null;
        $income_total = isset($record['g_q0501/income_total']) ? $record['g_q0501/income_total'] : null;

        $q050102 = isset($record['q050102']) ? $record['q050102'] : null;
        $q050103 = isset($record['q050103']) ? $record['q050103'] : null;
        $q050201 = isset($record['g_q0502/q050201']) ? $record['g_q0502/q050201'] : null;
        $q050202 = isset($record['g_q0502/q050202']) ? $record['g_q0502/q050202'] : null;
        $q050203 = isset($record['g_q0502/q050203']) ? $record['g_q0502/q050203'] : null;
        $q050204 = isset($record['g_q0502/q050204']) ? $record['g_q0502/q050204'] : null;
        $animals6month_01 = isset($record['g_q0503/animals6month_01']) ? $record['g_q0503/animals6month_01'] : null;
        $animals6month_02 = isset($record['g_q0503/animals6month_02']) ? $record['g_q0503/animals6month_02'] : null;
        $animals6month_03 = isset($record['g_q0503/animals6month_03']) ? $record['g_q0503/animals6month_03'] : null;
        $animals6month_99 = isset($record['g_q0503/animals6month_99']) ? $record['g_q0503/animals6month_99'] : null;
        $animals6month_total = isset($record['g_q0503/animals6month_total']) ? $record['g_q0503/animals6month_total'] : null;
        $animals_01 = isset($record['g_q0503/animals_01']) ? $record['g_q0503/animals_01'] : null;
        $animals_02 = isset($record['g_q0503/animals_02']) ? $record['g_q0503/animals_02'] : null;
        $animals_03 = isset($record['g_q0503/animals_03']) ? $record['g_q0503/animals_03'] : null;
        $animals_99 = isset($record['g_q0503/animals_99']) ? $record['g_q0503/animals_99'] : null;
        $animals_total = isset($record['g_q0503/animals_total']) ? $record['g_q0503/animals_total'] : null;

        $q050302 = isset($record['g_q050302/q050302']) ? $record['g_q050302/q050302'] : null;
        $q050401 = isset($record['q0504/q050401']) ? $record['q0504/q050401'] : null;
        $q050402 = isset($record['q0504/q050402']) ? $record['q0504/q050402'] : null;

        $comments = isset($record['comments']) ? $record['comments'] : null;
        $ifinish = isset($record['iFinish']) ? $record['iFinish'] : null;
        $version = isset($record['__version__']) ? $record['__version__'] : null;
        $instance_id = isset($record['meta/instanceID']) ? $record['meta/instanceID'] : null;
        $uuid = isset($record['_uuid']) ? $record['_uuid'] : null;
        $submission_time = isset($record['_submission_time']) ? $record['_submission_time'] : null;
        $submitted_by = isset($record['_submitted_by']) ? $record['_submitted_by'] : null;


        // Check if the submission already exists in the database
        $sql_check = "SELECT * FROM kobo_data WHERE submission_id = '$submission_id'";
        $result_check = $conn->query($sql_check);

        if ($result_check->num_rows > 0) {
            // Update the existing record
            $sql_update = "UPDATE kobo_data SET 
                tstart = '$tstart', 
                tend = '$tend', 
                ttoday = '$ttoday', 
                username = '$username', 
                phonenumber = '$phonenumber', 
                deviceid = '$deviceid', 
                date_interview = '$date_interview', 
                name_interview = '$name_interview', 
                sex_interview = '$sex_interview', 
                name_respon = '$name_respon', 
                sex_respon = '$sex_respon', 
                work_company = '$work_company', 
                province = '$province', 
                district = '$district', 
                commune = '$commune', 
                village = '$village', 
                q020101 = '$q020101', 
                q020102 = '$q020102', 
                q020103 = '$q020103', 
                q020104 = '$q020104', 
                q020105 = '$q020105', 
                q020106 = '$q020106', 
                q020201 = '$q020201', 
                q020203 = '$q020203',
                q02020401 = '$q02020401', 
                q02020402 = '$q02020402', 
                q02020403 = '$q02020403', 
                q02020404 = '$q02020404', 
                q02020405 = '$q02020405', 
                q02020499 = '$q02020499', 
                q020205 = '$q020205', 
                q030101 = '$q030101', 
                q030101a = '$q030101a', 
                q030102 = '$q030102', 
                q030102a = '$q030102a', 
                q030103 = '$q030103', 
                q030201 = '$q030201', 
                q030301 = '$q030301', 
                q030401 = '$q030401', 
                q030402 = '$q030402', 
                q030403 = '$q030403', 
                q030404 = '$q030404', 
                q030501 = '$q030501', 
                q030502 = '$q030502', 
                q030503 = '$q030503', 
                q030601 = '$q030601', 
                q030602 = '$q030602', 
                q040101 = '$q040101', 
                q040102 = '$q040102', 
                q040103 = '$q040103', 
                q040104 = '$q040104', 
                q040201 = '$q040201', 
                q040202 = '$q040202', 
                q040301 = '$q040301', 
                q040302 = '$q040302', 
                income6month_01 = '$income6month_01', 
                income6month_02 = '$income6month_02', 
                income6month_03 = '$income6month_03', 
                income6month_04 = '$income6month_04', 
                income6month_05 = '$income6month_05', 
                income6month_06 = '$income6month_06', 
                income6month99 = '$income6month99', 
                income6month_total = '$income6month_total', 
                income_01 = '$income_01', 
                income_02 = '$income_02', 
                income_03 = '$income_03', 
                income_04 = '$income_04', 
                income_05 = '$income_05', 
                income_06 = '$income_06', 
                income99 = '$income99', 
                income_total = '$income_total', 
                q050102 = '$q050102', 
                q050103 = '$q050103', 
                q050201 = '$q050201', 
                q050202 = '$q050202', 
                q050203 = '$q050203', 
                q050204 = '$q050204', 
                animals6month_01 = '$animals6month_01', 
                animals6month_02 = '$animals6month_02', 
                animals6month_03 = '$animals6month_03', 
                animals6month_99 = '$animals6month_99', 
                animals6month_total = '$animals6month_total', 
                animals_01 = '$animals_01', 
                animals_02 = '$animals_02', 
                animals_03 = '$animals_03', 
                animals_99 = '$animals_99', 
                animals_total = '$animals_total', 
                q050302 = '$q050302', 
                q050401 = '$q050401', 
                q050402 = '$q050402', 
                comments = '$comments', 
                ifinish = '$ifinish', 
                version = '$version', 
                instance_id = '$instance_id', 
                uuid = '$uuid', 
                submission_time = '$submission_time', 
                submitted_by = '$submitted_by'
                WHERE submission_id = '$submission_id'";

            if ($conn->query($sql_update) === TRUE) {
                echo "Record updated successfully for submission ID $submission_id\n";
            } else {
                echo "Error updating record: " . $conn->error;
            }
        } else {
            // Insert a new record
            $sql_insert = "INSERT INTO kobo_data (
               submission_id, tstart, tend, ttoday, username, phonenumber, deviceid, date_interview, 
                name_interview, sex_interview, name_respon, sex_respon, work_company, province, district, commune, 
                village, q020101, q020102, q020103, q020104, q020105, q020106, q020201, q020203, q02020401, 
                q02020402, q02020403, q02020404, q02020405, q02020499, q020205, q030101, q030101a, q030102, 
                q030102a, q030103, q030201, q030301, q030401, q030402, q030403, q030404, q030501, q030502, 
                q030503, q030601, q030602, q040101, q040102, q040103, q040104, q040201, q040202, q040301, 
                q040302, income6month_01, income6month_02, income6month_03, income6month_04, income6month_05, 
                income6month_06, income6month99, income6month_total, income_01, income_02, income_03, income_04, 
                income_05, income_06, income99, income_total, q050102, q050103, q050201, q050202, q050203, 
                q050204, animals6month_01, animals6month_02, animals6month_03, animals6month_99, 
                animals6month_total, animals_01, animals_02, animals_03, animals_99, animals_total, q050302, 
                q050401, q050402, comments, ifinish, version, instance_id, uuid, submission_time, submitted_by)
                VALUES (
                '$submission_id', '$tstart', '$tend', '$ttoday', '$username', '$phonenumber', 
                '$deviceid', '$date_interview', '$name_interview', '$sex_interview', '$name_respon', '$sex_respon', 
                '$work_company', '$province', '$district', '$commune', '$village', '$q020101', '$q020102', '$q020103', 
                '$q020104', '$q020105', '$q020106', '$q020201', '$q020203', '$q02020401', '$q02020402', '$q02020403', 
                '$q02020404', '$q02020405', '$q02020499', '$q020205', '$q030101', '$q030101a', '$q030102', 
                '$q030102a', '$q030103', '$q030201', '$q030301', '$q030401', '$q030402', '$q030403', '$q030404', 
                '$q030501', '$q030502', '$q030503', '$q030601', '$q030602', '$q040101', '$q040102', '$q040103', 
                '$q040104', '$q040201', '$q040202', '$q040301', '$q040302', '$income6month_01', '$income6month_02', 
                '$income6month_03', '$income6month_04', '$income6month_05', '$income6month_06', '$income6month99', 
                '$income6month_total', '$income_01', '$income_02', '$income_03', '$income_04', '$income_05', 
                '$income_06', '$income99', '$income_total', '$q050102', '$q050103', '$q050201', '$q050202', '$q050203', 
                '$q050204', '$animals6month_01', '$animals6month_02', '$animals6month_03', '$animals6month_99', 
                '$animals6month_total', '$animals_01', '$animals_02', '$animals_03', '$animals_99', '$animals_total', 
                '$q050302', '$q050401', '$q050402', '$comments', '$ifinish', '$version', '$instance_id', '$uuid', 
                '$submission_time', '$submitted_by')";

            if ($conn->query($sql_insert) === TRUE) {
                echo "New record created successfully for submission ID $submission_id\n";
            } else {
                echo "Error inserting new record: " . $conn->error;
            }
        }
    }
} else {
    echo "Failed to retrieve data from KoboToolbox. HTTP Code: " . $http_code;
    file_put_contents('error_log.txt', "Kobo API response: " . $response . "\n", FILE_APPEND); 
}

// Close the MySQL connection
$conn->close();
?>







