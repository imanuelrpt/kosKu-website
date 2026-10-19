<?php

use CodeIgniter\Pager\PagerRenderer;

/**
 * Template pagination KosKu (Bootstrap 5).
 *
 * @var PagerRenderer $pager
 * @var string        $pagerGroup
 */
$pager->setSurroundCount(1);
?>
<?php if ($pager->getPageCount() > 1): ?>
    <nav aria-label="Navigasi halaman">
        <ul class="pagination pagination-sm mb-0">
            <li class="page-item <?= $pager->hasPrevious() ? '' : 'disabled' ?>">
                <a class="page-link" href="<?= $pager->hasPrevious() ? $pager->getPrevious() : '#' ?>" aria-label="Halaman sebelumnya">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>

            <?php foreach ($pager->links() as $link): ?>
                <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                    <a class="page-link" href="<?= $link['uri'] ?>"><?= $link['title'] ?></a>
                </li>
            <?php endforeach ?>

            <li class="page-item <?= $pager->hasNext() ? '' : 'disabled' ?>">
                <a class="page-link" href="<?= $pager->hasNext() ? $pager->getNext() : '#' ?>" aria-label="Halaman berikutnya">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
<?php endif ?>
