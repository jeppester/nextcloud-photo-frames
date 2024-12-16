<?php

declare(strict_types=1);

namespace OCA\PhotoFrame\Controller;

use OCA\PhotoFrame\AppInfo\Application;
use OCA\PhotoFrame\Db\EntryMapper;
use OCA\Photos\Service\UserConfigService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\FrontpageRoute;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\OpenAPI;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Http\TemplateResponse;
use OCA\Photos\Album\AlbumMapper;
use OCP\Common\Exception\NotFoundException;
use OCP\Files\IRootFolder;
use OCP\IRequest;
use OCP\IPreview;
use OCP\Security\Bruteforce\IThrottler;

/**
 * @psalm-suppress UnusedClass
 */
class PageController extends Controller
{
	private const BRUTEFORCE_ACTION = 'photoframe';
	private EntryMapper $entryMapper;
	private AlbumMapper $albumMapper;
	private IThrottler $throttler;
	private IRootFolder $rootFolder;
	private UserConfigService $userConfigService;
	private IPreview $preview;

	public function __construct(
		$appName,
		IRequest $request,
		AlbumMapper $albumMapper,
		EntryMapper $entryMapper,
		IThrottler $throttler,
		IRootFolder $rootFolder,
		IPreview $preview,
		UserConfigService $userConfigService,
	) {
		parent::__construct($appName, $request);
		$this->albumMapper = $albumMapper;
		$this->entryMapper = $entryMapper;
		$this->throttler = $throttler;
		$this->rootFolder = $rootFolder;
		$this->userConfigService = $userConfigService;
		$this->preview = $preview;
	}

	#[NoCSRFRequired]
	#[PublicPage]
	#[OpenAPI(OpenAPI::SCOPE_IGNORE)]
	#[FrontpageRoute(verb: 'GET', url: '/')]
	public function index(): TemplateResponse
	{
		return new TemplateResponse(
			appName: Application::APP_ID,
			templateName: 'index',
			renderAs: TemplateResponse::RENDER_AS_BLANK
		);
	}

	#[NoCSRFRequired]
	#[PublicPage]
	#[OpenAPI(OpenAPI::SCOPE_IGNORE)]
	#[FrontpageRoute(verb: 'GET', url: '/{shareToken}', requirements: ['shareToken' => '[a-zA-Z0-9]+'])]
	public function photoframe(): TemplateResponse
	{
		return new TemplateResponse(
			appName: Application::APP_ID,
			templateName: 'index',
			renderAs: TemplateResponse::RENDER_AS_BLANK
		);
	}

	#[NoCSRFRequired]
	#[PublicPage]
	#[OpenAPI(OpenAPI::SCOPE_IGNORE)]
	#[FrontpageRoute(verb: 'GET', url: '/{shareToken}/image', requirements: ['shareToken' => '[a-zA-Z0-9]+'])]
	public function photoframeImage($shareToken): FileDisplayResponse
	{
		$albums = $this->albumMapper->getSharedAlbumsForCollaboratorWithFiles($shareToken, AlbumMapper::TYPE_LINK);

		if (count($albums) !== 1) {
			$this->throttler->registerAttempt(self::BRUTEFORCE_ACTION, $this->request->getRemoteAddress());
			throw new NotFoundException('Unable to find album');
		}

		$album = $albums[0];
		$album->getAlbum();

		$usedPhotoIds = $this->entryMapper->getUsedPhotoIds($shareToken);

		$photoIds = $album->getFileIds();

		$unusedIds = array_diff($photoIds, $usedPhotoIds);
		$chosenId = $unusedIds[array_rand($unusedIds)];

		$albumFile = null;
		foreach ($album->getFiles() as $photo) {
			if ($photo->getFileId() === $chosenId) {
				$albumFile = $photo;
				break;
			}
		}

		$nodes = $this->rootFolder
			->getUserFolder($albumFile->getOwner() ?: $album->getAlbum()->getUserId())
			->getById($albumFile->getFileId());

		$node = current($nodes);
		if (!$node) {
			throw new NotFoundException('Photo not found user');
		}

		$preview = $this->preview->getPreview($node, 1000, 1000);

		return new FileDisplayResponse($preview, 200, ['Content-Type' => $albumFile->getMimeType()]);
	}
}
