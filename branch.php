<?php
function current_branch(): string {
    $headFile = __DIR__ . '/.git/HEAD';
    if (is_readable($headFile)) {
        $head = trim(file_get_contents($headFile));
        if (strpos($head, 'ref:') === 0) {
            return basename($head);
        }
        return $head ?: 'unknown';
    }
    return 'unknown';
}
$branch = htmlspecialchars(current_branch(), ENT_QUOTES, 'UTF-8');
?>
<div id="branch-info">Branch: <?php echo $branch; ?></div>
<style>
#branch-info {
    position: fixed;
    bottom: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.6);
    color: #fff;
    padding: 4px 8px;
    font-size: 12px;
    font-family: sans-serif;
    z-index: 1000;
}
</style>
