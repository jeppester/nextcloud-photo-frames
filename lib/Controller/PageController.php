<?php

declare(strict_types=1);

namespace OCA\PhotoFrames\Controller;

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
  }

  #[NoCSRFRequired]
  #[NoAdminRequired]
  #[OpenAPI(OpenAPI::SCOPE_IGNORE)]
  #[FrontpageRoute(verb: 'GET', url: '/')]
  public function index(): TemplateResponse
  {
    Util::addStyle(Application::APP_ID, 'main');

    if (!$this->db->tableExists('photos_albums')) {
      return new TemplateResponse(
        appName: Application::APP_ID,
        templateName: 'photos_not_installed',
        renderAs: TemplateResponse::RENDER_AS_USER,
      );
    }

    Util::addScript(Application::APP_ID, 'qrcode.min');

    $uid = $this->currentUser->getUID();
    $params = [
      'frames' => $this->frameMapper->getAllByUser($uid),
      'urlGenerator' => $this->urlGenerator,
    ];

    return new TemplateResponse(
      appName: Application::APP_ID,
      templateName: 'index',
      renderAs: TemplateResponse::RENDER_AS_USER,
      params: $params,
    );
  }

  #[NoCSRFRequired]
  #[NoAdminRequired]
  #[OpenAPI(OpenAPI::SCOPE_IGNORE)]
  #[FrontpageRoute(verb: 'GET', url: '/new')]
  public function new(): TemplateResponse
  {
    $uid = $this->currentUser->getUID();

    $params = [
      'frame' => new Frame(),
      'albums' => $this->frameMapper->getAvailableAlbums($uid),
      'urlGenerator' => $this->urlGenerator,
    ];

    Util::addStyle(Application::APP_ID, 'main');

    return new TemplateResponse(
      appName: Application::APP_ID,
      templateName: 'new',
      renderAs: TemplateResponse::RENDER_AS_USER,
      params: $params,
    );
  }

  #[NoAdminRequired]
  #[OpenAPI(OpenAPI::SCOPE_IGNORE)]
  #[FrontpageRoute(verb: 'POST', url: '/')]
  public function create(): RedirectResponse
  {
    $params = $this->request->getParams();
    $this->frameMapper->createFrame(
      $params['name'],
      $this->currentUser->getUID(),
      $this->frameMapper->validAlbumForUser($this->currentUser->getUID(), (int) $params['album_id']),
      $params['selection_method'],
      $params['entry_lifetime'],
      $params['start_day_at'],
      $params['end_day_at'],
      (bool) $params['show_photo_timestamp'],
    );

    return new RedirectResponse('/index.php/apps/photoframe');
  }

  #[NoCSRFRequired]
  #[NoAdminRequired]
  #[OpenAPI(OpenAPI::SCOPE_IGNORE)]
  #[FrontpageRoute(verb: 'GET', url: '/{id}/edit', requirements: ['id' => '[0-9]+'])]
  public function edit($id): TemplateResponse
  {
    $uid = $this->currentUser->getUID();

    $params = [
      'frame' => $this->frameMapper->getByUserIdAndFrameId($uid, (int) $id),
      'albums' => $this->frameMapper->getAvailableAlbums($uid),
      'urlGenerator' => $this->urlGenerator,
    ];

    Util::addStyle(Application::APP_ID, 'main');

    return new TemplateResponse(
      appName: Application::APP_ID,
      templateName: 'edit',
      renderAs: TemplateResponse::RENDER_AS_USER,
      params: $params,
    );
  }

  #[NoAdminRequired]
  #[OpenAPI(OpenAPI::SCOPE_IGNORE)]
  #[FrontpageRoute(verb: 'POST', url: '/{id}', requirements: ['id' => '[0-9]+'])]
  public function update($id): Response
  {
    $uid = $this->currentUser->getUID();
    $frame = $this->frameMapper->getByUserIdAndFrameId($uid, (int) $id);
    $params = $this->request->getParams();

    $this->frameMapper->updateFrame(
      $frame,
      $params['name'],
      $this->currentUser->getUID(),
      $this->frameMapper->validAlbumForUser($this->currentUser->getUID(), (int) $params['album_id']),
      $params['selection_method'],
      $params['entry_lifetime'],
      $params['start_day_at'],
      $params['end_day_at'],
      (bool) $params['show_photo_timestamp'],
    );

    return new RedirectResponse('/index.php/apps/photoframe');
  }

  #[NoCSRFRequired]
  #[NoAdminRequired]
  #[OpenAPI(OpenAPI::SCOPE_IGNORE)]
  #[FrontpageRoute(verb: 'DELETE', url: '/{id}', requirements: ['id' => '[0-9]+'])]
  public function destroy($id): Response
  {
    $uid = $this->currentUser->getUID();
    $frame = $this->frameMapper->getByUserIdAndFrameId($uid, (int) $id);

    $this->frameMapper->destroyFrame($frame);

    return new Response(204);
  }

  #[NoCSRFRequired]
  #[PublicPage]
  #[OpenAPI(OpenAPI::SCOPE_IGNORE)]
  #[FrontpageRoute(verb: 'GET', url: '/{shareToken}', requirements: ['shareToken' => '[a-zA-Z0-9]{64}'])]
  public function photoframe($shareToken): TemplateResponse
  {
    $frame = $this->frameMapper->getByShareTokenWithFiles($shareToken);
    if (!$frame) {
      $this->throttler->registerAttempt(self::BRUTEFORCE_ACTION, $this->request->getRemoteAddress());
      throw new NotFoundException('Unable to find album');
    }

    $service = new PhotoFrameService($this->entryMapper, $this->rootFolder, $frame);
    $frameFile = $service->getCurrentFrameFile();

    return new TemplateResponse(
      appName: Application::APP_ID,
      templateName: 'frame',
      params: ['shareToken' => $shareToken, 'frameFile' => $frameFile, 'showPhotoTimestamp' => $frame->getShowPhotoTimestamp()],
      renderAs: TemplateResponse::RENDER_AS_BLANK
    );
  }

  #[NoCSRFRequired]
  #[PublicPage]
  #[OpenAPI(OpenAPI::SCOPE_IGNORE)]
  #[FrontpageRoute(verb: 'GET', url: '/{shareToken}/image', requirements: ['shareToken' => '[a-zA-Z0-9]+'])]
  public function photoframeImage($shareToken): FileDisplayResponse
  {
    $frame = $this->frameMapper->getByShareTokenWithFiles($shareToken);
    if (!$frame) {
      $this->throttler->registerAttempt(self::BRUTEFORCE_ACTION, $this->request->getRemoteAddress());
      throw new NotFoundException('Unable to find album');
    }

    $service = new PhotoFrameService($this->entryMapper, $this->rootFolder, $frame);
    $frameFile = $service->getCurrentFrameFile();
    $node = $service->getFrameFileNode($frameFile);

    $preview = $this->preview->getPreview($node, 1000, 1000);

    return new FileDisplayResponse($preview, 200, ['X-Photo-Timestamp' => $frameFile->getCapturedAtTimestamp(), 'Expires' => $frameFile->getExpiresHeader(), 'Content-Type' => $frameFile->getMimeType()]);
  }
}
