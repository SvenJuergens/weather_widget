<?php
declare(strict_types = 1);

namespace SvenJuergens\WeatherWidget\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\Exception\RouteNotFoundException;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Http\RedirectResponse;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class LocationController
{

    /**
     * @var string
     */
    private $backendUserLocation = '';

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws RouteNotFoundException
     */
    public function addLocationAction(ServerRequestInterface $request): ResponseInterface
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $location = $request->getQueryParams()['location'] ?? null;
        if ($location) {
            $this->backendUserLocation = htmlspecialchars($location);
            $this->saveBackendUserLocation();
        }

        return new RedirectResponse($uriBuilder->buildUriFromRoute('dashboard', ['action' => 'main']));
    }

    protected function saveBackendUserLocation(): void
    {
        $backendUser = $this->getBackendUserAuthentication();

        if (!$backendUser instanceof BackendUserAuthentication) {
            return;
        }

        $backendUserId = (int)$backendUser->user['uid'];
        $backendUserRecord = BackendUtility::getRecord('be_users', $backendUserId);

        if (is_array($backendUserRecord) && isset($backendUserRecord['uc'])) {
            $uc = unserialize($backendUserRecord['uc'], ['allowed_classes' => [\stdClass::class]]);
            if (is_array($uc)) {
                $uc['BackendComponents']['States']['DashboardWeatherWidget']['location'] = $this->backendUserLocation;
                $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('be_users');
                $connection->update('be_users', ['uc' => serialize($uc)], ['uid' => $backendUserId]);
            }
        }
    }

    protected function getBackendUserAuthentication(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
