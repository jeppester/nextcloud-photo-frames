<?php

declare(strict_types=1);

namespace OCA\PhotoFrame\Controller;

use OCA\PhotoFrame\AppInfo\Application;
use OCA\PhotoFrame\Db\EntryMapper;
use OCA\PhotoFrame\Db\FrameMapper;
use OCA\PhotoFrame\Service\PhotoFrameService;
use OCA\Photos\Service\UserConfigService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCA\Photos\Album\AlbumMapper;
use OCP\Common\Exception\NotFoundException;
use OCP\Files\IRootFolder;
use OCP\IRequest;
use OCP\IPreview;
use OCP\IUserSession;
use OCP\Security\Bruteforce\IThrottler;
use OCP\Util;

/**
 * @psalm-suppress UnusedClass
 */
class PageController extends Controller
{
	private const BRUTEFORCE_ACTION = 'photoframe';
	private EntryMapper $entryMapper;
	private FrameMapper $frameMapper;
	private AlbumMapper $albumMapper;
	private IThrottler $throttler;
	private IRootFolder $rootFolder;
	private UserConfigService $userConfigService;
	private IPreview $preview;
	private IUserSession $userSession;

	public function __construct(
		$appName,
		IRequest $request,
		AlbumMapper $albumMapper,
		EntryMapper $entryMapper,
		FrameMapper $frameMapper,
		IThrottler $throttler,
		IRootFolder $rootFolder,
		IPreview $preview,
		UserConfigService $userConfigService,
		IUserSession $userSession,
	) {
		parent::__construct($appName, $request);
		$this->albumMapper = $albumMapper;
		$this->entryMapper = $entryMapper;
		$this->frameMapper = $frameMapper;
		$this->throttler = $throttler;
		$this->rootFolder = $rootFolder;
		$this->userConfigService = $userConfigService;
		$this->preview = $preview;
		$this->userSession = $userSession;
	}

	#[NoCSRFRequired]
	#[NoAdminRequired]
	#[OpenAPI(OpenAPI::SCOPE_IGNORE)]
	#[FrontpageRoute(verb: 'GET', url: '/')]
	public function index(): TemplateResponse
	{
		$uid = $this->userSession->getUser()->getUID();

		$params = [
			'frames' => $this->frameMapper->getAllByUser($uid),
		];

		Util::addStyle(Application::APP_ID, 'main');

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
		$uid = $this->userSession->getUser()->getUID();

		$params = [
			'albums' => $this->frameMapper->getAvailableAlbums($uid),
		];

		Util::addStyle(Application::APP_ID, 'main');

		return new TemplateResponse(
			appName: Application::APP_ID,
			templateName: 'new',
			renderAs: TemplateResponse::RENDER_AS_USER,
			params: $params,
		);
	}

	#[NoCSRFRequired]
	#[NoAdminRequired]
	#[OpenAPI(OpenAPI::SCOPE_IGNORE)]
	#[FrontpageRoute(verb: 'POST', url: '/')]
	public function create(): RedirectResponse
	{
		$params = $this->request->getParams();
		$this->frameMapper->createFrame(
			$params['name'],
			$this->userSession->getUser()->getUID(),
			(int) $params['album_id'],
			$params['selection_method'],
			$params['entry_lifetime'],
			$params['start_day_at'],
			$params['end_day_at'],
		);

		return new RedirectResponse('/index.php/apps/photoframe');
	}


	#[NoCSRFRequired]
	#[PublicPage]
	#[OpenAPI(OpenAPI::SCOPE_IGNORE)]
	#[FrontpageRoute(verb: 'GET', url: '/{shareToken}', requirements: ['shareToken' => '[a-zA-Z0-9]{64}'])]
	public function photoframe($shareToken): TemplateResponse
	{
		Util::addScript(Application::APP_ID, 'frame');

		return new TemplateResponse(
			appName: Application::APP_ID,
			templateName: 'frame',
			params: ['shareToken' => $shareToken],
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

		return new FileDisplayResponse($preview, 200, ['Content-Type' => $frameFile->getMimeType()]);
	}
}
