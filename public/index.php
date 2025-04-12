<?php include '@layout.php';
page_layout_include('main')
?>

<?php

$schemas = Config::load('schemas');

foreach ($schemas as $t) {
    if ($t) {
        DB::getInstance()->sqlexec($t->getSQL());
    }
}

$users = EntityModel::new('User');
$data = [];
$users->validate($data);
dd($users->errors());
$user = $users->create($data);

?>

Pagina Inicial