<link rel="stylesheet" href="<?php echo REPLYBOX_URL; ?>assets/css/replybox-welcome.css">

<div class="replybox-welcome">
    <header class="replybox-welcome-header">
        <div class="replybox-container">
            <div class="replybox-welcome-header__inner">
                <div class="replybox-welcome-header__left">
                    <a href="https://getreplybox.com/?utm_source=ReplyBox&utm_medium=Plugin&utm_campaign=Welcome Page" target="_blank">
                        <?php echo file_get_contents( REPLYBOX_PATH . 'assets/img/logo.svg' ); ?>
                    </a>
                </div>
                <div class="replybox-welcome-header__right">
                    <div class="replybox-welcome-header__right-text">
                        A privacy-focused comment system
                    </div>
                    <a href="https://getreplybox.com/?utm_source=ReplyBox&utm_medium=Plugin&utm_campaign=Welcome Page" target="_blank" class="btn-square">
                        <span>Learn More</span>
                        <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M0 8.50244V6.50244H12L8 2.50244L9 0.502441L16 7.50244L9 14.5024L8 12.5024L12 8.50244H0Z" fill="white"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="replybox-welcome-main">
        <div class="replybox-welcome-container">

            <section class="replybox-block-cta">
                <div class="replybox-container">
                    <div class="replybox-block-cta__inner">
                        <div class="replybox-block-cta__row">
                            <div class="replybox-block-cta__left">
                                <div class="replybox-block-cta__content">
                                    <h2 class="replybox-block-cta__title">
                                        <?php _e( 'Connect your site to ReplyBox', 'replybox' ); ?>
                                    </h2>
                                    <div class="replybox-block-cta__text">
                                        <?php
                                            printf(
                                                __(
                                                    'Connect your site to <a href="%s" target="_blank">ReplyBox</a> to get started. If you already have a ReplyBox account, go to the settings page to add your site ID. Otherwise, head over to the ReplyBox site to learn more about our privacy-focussed commenting system',
                                                    'replybox'
                                                ),
                                                'https://getreplybox.com/?utm_source=ReplyBox&utm_medium=Plugin&utm_campaign=Welcome Page'
                                            );
                                        ?>
                                    </div>
                                    <div class="replybox-block-cta__links">
                                        <a href="/wp-admin/options-general.php?page=replybox" class="btn-square btn-square--brand btn-square--large">
                                            <span>Go to Settings</span>
                                            <svg width="16" height="15" viewBox="0 0 16 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M0 8.50244V6.50244H12L8 2.50244L9 0.502441L16 7.50244L9 14.5024L8 12.5024L12 8.50244H0Z" fill="white"/>
                                            </svg>
                                        </a>
                                        <a href="https://getreplybox.com/?utm_source=ReplyBox&utm_medium=Plugin&utm_campaign=Welcome Page" target="_blank" class="link--underline">
                                            <span>or learn more</span>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="replybox-block-cta__right">
                                <div class="replybox-block-cta__frame { u-anim-intro }">
                                    <?php echo file_get_contents( REPLYBOX_PATH . 'assets/img/browser.svg' ); ?>
                                    <figure class="replybox-block-cta__frame-inset { u-anim-intro }">
                                        <img src="<?php echo REPLYBOX_URL; ?>assets/img/comments.jpg" alt="ReplyBox Embed" />
                                    </figure>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <?php
                $features = array(
                    array(
                        'title' => 'Markdown',
                        'text' => 'Links, lists, quotes, code blocks and even your favorite emojis are all welcome here 💪'
                    ),
                    array(
                        'title' => 'Natural Discussion',
                        'text' => 'We\'ve added nested comments so your users can reply direct to any comment they like and share their bright ideas.'
                    ),
                    array(
                        'title' => 'Always in the Loop',
                        'text' => 'Anyone can subscribe to email notifications in an instant so that a reply or comment is never missed.'
                    ),
                    array(
                        'title' => 'Pin a Comment',
                        'text' => 'Highlight a bright idea or spark of inspiration with the click of a button so you never lose it.'
                    ),
                    array(
                        'title' => 'Social Login',
                        'text' => 'Make commenting even easier by authenticating via Google or Twitter in just two clicks (more coming soon).'
                    ),
                    array(
                        'title' => 'Webhooks',
                        'text' => 'Notify external services that new comments have been added and streamline your workflow.'
                    ),
                    array(
                        'title' => 'Design Options',
                        'text' => 'Our designers are hard at work putting the finishing touches to even more ways to turn on the style.'
                    ),
                    array(
                        'title' => 'Custom CSS',
                        'text' => 'Use CSS to transform ReplyBox into the on-brand addition your business has been waiting for.'
                    ),
                );
            ?>

            <section class="replybox-block-features">
                <div class="replybox-container">
                    <div class="replybox-block-features__grid">

                        <?php foreach( $features as $feature ) : ?>
                        <div class="replybox-block-features__block">

                            <div class="replybox-c-feature">
                                <div class="replybox-c-feature__icon">
                                    <?php echo file_get_contents( REPLYBOX_PATH . 'assets/img/icons/icon-' . sanitize_title_with_dashes( $feature['title'] ) . '.svg' ); ?>
                                </div>
                                <div class="replybox-c-feature__content">
                                    <h3 class="replybox-c-feature__title">
                                        <?php echo $feature['title']; ?>
                                    </h3>
                                    <div class="replybox-c-feature__text">
                                        <?php echo $feature['text']; ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            </section>


        </div>
    </main>
</div>