<?php

declare(strict_types=1);

namespace OCA\PaperlessView\Controller;

use OCA\PaperlessView\AppInfo\Application;
use OCA\PaperlessView\Service\PaperlessService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataDownloadResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\Response;
use OCP\IConfig;
use OCP\IRequest;

class ApiController extends Controller {
    private PaperlessService $paperless;
    private IConfig $config;

    public function __construct(
        IRequest $request,
        PaperlessService $paperless,
        IConfig $config
    ) {
        parent::__construct(Application::APP_ID, $request);
        $this->paperless = $paperless;
        $this->config = $config;
    }

    /**
     * @NoAdminRequired
     */
    public function documents(): JSONResponse {
        if (!$this->paperless->isConfigured()) {
            return new JSONResponse(['error' => 'Paperless not configured'], 503);
        }

        $params = [];
        foreach (['page', 'page_size', 'ordering', 'tags__id__in', 'correspondent__id', 'document_type__id', 'is_tagged'] as $key) {
            $val = $this->request->getParam($key);
            if ($val !== null && $val !== '') {
                $params[$key] = $val;
            }
        }

        try {
            $result = $this->paperless->request('documents/', $params);
            return new JSONResponse(json_decode($result['body'], true));
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 502);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function thumbnail(int $id): Response {
        if (!$this->paperless->isConfigured()) {
            return new JSONResponse(['error' => 'Paperless not configured'], 503);
        }

        try {
            $result = $this->paperless->requestBinary("documents/{$id}/thumb/");
            $response = new DataDownloadResponse($result['body'], "thumb-{$id}", $result['contentType']);
            $response->addHeader('Content-Disposition', 'inline');
            $response->addHeader('Cache-Control', 'public, max-age=3600');
            return $response;
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 502);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function preview(int $id): Response {
        if (!$this->paperless->isConfigured()) {
            return new JSONResponse(['error' => 'Paperless not configured'], 503);
        }

        try {
            $result = $this->paperless->requestBinary("documents/{$id}/preview/");
            $response = new DataDownloadResponse($result['body'], "preview-{$id}.pdf", $result['contentType']);
            $response->addHeader('Content-Disposition', 'inline');
            return $response;
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 502);
        }
    }

    /**
     * @NoAdminRequired
     * @NoCSRFRequired
     */
    public function download(int $id): Response {
        if (!$this->paperless->isConfigured()) {
            return new JSONResponse(['error' => 'Paperless not configured'], 503);
        }

        try {
            $result = $this->paperless->requestBinary("documents/{$id}/download/");
            $response = new DataDownloadResponse($result['body'], "document-{$id}.pdf", $result['contentType']);
            $response->addHeader('Content-Disposition', 'attachment');
            return $response;
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 502);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function tags(): JSONResponse {
        if (!$this->paperless->isConfigured()) {
            return new JSONResponse(['error' => 'Paperless not configured'], 503);
        }

        try {
            $result = $this->paperless->request('tags/', ['page_size' => '1000']);
            return new JSONResponse(json_decode($result['body'], true));
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 502);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function correspondents(): JSONResponse {
        if (!$this->paperless->isConfigured()) {
            return new JSONResponse(['error' => 'Paperless not configured'], 503);
        }

        try {
            $result = $this->paperless->request('correspondents/', ['page_size' => '1000']);
            return new JSONResponse(json_decode($result['body'], true));
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 502);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function documentTypes(): JSONResponse {
        if (!$this->paperless->isConfigured()) {
            return new JSONResponse(['error' => 'Paperless not configured'], 503);
        }

        try {
            $result = $this->paperless->request('document_types/', ['page_size' => '1000']);
            return new JSONResponse(json_decode($result['body'], true));
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 502);
        }
    }

    /**
     * @NoAdminRequired
     */
    public function search(): JSONResponse {
        if (!$this->paperless->isConfigured()) {
            return new JSONResponse(['error' => 'Paperless not configured'], 503);
        }

        $query = $this->request->getParam('query', '');
        $page = $this->request->getParam('page', '1');

        if (empty($query)) {
            return new JSONResponse(['error' => 'Query parameter required'], 400);
        }

        try {
            $result = $this->paperless->request('documents/', [
                'query' => $query,
                'page' => $page,
                'page_size' => '24',
            ]);
            return new JSONResponse(json_decode($result['body'], true));
        } catch (\Exception $e) {
            return new JSONResponse(['error' => $e->getMessage()], 502);
        }
    }

    /**
     * Get admin settings (admin only).
     */
    public function getSettings(): JSONResponse {
        $url = $this->config->getAppValue(Application::APP_ID, 'paperless_url', '');
        $token = $this->config->getAppValue(Application::APP_ID, 'paperless_token', '');

        return new JSONResponse([
            'paperless_url' => $url,
            'paperless_token' => $token,
        ]);
    }

    /**
     * Save admin settings (admin only).
     */
    public function saveSettings(): JSONResponse {
        $url = $this->request->getParam('paperless_url', '');
        $token = $this->request->getParam('paperless_token', '');

        $this->config->setAppValue(Application::APP_ID, 'paperless_url', $url);
        $this->config->setAppValue(Application::APP_ID, 'paperless_token', $token);

        return new JSONResponse(['status' => 'ok']);
    }
}
