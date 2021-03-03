<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
$root = $_SERVER['DOCUMENT_ROOT'];
if (!file_exists($root . '/../config/autoload/database.local.php')) {
    $error = '';
    if (count($_POST)) {
        $data = $_POST;
        $result = mysqli_connect($data['DB_HOSTNAME'], $data['DB_USER'], $data['DB_PASSWORD'], $data['DB_NAME']);
        if (isset($result->server_info)) {
            $tpl = file_get_contents(__DIR__ . '/database.local.php.dist');
            $config = str_replace(array_keys($data), array_values($data), $tpl);
            file_put_contents($root . '/../config/autoload/database.local.php', $config);
        }
        if ($result === false) {
            $error = 'Error connecting to DB: ' . mysqli_connect_error();
        } else {
            header("Location: /");
            exit;
        }
    }
    echo("
<!DOCTYPE html>
<html>
<head>
<title>Configure DB</title>
<link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css\" integrity=\"sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO\" crossorigin=\"anonymous\">
</head>
<body>

<form class='container' method=\"post\">
        <div class='alert alert-danger'>$error</div>

<div class='form-group'>
 <label for=\"DB_NAME\">DB_NAME</label>
            <input class=\"form-control\"  name=\"DB_NAME\" value=\"pars\">
</div>
           
<div class='form-group'>
   <label for=\"DB_USER\">DB_USER</label>
            <input class=\"form-control\"  name=\"DB_USER\" value=\"pars\">     
</div>
                
<div class='form-group'>
            <label for=\"DB_PASSWORD\">DB_PASSWORD</label>
            <input class=\"form-control\" name=\"DB_PASSWORD\" value=\"pars\">
                        </div>

<div class='form-group'>

            <label for=\"DB_HOSTNAME\">DB_HOSTNAME</label>
            <input class=\"form-control\"  name=\"DB_HOSTNAME\" value=\"localhost\">
</div>
<div class='form-group'>

            <input class='btn btn-primary' type=\"submit\">
            </div>

            </form>
            <br>
</body>
</html>
            
            ");
    exit;
}
