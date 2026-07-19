<?php
/** @var array $preferences */
/** @var array $ui */
/** @var string $message */
/** @var array $pdfFiles */
?>
<h1><?php echo htmlspecialchars($ui['uploadTitle'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h1>
<p><?php echo htmlspecialchars($ui['uploadHint'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>

<?php if ($message !== ''): ?>
    <p><?php echo htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="upload">
    <p>
        <label><?php echo htmlspecialchars($ui['fileLabel'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
            <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf,.pdf" required style="display:none" onchange="document.getElementById('file_name').textContent = this.value ? this.value.split('\\').pop() : '<?php echo htmlspecialchars($ui['noFileChosen'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>'">
            <button type="button" onclick="document.getElementById('pdf_file').click()"><?php echo htmlspecialchars($ui['chooseFile'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></button>
            <span id="file_name"><?php echo htmlspecialchars($ui['noFileChosen'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></span>
        </label>
    </p>
    <p><button type="submit"><?php echo htmlspecialchars($ui['uploadButton'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></button></p>
</form>

<h2><?php echo htmlspecialchars($ui['availableFiles'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></h2>
<ul>
    <?php foreach ($pdfFiles as $file): ?>
        <li>
            <?php echo htmlspecialchars($file->name, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?>
            (<a href="/download.php?file=<?php echo rawurlencode($file->name); ?>"><?php echo htmlspecialchars($ui['downloadButton'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></a>)
        </li>
    <?php endforeach; ?>
    <?php if ($pdfFiles === []): ?>
        <li><?php echo htmlspecialchars($ui['uploadEmpty'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); ?></li>
    <?php endif; ?>
</ul>
