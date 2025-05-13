<?php
// // this needs to be commented out for the code to work
require_once __DIR__."/../../../private/rvctf_passwords.inc.php";
// // this needs to be present when hosting on localhost
// define("SERVERNAME", "localhost");
// define("DB_USER", "root");
// define("DB_PASS", "");
// define("DB_NAME", "ctfdb");
define("SECRET_KEY","uoqcy169(361");
define("salt","vnljh19d1996v");
define("CSRF_TOKEN_SECRET",'wxVy4t0EpypTDfPsEhqXfU92wsjnFce1bLMtbDyKWpbiVXGUp1D');
define("FLAG_SALT","3Y_J2ACWccfmI8ve?(q_fkLl");

$conn = new mysqli(SERVERNAME,DB_USER,DB_PASS,DB_NAME);
function prepared_query($mysqli, $sql, $params, $types = "")
{
    $stmt = $mysqli->prepare($sql);
    if ($stmt->bind_param($types, ...$params)===false){
        echo "parameter binding error";
    	return false;
    }
    if ($stmt->execute()===false){
        echo "Statement execution error";
    	return false;
    }
    return $stmt;
}

function fetchDataFromQuery($conn, $sql, $params, $types,$errorMessage = "Error in fetching data") {
    if ($params == []) {
        $stmt = $conn->query($sql);
        if (!$stmt){
            onError($conn,$errorMessage);
        }
        $data = [];
        while ($row = $stmt->fetch_assoc()) {
            $data[] = $row;
        }
        return $data;
    } 
    $stmt = prepared_query($conn, $sql, $params, $types);
    if (!$stmt){
        onError($conn,$errorMessage);
    }
    $result = iimysqli_stmt_get_result($stmt);
    $data = [];
    while ($row = iimysqli_result_fetch_assoc_array($result)) {
        $data[] = $row;
    }
    mysqli_stmt_close($stmt);
    return $data;
}

function executeQuery($conn, $sql, $params, $types,$returnId = false,$errorMessage = "Error in executing query") {
    $stmt = prepared_query($conn, $sql, $params, $types);
    if (!$stmt){
        onError($conn,$errorMessage);
    }
    if ($returnId) {
        $id = $stmt->insert_id;
        mysqli_stmt_close($stmt);
        return $id;
    }
    mysqli_stmt_close($stmt);
}

class iimysqli_result
{
    public $stmt, $nCols;
}   

function iimysqli_stmt_get_result($stmt)
{
    $metadata = mysqli_stmt_result_metadata($stmt);
    $ret = new iimysqli_result;
    if (!$ret)
        return NULL;
    $fields = $metadata->fetch_fields();
    $ret->fields = $fields;
    $ret->nCols = mysqli_num_fields($metadata);
    $ret->stmt = $stmt;

    mysqli_free_result($metadata);
    return $ret;
}

function iimysqli_result_fetch_array(&$result)
{
    $ret = array();
    $code = "return mysqli_stmt_bind_result(\$result->stmt ";

    for ($i=0; $i<$result->nCols; $i++)
    {
        $ret[$i] = NULL;
        $code .= ", \$ret['" .$i ."']";
    };

    $code .= ");";
    if (!eval($code)) { return NULL; };

    // This should advance the "$stmt" cursor.
    if (!mysqli_stmt_fetch($result->stmt)) { return NULL; };

    // Return the array we built.
    return $ret;
}



function iimysqli_result_fetch_assoc_array(&$result)
{
    $ret = array();
    $code = "return mysqli_stmt_bind_result(\$result->stmt ";
    $fields = $result->fields;
    for ($i = 0; $i < $result->nCols; $i++) {
        $ret[$fields[$i]->name] = NULL;
        $code .= ", \$ret['" . $fields[$i]->name . "']";
    }
    ;

    $code .= ");";
    if (!eval($code)) {
        return NULL;
    }
    ;

    // This should advance the "$stmt" cursor.
    if (!mysqli_stmt_fetch($result->stmt)) {
        return NULL;
    }
    ;

    // Return the array we built.
    return $ret;
}
function GenerateRandomToken($length = 32){
    if(!isset($length) || intval($length) <= 8 ){
      $length = 32;
    }
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes($length));
    }
    if (function_exists('mcrypt_create_iv')) {
        return bin2hex(mcrypt_create_iv($length, MCRYPT_DEV_URANDOM));
    }
    if (function_exists('openssl_random_pseudo_bytes')) {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}
function storeTokenForUser($conn,$user,$token){
    $token = hash_hmac("sha256",$token,salt);
    $SQL = 'INSERT INTO `tokens`(`token`,`user_id`) VALUES (?,?)';
    
    print_r($token);
    print_r($user);
    $stmt = prepared_query($conn,$SQL,[$token,$user],"si");
    if (!$stmt) return false;
    mysqli_stmt_close($stmt);
    $SQL = "SELECT MAX(`token_id`) FROM `tokens` WHERE `token` = ? AND `user_id` = ?";
    $stmt = prepared_query($conn,$SQL,[$token,$user],"si");
    if (!$stmt) return false;
    $res = iimysqli_stmt_get_result($stmt);
    $tokenid = iimysqli_result_fetch_array($res)[0];
    return $tokenid;
}

function fetchTokenByUserName($conn,$user,$tokenid){
    # Finds the user's token in the database
    $SQL = 'SELECT `token` FROM `tokens` WHERE `user_id` = ? AND `token_id` = ?';
    $stmt = prepared_query($conn,$SQL,[$user,$tokenid],"si");
    if (!$stmt) return false;
    $res = iimysqli_stmt_get_result($stmt);
    if (!$res) return false;
    $res2 = iimysqli_result_fetch_array($res);
    if (!$res2) return false;
    $token = $res2[0];
    mysqli_stmt_close($stmt);
    return $token;
}

function destroyCookie($conn){
    
    $cookie = isset($_COOKIE['rememberme']) ? $_COOKIE['rememberme'] : '';
    
    // print_r($_COOKIE);
    if (!$cookie) return false;
    list ($user, $token, $tokenid,$mac) = explode(':', $cookie);
    if (!hash_equals(hash_hmac('sha256', $user . ':' . $token.':'.$tokenid, SECRET_KEY), $mac)) return false;
    
    $SQL = 'DELETE FROM `tokens` WHERE `user_id` = ? AND `token_id` = ?';
    $stmt = prepared_query($conn,$SQL,[$user,$tokenid],"ii");
    mysqli_stmt_close($stmt);
    setcookie("rememberme", "", time()-3600,"/");
}



function rememberMe($conn) {
    $cookie = isset($_COOKIE['rememberme']) ? $_COOKIE['rememberme'] : '';
    // If cookie exists
    if ($cookie) {
        list ($user, $token, $tokenid,$mac) = explode(':', $cookie);
        if (!hash_equals(hash_hmac('sha256', $user . ':' . $token.':'.$tokenid, SECRET_KEY), $mac)) {
            return false;
        }
        $usertoken = fetchTokenByUserName($conn,$user,$tokenid);
        if (!$usertoken) return;
        if (hash_equals($usertoken, hash_hmac("sha256",$token,salt))) {
            $_SESSION["loggedin"] = true;
            $_SESSION["userid"] = $user;
            return true;
        }
        else{
            return false;
        }
    }

}

function onError($conn,$error,$additionalData = []) {
    
    $data = array_merge(["error"=>$error],$additionalData); 
    echo json_encode($data, JSON_FORCE_OBJECT);
    mysqli_close($conn);
    exit();
}

function onSuccess($conn,$success,$additionalData = []){
    $data = array_merge(["success"=>$success],$additionalData);
    error_log(">>> onSuccess CALLED with: " . json_encode($data));
    echo json_encode($data, JSON_FORCE_OBJECT);
    mysqli_close($conn);
    exit();
}
?>
