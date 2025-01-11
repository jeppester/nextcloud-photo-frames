<?php

declare(strict_types=1);

namespace Service;

use OCA\PhotoFrame\Db\Entry;
use OCA\PhotoFrame\Db\EntryMapper;
use OCA\PhotoFrame\Db\Frame;
use OCA\PhotoFrame\Db\FrameFile;
use OCA\PhotoFrame\Db\FrameMapper;
use OCA\PhotoFrame\Service\PhotoFrameService;
use OCP\Files\IRootFolder;
use PHPUnit\Framework\TestCase;

class PhotoFrameServiceTest extends TestCase
{
  public function testGetEntryExpiryForEntryLifeTimeOneHour()
  {
    $entryMapper = $this->createMock(EntryMapper::class);
    $rootFolder = $this->createMock(IRootFolder::class);

    $frame = new Frame();
    $frame->setEntryLifetime(FrameMapper::ENTRY_LIFETIME_ONE_HOUR);
    $frame->setStartDayAt('06:30');
    $frame->setEndDayAt('20:00');
    $frame->setTimezone(new \DateTimeZone('UTC'));

    $service = new PhotoFrameService($entryMapper, $rootFolder, $frame);

    $entry = new Entry();

    $testTimes = [
      ['01:02', '07:30'],
      ['12:45', '13:30'],
      ['19:31', '24:00'],
    ];

    foreach ($testTimes as $testTime) {
      $entry->setCreatedAt((new \DateTime)->modify($testTime[0]));
      $this->assertEquals($service->getEntryExpiry($entry), (new \DateTime)->modify($testTime[1]));
    }
  }

  public function testGetEntryExpiryForEntryLifeTimeOneDay()
  {
    $entryMapper = $this->createMock(EntryMapper::class);
    $rootFolder = $this->createMock(IRootFolder::class);

    $frame = new Frame();
    $frame->setEntryLifetime(FrameMapper::ENTRY_LIFETIME_ONE_DAY);
    $frame->setStartDayAt('06:30');
    $frame->setEndDayAt('20:00');
    $frame->setTimezone(new \DateTimeZone('UTC'));

    $service = new PhotoFrameService($entryMapper, $rootFolder, $frame);

    $entry = new Entry();

    $testTimes = [
      ['01:02', '24:00'],
      ['12:45', '24:00'],
      ['19:31', '24:00'],
    ];

    foreach ($testTimes as $testTime) {
      $entry->setCreatedAt((new \DateTime)->modify($testTime[0]));
      $this->assertEquals($service->getEntryExpiry($entry), (new \DateTime)->modify($testTime[1]));
    }
  }

  public function testGetEntryExpiryForEntryLifeTimeHalfDay()
  {
    $entryMapper = $this->createMock(EntryMapper::class);
    $rootFolder = $this->createMock(IRootFolder::class);

    $frame = new Frame();
    $frame->setEntryLifetime(FrameMapper::ENTRY_LIFETIME_1_2_DAY);
    $frame->setStartDayAt('06:00');
    $frame->setEndDayAt('20:00');
    $frame->setTimezone(new \DateTimeZone('UTC'));

    $service = new PhotoFrameService($entryMapper, $rootFolder, $frame);

    $entry = new Entry();

    $testTimes = [
      ['02:02', '13:00'],
      ['12:59', '13:00'],
      ['13:01', '24:00'],
      ['19:31', '24:00'],
    ];

    foreach ($testTimes as $testTime) {
      $entry->setCreatedAt((new \DateTime)->modify($testTime[0]));
      $this->assertEquals($service->getEntryExpiry($entry), (new \DateTime)->modify($testTime[1]));
    }
  }

  public function testGetEntryExpiryForEntryLifeTimeThirdDay()
  {
    $entryMapper = $this->createMock(EntryMapper::class);
    $rootFolder = $this->createMock(IRootFolder::class);

    $frame = new Frame();
    $frame->setEntryLifetime(FrameMapper::ENTRY_LIFETIME_1_3_DAY);
    $frame->setStartDayAt('07:00');
    $frame->setEndDayAt('22:00');
    $frame->setTimezone(new \DateTimeZone('UTC'));

    $service = new PhotoFrameService($entryMapper, $rootFolder, $frame);

    $entry = new Entry();

    $testTimes = [
      ['02:02', '12:00'],
      ['11:59', '12:00'],
      ['12:01', '17:00'],
      ['16:59', '17:00'],
      ['17:01', '24:00'],
      ['22:01', '24:00'],
    ];

    foreach ($testTimes as $testTime) {
      $entry->setCreatedAt((new \DateTime)->modify($testTime[0]));
      $this->assertEquals($service->getEntryExpiry($entry), (new \DateTime)->modify($testTime[1]));
    }
  }

  public function testGetEntryExpiryForEntryLifeTimeQuarterDay()
  {
    $entryMapper = $this->createMock(EntryMapper::class);
    $rootFolder = $this->createMock(IRootFolder::class);

    $frame = new Frame();
    $frame->setEntryLifetime(FrameMapper::ENTRY_LIFETIME_1_4_DAY);
    $frame->setStartDayAt('00:00');
    $frame->setEndDayAt('24:00');
    $frame->setTimezone(new \DateTimeZone('UTC'));

    $service = new PhotoFrameService($entryMapper, $rootFolder, $frame);

    $entry = new Entry();

    $testTimes = [
      ['02:02', '06:00'],
      ['05:59', '06:00'],
      ['06:01', '12:00'],
      ['11:59', '12:00'],
      ['12:01', '18:00'],
      ['17:59', '18:00'],
      ['18:01', '24:00'],
      ['23:59', '24:00'],
    ];

    foreach ($testTimes as $testTime) {
      $entry->setCreatedAt((new \DateTime)->modify($testTime[0]));
      $this->assertEquals($service->getEntryExpiry($entry), (new \DateTime)->modify($testTime[1]));
    }
  }

  public function testSortFrameFilesBySelectionMethodLatest()
  {
    $entryMapper = $this->createMock(EntryMapper::class);
    $rootFolder = $this->createMock(IRootFolder::class);

    $frame = new Frame();
    $frame->setSelectionMethod(FrameMapper::SELECTION_METHOD_LATEST);

    $service = new PhotoFrameService($entryMapper, $rootFolder, $frame);

    $frameFiles = [
      new FrameFile(1, "admin", 'image/jpg', 1700025000, 1600025000),
      new FrameFile(1, "admin", 'image/jpg', 1700025000, 1600020000),
      new FrameFile(1, "admin", 'image/jpg', 1700025000, 1600015000),
      new FrameFile(1, "admin", 'image/jpg', 1700020000, 1600020000),
      new FrameFile(1, "admin", 'image/jpg', 1700020000, 1600015000),
      new FrameFile(1, "admin", 'image/jpg', 1700015000, 1600025000),
    ];

    $randomized = $frameFiles;
    shuffle($randomized);
    $this->assertEquals($frameFiles, $service->sortFrameFilesBySelectionMethod($randomized));
  }

  public function testSortFrameFilesBySelectionMethodOldest()
  {
    $entryMapper = $this->createMock(EntryMapper::class);
    $rootFolder = $this->createMock(IRootFolder::class);

    $frame = new Frame();
    $frame->setSelectionMethod(FrameMapper::SELECTION_METHOD_OLDEST);

    $service = new PhotoFrameService($entryMapper, $rootFolder, $frame);

    $frameFiles = [
      new FrameFile(1, "admin", 'image/jpg', 1700015000, 1600025000),
      new FrameFile(1, "admin", 'image/jpg', 1700020000, 1600015000),
      new FrameFile(1, "admin", 'image/jpg', 1700020000, 1600020000),
      new FrameFile(1, "admin", 'image/jpg', 1700025000, 1600015000),
      new FrameFile(1, "admin", 'image/jpg', 1700025000, 1600020000),
      new FrameFile(1, "admin", 'image/jpg', 1700025000, 1600025000),
    ];

    $randomized = $frameFiles;
    shuffle($randomized);
    $this->assertEquals($frameFiles, $service->sortFrameFilesBySelectionMethod($randomized));
  }
}
