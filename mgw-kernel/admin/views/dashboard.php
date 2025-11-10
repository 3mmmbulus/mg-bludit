<div id="dashboard" class="container">
    <div class="row">
        <div class="col-md-7">

            <!-- Good message -->
            <div>
                <h2 id="hello-message" class="pt-0"
                    data-username="<?php echo $name ?>"
                    data-morning="<?php echo $L->g('good-morning') ?>"
                    data-afternoon="<?php echo $L->g('good-afternoon') ?>"
                    data-evening="<?php echo $L->g('good-evening') ?>"
                    data-night="<?php echo $L->g('good-night') ?>">
                    <?php
                    $username = $login->username();
                    $user = new User($username);
                    $name = '';
                    if ($user->nickname()) {
                        $name = $user->nickname();
                    } elseif ($user->firstName()) {
                        $name = $user->firstName();
                    }
                    ?>
                    <span class="bi bi-hand-thumbs-up"></span><span><?php echo $L->g('welcome') ?></span>
                </h2>
            </div>

            <!-- Quick Links -->
            <div class="container pb-5" id="jsclippyContainer">

                <div class="row">
                    <div class="col p-0">
                        <div class="mb-3">
                            <select id="jsclippy" class="clippy" name="state"
                                    data-placeholder="<?php $L->p('Start typing to see a list of suggestions') ?>"
                                    data-view-label="<?php $L->p('view') ?>"
                                    data-edit-label="<?php $L->p('edit') ?>"></select>
                        </div>
                    </div>
                </div>

            </div>

            <?php Theme::plugins('dashboard') ?>
        </div>
        <div class="col-md-5">

            <!-- Notifications -->
            <ul class="list-group list-group-striped b-0">
                <li class="list-group-item pt-0">
                    <h4 class="m-0"><?php $L->p('Notifications') ?></h4>
                </li>
                <?php
                $logs = array_slice($syslog->db, 0, NOTIFICATIONS_AMOUNT);
                foreach ($logs as $log) {
                    $phrase = $L->g($log['dictionaryKey']);
                    echo '<li class="list-group-item">';
                    echo $phrase;
                    if (!empty($log['notes'])) {
                        echo ' « <b>' . $log['notes'] . '</b> »';
                    }
                    echo '<br><span class="notification-date"><small>';
                    echo Date::format($log['date'], DB_DATE_FORMAT, NOTIFICATIONS_DATE_FORMAT);
                    echo ' [ ' . $log['username'] . ' ]';
                    echo '</small></span>';
                    echo '</li>';
                }
                ?>
            </ul>

        </div>
    </div>
</div>

<?php echo '<script src="'.DOMAIN_CORE_JS.'dashboard.js?version='.MAIGEWAN_VERSION.'"></script>'; ?>
