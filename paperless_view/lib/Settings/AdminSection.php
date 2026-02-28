<?php

declare(strict_types=1);

namespace OCA\PaperlessView\Settings;

use OCA\PaperlessView\AppInfo\Application;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\Settings\IIconSection;

class AdminSection implements IIconSection {
    private IURLGenerator $urlGenerator;

    public function __construct(IURLGenerator $urlGenerator) {
        $this->urlGenerator = $urlGenerator;
    }

    public function getID(): string {
        return 'paperless_view';
    }

    public function getName(): string {
        return 'Paperless View';
    }

    public function getPriority(): int {
        return 90;
    }

    public function getIcon(): string {
        return $this->urlGenerator->imagePath(Application::APP_ID, 'app.svg');
    }
}
