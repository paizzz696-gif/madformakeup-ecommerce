<?php $errors = $errors ?? session()->getFlashdata('errors') ?? []; ?>
<?php if (! empty($errors)): ?>
    <div class="alert alert-danger py-2">
        <ul class="mb-0 small">
            <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>