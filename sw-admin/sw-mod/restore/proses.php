<?php

session_start();
if (empty($_SESSION['SESSION_USER']) && empty($_SESSION['SESSION_ID'])) {
    header('location:../../login/');
    exit;
} else {
    require_once '../../../sw-library/sw-config.php';
    require_once '../../login/login_session.php';
    include('../../../sw-library/sw-function.php');

    $action = $_POST["action"];
    switch ($action) {
        case 'backup':
            backup($connection, $DB_NAME);
            break;
        case 'restore':
            $response=array(
                "type"=>"",
                "message" => ""
            );
            if (!empty($_FILES)) {
                
                // Validating SQL file type by extensions
                if (!in_array(strtolower(pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION)), array(
                    "sql"
                ))) {
                    $response = array(
                        "type" => "error",
                        "message" => "Invalid File Type"
                    );
                } else {

                    if (is_uploaded_file($_FILES["file"]["tmp_name"])) {
                        $move = move_uploaded_file($_FILES["file"]["tmp_name"], $_FILES["file"]["name"]);
                        
                        $response=restoreMysqlDB($_FILES["file"]["name"], $connection);
                    }
                }
            }
            echo $response['message'];
            die;
            break;
    }
}

function backup($conn, $database_name)
{
    $tables = array();
    $sql = "SHOW TABLES";
    $result = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_row($result)) {
        $tables[] = $row[0];
    }
    $sqlScript = "";
    $sqlScript .= "\n\n USE " . $database_name . ";\n\n";


    foreach ($tables as $table) {

        // Prepare SQLscript for creating table structure
        $query = "SHOW CREATE TABLE $table";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_row($result);
        $sqlScript .= "\n\n DROP TABLE IF EXISTS  " . $table . ";\n\n";
        $sqlScript .= "\n\n" . $row[1] . ";\n\n";
        $query = "SELECT * FROM $table";
        $result = mysqli_query($conn, $query);
        $columnCount = mysqli_num_fields($result);
        // Prepare SQLscript for dumping data for each table
        for ($i = 0; $i < $columnCount; $i++) {
            while ($row = mysqli_fetch_row($result)) {
                $sqlScript .= "INSERT INTO $table VALUES(";
                for ($j = 0; $j < $columnCount; $j++) {
                    $row[$j] = $row[$j];
                    if (isset($row[$j])) {
                        $sqlScript .= '"' . $row[$j] . '"';
                    } else {
                        $sqlScript .= '""';
                    }
                    if ($j < ($columnCount - 1)) {
                        $sqlScript .= ',';
                    }
                }
                $sqlScript .= ");\n";
            }
        }

        $sqlScript .= "\n";
    }

    if (!empty($sqlScript)) {

        // Save the SQL script to a backup file
        $backup_file_name = $database_name . '_backup_' . time() . '.sql';
        $fileHandler = fopen($backup_file_name, 'w+');
        $number_of_lines = fwrite($fileHandler, $sqlScript);
        fclose($fileHandler);
        // Download the SQL backup file to the browser
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($backup_file_name));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($backup_file_name));
        ob_clean();
        flush();
        readfile($backup_file_name);
        exec('rm ' . $backup_file_name);
    } 
}

function restoreMysqlDB($filePath, $conn)
    {
       
        $sql = '';
        $error = '';

        if (file_exists($filePath)) {
            $lines = file($filePath);
            
            foreach ($lines as $line) {

                // Ignoring comments from the SQL script
                if (substr($line, 0, 2) == '--' || $line == '') {
                    continue;
                }

                $sql .= $line;

                if (substr(trim($line), -1, 1) == ';') {

                    $result = mysqli_query($conn, $sql);
                    if (!$result) {
                        $error .= mysqli_error($conn) . "\n";
                    }
                    $sql = '';
                }
            } // end foreach

            if ($error) {
                $response = array(
                    "type" => "error",
                    "message" => $error
                );
            } else {
                $response = array(
                    "type" => "success",
                    "message" => "Database Restore Completed Successfully."
                );
            }
        }
        // end if file exists
        return $response;
    }
