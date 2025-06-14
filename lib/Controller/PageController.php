<?php

declare(strict_types=1);

namespace OCA\PhotoFrames\Controller;

use Exception;
use OCA\PhotoFrames\AppInfo\Application;
use OCA\PhotoFrames\Db\EntryMapper;
use OCA\PhotoFrames\Db\Frame;
use OCA\PhotoFrames\Db\FrameMapper;
use OCA\PhotoFrames\Service\PhotoFrameService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\ContentSecurityPolicy;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\Response;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\Common\Exception\NotFoundException;
use OCP\Files\IRootFolder;
use OCP\IConfig;
use OCP\IDBConnection;
use OCP\IRequest;
use OCP\IPreview;
use OCP\IUser;
use OCP\IUserSession;
use OCP\IURLGenerator;
use OCP\Security\Bruteforce\IThrottler;
use OCP\Util;
use OCP\App\IAppManager;

/**
 * @psalm-suppress UnusedClass
 */
class PageController extends Controller
{
  private const BRUTEFORCE_ACTION = 'photo_frames';
  private EntryMapper $entryMapper;
  private FrameMapper $frameMapper;
  private IThrottler $throttler;
  private IRootFolder $rootFolder;
  private IPreview $preview;
  private IConfig $config;
  private ?IUser $currentUser;
  private IURLGenerator $urlGenerator;
  private IDBConnection $db;
  private IAppManager $appManager;

  private $testedPhotosVersions = [3, 4, 5];

  public function __construct(
    $appName,
    IRequest $request,
    EntryMapper $entryMapper,
    FrameMapper $frameMapper,
    IThrottler $throttler,
    IRootFolder $rootFolder,
    IPreview $preview,
    IConfig $config,
    IUserSession $userSession,
    IURLGenerator $urlGenerator,
    IDBConnection $db,
    IAppManager $appManager,
  ) {
    parent::__construct($appName, $request);
    $this->entryMapper = $entryMapper;
    $this->frameMapper = $frameMapper;
    $this->throttler = $throttler;
    $this->rootFolder = $rootFolder;
    $this->preview = $preview;
    $this->config = $config;
    $this->currentUser = $userSession->getUser();
    $this->urlGenerator = $urlGenerator;
    $this->db = $db;
    $this->appManager = $appManager;
  }

  #[NoCSRFRequired]
  #[NoAdminRequired]
  #[OpenAPI(OpenAPI::SCOPE_IGNORE)]
  #[FrontpageRoute(verb: 'GET', url: '/')]
  public function index(): Response
  {
    Util::addStyle(Application::APP_ID, 'main');

    if (!$this->photosIsInstalled()) {
      return $this->renderPage('ErrorPage', [
        "message" => "Photo Frames cannot function without the Photos app.\nPlease activate the Photos app and try again."
      ]);
    }

    Util::addScript(Application::APP_ID, 'vendor/qrcode.min');

    try {
      $uid = $this->currentUser->getUID();
      return $this->renderPage('IndexPage', [
        'frames' => $this->frameMapper->getAllByUser($uid)
      ]);
    } catch (Exception $error) {
      return $this->errorPage($error);
    }
  }

  #[NoCSRFRequired]
  #[NoAdminRequired]
  #[OpenAPI(OpenAPI::SCOPE_IGNORE)]
  #[FrontpageRoute(verb: 'GET', url: '/new')]
  public function new(): Response
  {
    try {
      $uid = $this->currentUser->getUID();
      Util::addStyle(Application::APP_ID, 'main');

      return $this->renderPage('NewPage', [
        'frame' => new Frame(),
        'albums' => $this->frameMapper->getAvailableAlbums($uid),
        'requestToken' => Util::callRegister()
      ]);
    } catch (Exception $error) {
      return $this->errorPage($error);
    }
  }

  #[NoAdminRequired]
  #[OpenAPI(OpenAPI::SCOPE_IGNORE)]
  #[FrontpageRoute(verb: 'POST', url: '/')]
  public function create(): Response
  {
    try {
      $params = $this->request->getParams();
      $this->frameMapper->createFrame(
        $params['name'],
        $this->currentUser->getUID(),
        $this->frameMapper->validAlbumForUser($this->currentUser->getUID(), (int) $params['albumId']),
        $params['selectionMethod'],
        $params['rotationUnit'],
        (int) $params['rotationsPerUnit'],
        $params['startDayAt'],
        $params['endDayAt'],
        (bool) $params['showPhotoTimestamp'],
        $params['photoSize'],
      );

      return new RedirectResponse(redirectURL: $this->urlGenerator->linkToRoute('photo_frames.page.index'));
    } catch (Exception $error) {
      return $this->errorPage($error);
    }
  }

  #[NoCSRFRequired]
  #[NoAdminRequired]
  #[OpenAPI(OpenAPI::SCOPE_IGNORE)]
  #[FrontpageRoute(verb: 'GET', url: '/{id}/edit', requirements: ['id' => '[0-9]+'])]
  public function edit($id): Response
  {
    try {
      $uid = $this->currentUser->getUID();

      Util::addStyle(Application::APP_ID, 'main');

      return $this->renderPage('EditPage', [
        'frame' => $this->frameMapper->getByUserIdAndFrameId($uid, (int) $id),
        'albums' => $this->frameMapper->getAvailableAlbums($uid),
        'requestToken' => Util::callRegister()
      ]);
    } catch (Exception $error) {
      return $this->errorPage($error);
    }
  }

  #[NoAdminRequired]
  #[OpenAPI(OpenAPI::SCOPE_IGNORE)]
  #[FrontpageRoute(verb: 'POST', url: '/{id}', requirements: ['id' => '[0-9]+'])]
  public function update($id): Response
  {
    try {
      $uid = $this->currentUser->getUID();
      $frame = $this->frameMapper->getByUserIdAndFrameId($uid, (int) $id);
      $params = $this->request->getParams();

      $this->frameMapper->updateFrame(
        $frame,
        $params['name'],
        $this->currentUser->getUID(),
        $this->frameMapper->validAlbumForUser($this->currentUser->getUID(), (int) $params['albumId']),
        $params['selectionMethod'],
        $params['rotationUnit'],
        (int) $params['rotationsPerUnit'],
        $params['startDayAt'],
        $params['endDayAt'],
        (bool) $params['showPhotoTimestamp'],
        $params['photoSize'],
      );

      return new RedirectResponse($this->urlGenerator->linkToRoute('photo_frames.page.index'));
    } catch (Exception $error) {
      return $this->errorPage($error);
    }
  }

  #[NoCSRFRequired]
  #[NoAdminRequired]
  #[OpenAPI(OpenAPI::SCOPE_IGNORE)]
  #[FrontpageRoute(verb: 'DELETE', url: '/{id}', requirements: ['id' => '[0-9]+'])]
  public function destroy($id): Response
  {
    try {
      $uid = $this->currentUser->getUID();
      $frame = $this->frameMapper->getByUserIdAndFrameId($uid, (int) $id);

      $this->frameMapper->destroyFrame($frame);

      return new Response(204);
    } catch (Exception $error) {
      return $this->errorPage($error);
    }
  }

  #[NoCSRFRequired]
  #[PublicPage]
  #[OpenAPI(OpenAPI::SCOPE_IGNORE)]
  #[FrontpageRoute(verb: 'GET', url: '/{shareToken}', requirements: ['shareToken' => '[a-zA-Z0-9]{64}'])]
  public function photoframe($shareToken): Response
  {
    $frame = $this->frameMapper->getByShareToken($shareToken);
    if (!$frame) {
      $this->throttler->registerAttempt(self::BRUTEFORCE_ACTION, $this->request->getRemoteAddress());
      throw new NotFoundException('Unable to find album');
    }

    try {
      return $this->renderPage(
        'FramePage',
        [
          'showPhotoTimestamp' => $frame->getShowPhotoTimestamp(),
          'photoSize' => $frame->getPhotoSize(),
        ],
        true
      );
    } catch (Exception $error) {
      return $this->errorPage($error);
    }
  }

  #[NoCSRFRequired]
  #[PublicPage]
  #[OpenAPI(OpenAPI::SCOPE_IGNORE)]
  #[FrontpageRoute(verb: 'GET', url: '/{shareToken}/image', requirements: ['shareToken' => '[a-zA-Z0-9]+'])]
  public function photoframeImage($shareToken): Response
  {
    $frame = $this->frameMapper->getByShareToken($shareToken);
    if (!$frame) {
      $this->throttler->registerAttempt(self::BRUTEFORCE_ACTION, $this->request->getRemoteAddress());
      throw new NotFoundException('Unable to find album');
    }

    try {
      $service = new PhotoFrameService($this->entryMapper, $this->frameMapper, $this->rootFolder, $frame);
      $frameFile = $service->getCurrentFrameFile();
      $node = $service->getFrameFileNode($frameFile);

      $preview = $this->preview->getPreview($node, 1000, 1000);

      $headers = [
        'X-Photo-Timestamp' => $frameFile->getCapturedAtTimestamp(),
        'X-Frame-Rotation-Unit' => $frame->getRotationUnit(),
        'Expires' => $frameFile->getExpiresHeader(),
        'Content-Type' => $frameFile->getMimeType(),
      ];
      return new FileDisplayResponse($preview, 200, $headers);
    } catch (Exception $error) {
      return $this->errorPage($error);
    }
  }

  private function renderPage($name, $props, $blank = false): Response
  {
    $response = new TemplateResponse(
      appName: Application::APP_ID,
      templateName: $blank ? "blank" : 'page',
      params: [
        'pageName' => $name,
        'pageProps' => $props,
        "appPath" => $this->appManager->getAppWebPath('photo_frames'),
      ],
      renderAs: $blank ? TemplateResponse::RENDER_AS_BLANK : TemplateResponse::RENDER_AS_USER,
    );

    $csp = new ContentSecurityPolicy();
    $csp->addAllowedFrameDomain($this->request->getServerHost());
    $response->setContentSecurityPolicy($csp);

    return $response;
  }

  private function errorPage($error): Response
  {
    Util::addStyle(Application::APP_ID, 'main');

    $testedVersionsString = join(', ', $this->testedPhotosVersions);
    $photosVersion = $this->getPhotosVersion();
    $message = $this->isTestedPhotosVersion()
      ? "Something went wrong. Please try disabling and reenabling the Photo Frames app."
      : "You are using an unsupported version of the Photos app ($photosVersion), supported versions are: $testedVersionsString";


    $debugInfo = [
      ["**Nextcloud version**", implode('.', Util::getVersion())],
      ["**Photo Frames version**", $this->appManager->getAppVersion("photo_frames")],
      ["**Photos version**", $this->appManager->getAppVersion("photos")],
      ["**Database**", $this->db->getDatabaseProvider()],
      ["**Error Message**", $error->getMessage()],
      ["**File:line**", '`' . $error->getFile() . ":" . $error->getLine() . '`'],
      ["**Stack trace**", "```txt\n" . $error->getTraceAsString() . "\n```"],
    ];
    $debugInfoString = implode("\n\n", array_map(function ($value) {
      return implode("\n", $value);
    }, $debugInfo));

    $issueBody = "## What happened\n\n[Describe what you did to trigger the error]\n\n## Debug information\n\n" . $debugInfoString;
    $issueTitle = $error->getMessage();
    $reportLink = "https://github.com/jeppester/nextcloud-photo-frames/issues/new?title=" . urlencode($issueTitle) . "&body=" . urlencode($issueBody);

    $response = $this->renderPage('ErrorPage', [
      'message' => $message,
      'reportLink' => $reportLink,
    ]);
    $response->setStatus(500);
    return $response;
  }

  private function photosIsInstalled()
  {
    return $this->appManager->isInstalled('photos');
  }

  private function getPhotosVersion()
  {
    return (int) $this->appManager->getAppVersion('photos')[0];
  }

  private function isTestedPhotosVersion()
  {
    return in_array($this->getPhotosVersion(), $this->testedPhotosVersions);
  }
}
