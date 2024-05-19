
    <title>RVCTF Join Team</title>
    <link rel="stylesheet" href="static/css/register.css">
</head>
<body>
    <?php include 'templates/Components/stars.php';?>

    <div class="header">Join Team</div>
    <table class="centre_box">
        <form method = "POST" action = "backend/teamjoin.php">
            <tr>
                <td>Team Name: <input type="text" id = "team-name" name="team_name" width="auto" height="auto"></td>
            </tr>
            <tr>
                <td>Password: <input type="password" id = "password" name="team_password" width="auto" height="auto">
                    <br><span style="font-size: 50%">*please request from team leader</span>
                </td>
            </tr>
            <?php 
                            if (isset($_GET["error"]) && strpos($_GET["error"], "passworderror") != false) {
                                echo '
                                <tr>
                                    <td class = "error_msg">Password incorrect, please try again</td>
                                </tr>';
                            }
                            ?>
            <tr>
                <td><button class="button" type="submit">Enter</button></td>
            </tr>
        </form>
    </table>
</body>
</html>