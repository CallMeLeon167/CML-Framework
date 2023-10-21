<?php 
function formatDate($dateString) {
    $timestamp = strtotime($dateString);
    return date("d.m.Y", $timestamp);
}

$createdate = formatDate($created_at);
$last_update = formatDate($pushed_at);

?>

<main>
    <span id="last-update">Last update: <?=$last_update?></span>
    <h2>Thank you for Downloading the <a id="cml-text" href="https://github.com/CallMeLeon167/CML-Framework" target="_blank">CML-Framework</a> <?=$version?></h2>
    <h3>A small project that started on <?=$createdate?></h3>
    <a id="docs-button" href="https://docs.callmeleon.de" target="_blank">Documentation</a>
    <span id="thanks">A special thanks to all contributors:</span>
    <div id="info">
        <?php foreach ($contributors as $value): ?>
            <div id="contributors">
                <img src="<?= $value['avatar_url'] ?>" alt="<?= $value['login'] ?>">
                <a class="name" href="https://github.com/<?= $value['login'] ?>"><?= $value['login'] ?></a>
            </div>
        <?php endforeach ?>
    </div>
</main>