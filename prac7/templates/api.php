<?php
/** @var array $preferences */
/** @var array $ui */
?>
<h1><?php echo htmlspecialchars($ui['apiTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1>
<p><?php echo htmlspecialchars($ui['apiIntro'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> <code>/api.php/weather</code>, <code>/api.php/users</code> <?php echo htmlspecialchars($ui['apiAnd'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> <code>/api.php/uploads</code>.</p>
<h2><?php echo htmlspecialchars($ui['apiWeatherHeader'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2><ul>
    <li>GET /api.php/weather — <?php echo htmlspecialchars($preferences['language'] === 'en' ? 'list all records' : 'список всех записей', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
    <li>GET /api.php/weather/{id} — <?php echo htmlspecialchars($preferences['language'] === 'en' ? 'get record by id' : 'получить запись по id', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
    <li>POST /api.php/weather — <?php echo htmlspecialchars($preferences['language'] === 'en' ? 'create record' : 'создать запись', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
    <li>PUT /api.php/weather/{id} — <?php echo htmlspecialchars($preferences['language'] === 'en' ? 'update record' : 'обновить запись', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
    <li>DELETE /api.php/weather/{id} — <?php echo htmlspecialchars($preferences['language'] === 'en' ? 'delete record' : 'удалить запись', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
</ul>
<h2><?php echo htmlspecialchars($ui['apiUsersHeader'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2><ul>
    <li>GET /api.php/users — <?php echo htmlspecialchars($preferences['language'] === 'en' ? 'list users (without passwords)' : 'список пользователей (без паролей)', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
    <li>GET /api.php/users/{id} — <?php echo htmlspecialchars($preferences['language'] === 'en' ? 'get user' : 'получить пользователя', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
    <li>POST /api.php/users — <?php echo htmlspecialchars($preferences['language'] === 'en' ? 'create user' : 'создать пользователя', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
    <li>PUT /api.php/users/{id} — <?php echo htmlspecialchars($preferences['language'] === 'en' ? 'update user' : 'обновить пользователя', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
    <li>DELETE /api.php/users/{id} — <?php echo htmlspecialchars($preferences['language'] === 'en' ? 'delete user' : 'удалить пользователя', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
</ul>
<h2><?php echo htmlspecialchars($ui['apiPdfHeader'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2><ul>
    <li>GET /api.php/uploads — <?php echo htmlspecialchars($preferences['language'] === 'en' ? 'list uploaded PDF files' : 'список загруженных PDF', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
    <li>GET /api.php/uploads/{id} — <?php echo htmlspecialchars($preferences['language'] === 'en' ? 'PDF metadata' : 'метаданные PDF', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
</ul>
<p><?php echo htmlspecialchars($ui['apiNote'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p><pre>{"city":"Казань","temperature":20.5,"description":"Солнечно"}</pre>
