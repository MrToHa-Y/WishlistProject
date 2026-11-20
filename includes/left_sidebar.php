<aside class="home-sidebar home-sidebar--left">
    <nav class="home-sidebar__nav">
        <?php foreach ($leftSidebarSections as $section): ?>
            <?php if (!empty($section['is_divider'])): ?>
                <div class="home-sidebar__divider" aria-hidden="true"></div>
                <?php continue; ?>
            <?php endif; ?>

            <?php
                $sectionLinkClasses = ['home-sidebar__link'];
                $sectionHref = (string) ($section['href'] ?? '/pages/error.php');
                $isCurrentSection = !empty($section['is_active']);

                if (!empty($section['is_title']) && !isset($section['href'])){
                    $sectionLinkClasses[] = 'home-sidebar__link--title';
                    $sectionHref = ROUTE_HOME;
                    $isCurrentSection = true;
                }

                if ($isCurrentSection){
                    $sectionLinkClasses[] = 'home-sidebar__link--active';
                }

                if (($section['icon'] ?? '') === 'first_april_icon.svg'){
                    $sectionLinkClasses[] = 'home-sidebar__link--fixed-april-icon';
                }

                $openCreateWish = !empty($section['open_create_wish']);
                $sectionIcon = (string) ($section['icon'] ?? 'for_you_icon.svg');
                $sectionTitle = (string) ($section['title'] ?? '');
            ?>
            
            <a class="<?= e(implode(' ', $sectionLinkClasses)) ?>" href="<?= e($sectionHref) ?>" <?= $openCreateWish ? 'data-create-wish-open' : '' ?> <?= $isCurrentSection ? 'aria-current="page"' : '' ?> >
                <span class="home-sidebar__icon" aria-hidden="true"><?= $renderInlineIcon($sectionIcon) ?></span>
                <span><?= e($sectionTitle) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="home-sidebar__apps">
        <?php foreach ($leftSidebarApps as $app): ?>
            <?php
                $appLinkClasses = ['home-sidebar__link'];
                if (($app['icon'] ?? '') === 'for_business_icon.svg'){
                    $appLinkClasses[] = 'home-sidebar__link--fixed-business-icon';
                }

                $appHref = (string) ($app['href'] ?? '/pages/error.php');
                $appIcon = (string) ($app['icon'] ?? 'for_business_icon.svg');
                $appTitle = (string) ($app['title'] ?? '');
            ?>
            <a class="<?= e(implode(' ', $appLinkClasses)) ?>" href="<?= e($appHref) ?>">
                <span class="home-sidebar__icon" aria-hidden="true"><?= $renderInlineIcon($appIcon) ?></span>
                <span><?= e($appTitle) ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="home-sidebar__legal">
        <p>&copy; Ohmywishes, LLC</p>
        <a href="/pages/error.php">Privacy policy</a>
        <a href="/pages/error.php">User agreement</a>
    </div>
</aside>