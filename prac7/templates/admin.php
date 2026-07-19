<?php
/** @var array $preferences */
/** @var array $ui */
/** @var string $now */
?>
<h1><?php echo htmlspecialchars($ui['adminTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1>
<p><?php echo htmlspecialchars($ui['adminWelcome'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> <?php echo htmlspecialchars($preferences['login'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
<p><?php echo htmlspecialchars($ui['adminStatus'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> <?php echo htmlspecialchars($now, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
<p><?php echo htmlspecialchars($ui['adminUser'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> <?php echo htmlspecialchars($preferences['login'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
<p><?php echo htmlspecialchars($ui['adminTheme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> <?php echo htmlspecialchars($preferences['theme'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
