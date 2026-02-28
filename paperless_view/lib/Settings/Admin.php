<?php

declare(strict_types=1);

namespace OCA\PaperlessView\Settings;

use OCA\PaperlessView\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\Settings\ISettings;
use OCP\Util;

class Admin implements ISettings {
    private IConfig $config;

    public function __construct(IConfig $config) {
        $this->config = $config;
    }

    public function getForm(): TemplateResponse {
        Util::addScript(Application::APP_ID, 'paperless_view-admin');
        $params = [
            'paperless_url' => $this->config->getAppValue(Application::APP_ID, 'paperless_url', ''),
            'paperless_token' => $this->config->getAppValue(Application::APP_ID, 'paperless_token', ''),
        ];
        return new TemplateResponse(Application::APP_ID, 'admin', $params);
    }

    public function getSection(): string {
        return 'paperless_view';
    }

    public function getPriority(): int {
        return 10;
    }
}
