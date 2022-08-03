<!DOCTYPE html>
<html lang="en">
<head>
    <title>RVCTF Login</title>
    <link rel="stylesheet" href="../../css/style_login.css">
</head>
<body style="padding-top:50px">
    <?php include "../stars.php" ?>
    <img src="../../images/RVCTF Neon Logo.png" id="cca_name">
    <div style="height:500px; min-height:100%; margin-top: 50px;">
        <table class="content">
            <tr>
                <td style="text-align:center; width: 25%;">
                    <img src="../../images/discord.png" id="discord_logo" onclick="location='https://discord.gg/uagKpY6c'"><br/>
                    <img src="../../images/instagram.png" id="IG_logo"  onclick="location='https://www.instagram.com/rv.ctf/'">
                </td>
                <td>
                    <table class="centre_box">
                        <form action="#">   
                            <tr style="text-align: center;"><td>Login</td></tr>
                            <tr style="text-align: center;">
                                <td>Username: <input type="text" name="login_username"></td>
                            </tr>
                            <tr style="text-align: center;">
                                <td>Password: <input type="password" name="login_password" width="auto" height="auto"></td>
                            </tr>
                            <tr style="text-align: center;">
                                <td>
                                <button type="submit" value="register" style="margin-right: 10%;">Register</button>
                                <button type="submit" value="login">Enter</button>
                                </td>
                            </tr>
                        </form>
                    </table>
                </td>
            </tr>
        </table>
    </div>
    <p style="color: white;">Made in collaboration with Rdev</p>
</body>
</html>