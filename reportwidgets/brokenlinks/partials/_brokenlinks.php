<div class="report-widget">
    <h3><?= Lang::get('bombozama.linkcheck::lang.reportwidget.title'); ?></h3>
    <?php if (!isset($error)): ?>
        <div class="control-status-list">
            <ul>
                <li>
                    <?= Lang::get('bombozama.linkcheck::lang.reportwidget.last_check'); ?> <?= $vars['last_check'] ?>
                </li>
                <li>
                    <span class="status-text"><strong><?= Lang::get('bombozama.linkcheck::lang.reportwidget.categories.status'); ?></strong></span>
                    <span class="status-label link"><strong><?= Lang::get('bombozama.linkcheck::lang.reportwidget.categories.broken_links'); ?></strong></span>
                </li>
                <?php 
                    krsort($vars['grouped']);
                    foreach ($vars['grouped'] as $status_code => $links) {
                        echo '<li>';
                        echo '<span class="status-text">'.$status_code.'</span>';
                        echo '<span class="status-label link">'.sizeof($links).'</span>';
                        echo '</li>';
                    }
                ?>
                <li>
                    <span class="status-text">
                       <strong><?= Lang::get('bombozama.linkcheck::lang.reportwidget.categories.total'); ?></strong>
                    </span>
                    <span class="status-label link">
                    <strong><?= $vars['total'] ?></strong>
                    </span>
                </li>
                <li>
                    <div class="mb-2">
                    <?= Lang::get('bombozama.linkcheck::lang.reportwidget.status_info'); ?>
                    </div>
                    <div>
                        <a
                            role="button"
                            class="btn btn-default"
                            href="/zb_backend/bombozama/linkcheck/context">
                            <?= Lang::get('bombozama.linkcheck::lang.reportwidget.button.label'); ?>
                        </a>
                    </div>
                </li>
            </ul>
        </div>
    <?php else: ?>
        <p class="flash-message static warning"><?= e($error) ?></p>
    <?php endif ?>
</div>
