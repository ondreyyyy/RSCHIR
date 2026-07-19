<?php
/** @var array $preferences */
/** @var array $ui */
/** @var array $rows */
/** @var array $chartUrls */
?>
<h1><?php echo htmlspecialchars($ui['statsTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1>
<p><?php echo htmlspecialchars($ui['statsTotal'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?> <?php echo count($rows); ?></p>

<?php if (!empty($chartUrls['bar'])): ?>
    <h2><?php echo htmlspecialchars($ui['statsBarTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2>
    <img src="<?php echo htmlspecialchars($chartUrls['bar'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" alt="Bar chart">
<?php endif; ?>

<?php if (!empty($chartUrls['line'])): ?>
    <h2><?php echo htmlspecialchars($ui['statsLineTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2>
    <img src="<?php echo htmlspecialchars($chartUrls['line'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" alt="Line chart">
<?php endif; ?>

<?php if (!empty($chartUrls['pie'])): ?>
    <h2><?php echo htmlspecialchars($ui['statsPieTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2>
    <img src="<?php echo htmlspecialchars($chartUrls['pie'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>" alt="Pie chart">
<?php endif; ?>
