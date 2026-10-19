<?php foreach ($teamMembers as $member): ?>

<div class="col-md-6 col-lg-3">

    <div class="agent-card">

        <?php if (!empty($member['photo'])): ?>

            <img
                src="<?= e($member['photo']) ?>"
                alt="<?= e($member['name']) ?>"
            >

        <?php endif; ?>

        <h3>
            <?= e($member['name']) ?>
        </h3>

        <p>
            <?= e($member['position']) ?>
        </p>

        <?php if (!empty($member['biography'])): ?>

            <p>
                <?= nl2br(e($member['biography'])) ?>
            </p>

        <?php endif; ?>

    </div>

</div>

<?php endforeach; ?>