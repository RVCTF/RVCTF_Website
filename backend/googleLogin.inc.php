<?php
require_once "backend/google-api-php-client--PHP7.4/vendor/autoload.php";
require_once "backend/includes/connect.inc.php";
require_once "backend/googleLoginInfo.inc.php";
if (isset($_GET["origin"])) $origin = $_GET["origin"];
else $origin = "Login";
//creating client request to google
$client = new Google_Client();
$client->setClientId(clientID);
$client->setClientSecret(clientSecret);
$client->setRedirectUri(redirecturl);
$client->addScope("profile");
$client->addScope("email");
$googleUrl = $client->createAuthUrl();

function onLogin($conn,$user) {
   
    $_SESSION['userid'] = $user;
    print_r("userid:".$_SESSION['userid']);
    $token = GenerateRandomToken(128); // generate a token, should be 128 - 256 bit
    $tokenid = storeTokenForUser($conn, $user, $token);
    $cookie = $user . ':' . $token.':'.$tokenid;
    $mac = hash_hmac('sha256', $cookie, SECRET_KEY); //
    $cookie .= ':' . $mac;
    setcookie('rememberme', $cookie, [
        'expires'=>time() + (10 * 365 * 24 * 60 * 60),
        'path'=>"/",
        'secure'=>true,
        'httponly'=>true]);
}
if(isset($_GET['code'])){
    session_destroy();
    session_start();
    if(isset($_COOKIE['rememberme'])) {
        setcookie("rememberme", "", time()-3600,"/");
        // echo json_encode("Failure");
       }  
    // $_SESSION['logged_in'] = true;
    // $_SESSION[""]
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    if ($token["error"]) echo "<script>window.location.href = 'index.php?filename=login' </script>";
    $client->setAccessToken($token);
    $gauth = new Google_Service_Oauth2($client);
    $google_info = $gauth->userinfo->get();
    $email = $google_info->email;
    $name = $google_info->name;
    $picture = $google_info->picture;
    if ($email){
        $res = prepared_query($conn,"SELECT id,COUNT(*) FROM `ctf_users` WHERE `email` = ?",[$email,],"s");
        $res->bind_result($id,$countof);
        $res->fetch();
        mysqli_stmt_close($res);
        if ($countof>0){
            onLogin($conn,$id);
            header("Location:index.php?filename=home");
        }
        else{
            $exploded = explode(" ",rtrim($name," "));
            $lastexploded = end($exploded);
            $fromrvhs = strtoupper($lastexploded)==="(RVHS)";
            if (($google_info['hd']=="students.edu.sg" && $fromrvhs) || $google_info['hd'] == "moe.edu.sg"){
                $res = prepared_query($conn,"INSERT INTO `ctf_users`(`email`) VALUES (?)",[$email],"s");
                mysqli_stmt_close($res);
                $res = prepared_query($conn,"SELECT `id` FROM `ctf_users` WHERE `email` = ?",[$email,],"s");
                $res->bind_result($id);
                $res->fetch();
                mysqli_stmt_close($res);
                print_r("Hello");
                print_r($id);
                onLogin($conn,$id);
                header("Location:index.php?filename=teamsignup");

        }
            else{
                echo "<script>alert('Sorry! Only emails with the students.edu.sg or moe.edu.sg domain name, and from RVHS can signup!');
                 window.location.href = '../../../index.php?filename=Login' </script>";
            }
        }
    }

}

?>