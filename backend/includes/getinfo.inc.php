<?php
function getTeamStatusFromUserId($conn, $userId){
    $sql = "SELECT 
                teams.team_name, 
                CASE 
                    WHEN teams.teamleader_id = ? THEN 'leader'
                    ELSE 'member'
                END AS status,
                teams.team_id
            FROM 
                teams 
            JOIN 
                teamates ON teamates.team_id = teams.team_id
            WHERE 
                teamates.user_id = ?;
            ";
    $res = prepared_query($conn,$sql,[$userId,$userId],"ii");
    $res -> bind_result($teamname,$status,$teamid);
    if (!($res -> fetch())) return false;
    mysqli_stmt_close($res);
    return ["teamname"=>$teamname,"position"=>$status,"teamid"=>$teamid];
}

function getPoints($conn,$userid){
    $sql = "SELECT SUM(challenges.points) + SUM(admin_points.points) FROM completedchallenges 
            JOIN challenges ON completedchallenges.challenge_id = challenges.id
            JOIN admin_points ON admin_points.user_id = completedchallenges.user_id 
            WHERE completedchallenges.user_id = ?";
    $res = prepared_query($conn,$sql,[$userid],"i");
    $res -> bind_result($points);
    $res -> fetch();
    mysqli_stmt_close($res);
    $points = $points ? $points : 0;
    return $points;
}

function getUserInfo($conn,$userid){
    $sql = "SELECT username,email,admin FROM ctf_users WHERE id = ?";
    $res = prepared_query($conn,$sql,[$userid],"i");
    $res -> bind_result($username,$email,$admin);
    if (!($res -> fetch())) return false;
    mysqli_stmt_close($res);
    return ["username"=>$username,"email"=>$email,"admin"=>$admin];
}

function getPostParam($param) {
    return isset($_POST[$param]) ? htmlspecialchars($_POST[$param]) : null;
}

function getGetParam($param) {
    return isset($_GET[$param]) ? htmlspecialchars($_GET[$param]) : null;
}
?>