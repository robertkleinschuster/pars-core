<?php
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
            http_redirect('/');
        }
    }
    echo('
            <form method="post">
            <label for="DB_NAME">DB_NAME</label>
            <input name="DB_NAME" value="pars">
            <br>
            <label for="DB_USER">DB_USER</label>
            <input name="DB_USER" value="pars">            
                        <br>
            <label for="DB_PASSWORD">DB_PASSWORD</label>
            <input name="DB_PASSWORD" value="pars">
                        <br>

            <label for="DB_HOSTNAME">DB_HOSTNAME</label>
            <input name="DB_HOSTNAME" value="localhost">
                        <br>

            <input type="submit">
            </form>
            <br>
            ' . $error);
    exit;
}
