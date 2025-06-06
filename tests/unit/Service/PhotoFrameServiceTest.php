<?php

declare(strict_types=1);

namespace Service;

use OCA\PhotoFrames\Db\Entry;
use OCA\PhotoFrames\Db\EntryMapper;
use OCA\PhotoFrames\Db\Frame;
use OCA\PhotoFrames\Db\FrameFile;
use OCA\PhotoFrames\Db\FrameMapper;
use OCA\PhotoFrames\Service\PhotoFrameService;
use OCP\Files\IRootFolder;
use PHPUnit\Framework\TestCase;

class PhotoFrameServiceTest extends TestCase
{
  public function testGetEntryExpiry1PerHour()
  {
    $entryMapper = $this->createMock(EntryMapper::class);
    $rootFolder = $this->createMock(IRootFolder::class);

    $frame = new Frame();
    $frame->setRotationUnit(FrameMapper::ROTATION_UNIT_HOUR);
    $frame->setRotationsPerUnit(1);
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
      $this->assertEquals((new \DateTime)->modify($testTime[1]), $service->getEntryExpiry($entry));
    }
  }

  public function testGetEntryExpiry2PerHour()
  {
    $entryMapper = $this->createMock(EntryMapper::class);
    $rootFolder = $this->createMock(IRootFolder::class);

    $frame = new Frame();
    $frame->setRotationUnit(FrameMapper::ROTATION_UNIT_HOUR);
    $frame->setRotationsPerUnit(2);
    $frame->setStartDayAt('06:30');
    $frame->setEndDayAt('20:00');
    $frame->setTimezone(new \DateTimeZone('UTC'));

    $service = new PhotoFrameService($entryMapper, $rootFolder, $frame);

    $entry = new Entry();

    $testTimes = [
      ['01:02', '07:00'],
      ['07:02', '07:30'],
      ['12:45', '13:00'],
      ['19:31', '24:00'],
    ];

    foreach ($testTimes as $testTime) {
      $entry->setCreatedAt((new \DateTime)->modify($testTime[0]));
      $this->assertEquals((new \DateTime)->modify($testTime[1]), $service->getEntryExpiry($entry));
    }
  }

  public function testGetEntryExpiry6PerHour()
  {
    $entryMapper = $this->createMock(EntryMapper::class);
    $rootFolder = $this->createMock(IRootFolder::class);

    $frame = new Frame();
    $frame->setRotationUnit(FrameMapper::ROTATION_UNIT_HOUR);
    $frame->setRotationsPerUnit(6);
    $frame->setStartDayAt('06:55');
    $frame->setEndDayAt('20:00');
    $frame->setTimezone(new \DateTimeZone('UTC'));

    $service = new PhotoFrameService($entryMapper, $rootFolder, $frame);

    $entry = new Entry();

    $testTimes = [
      ['06:50', '07:05'],
      ['07:09', '07:15'],
      ['12:45', '12:55'],
      ['19:54', '19:55'],
      ['19:55', '24:00'],
    ];

    foreach ($testTimes as $testTime) {
      $entry->setCreatedAt((new \DateTime)->modify($testTime[0]));
      $this->assertEquals((new \DateTime)->modify($testTime[1]), $service->getEntryExpiry($entry));
    }
  }

  public function testGetEntryExpiry1PerDay()
  {
    $entryMapper = $this->createMock(EntryMapper::class);
    $rootFolder = $this->createMock(IRootFolder::class);

    $frame = new Frame();
    $frame->setRotationUnit(FrameMapper::ROTATION_UNIT_DAY);
    $frame->setRotationsPerUnit(1);
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
      $this->assertEquals((new \DateTime)->modify($testTime[1]), $service->getEntryExpiry($entry));
    }
  }

  public function testGetEntryExpiry2PerDay()
  {
    $entryMapper = $this->createMock(EntryMapper::class);
    $rootFolder = $this->createMock(IRootFolder::class);

    $frame = new Frame();
    $frame->setRotationUnit(FrameMapper::ROTATION_UNIT_DAY);
    $frame->setRotationsPerUnit(2);
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
      $this->assertEquals((new \DateTime)->modify($testTime[1]), $service->getEntryExpiry($entry));
    }
  }

  public function testGetEntryExpiry3PerDay()
  {
    $entryMapper = $this->createMock(EntryMapper::class);
    $rootFolder = $this->createMock(IRootFolder::class);

    $frame = new Frame();
    $frame->setRotationUnit(FrameMapper::ROTATION_UNIT_DAY);
    $frame->setRotationsPerUnit(3);
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
      $this->assertEquals((new \DateTime)->modify($testTime[1]), $service->getEntryExpiry($entry));
    }
  }

  public function testGetEntryExpiry4PerDay()
  {
    $entryMapper = $this->createMock(EntryMapper::class);
    $rootFolder = $this->createMock(IRootFolder::class);

    $frame = new Frame();
    $frame->setRotationUnit(FrameMapper::ROTATION_UNIT_DAY);
    $frame->setRotationsPerUnit(4);
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
      $this->assertEquals((new \DateTime)->modify($testTime[1]), $service->getEntryExpiry($entry));
    }
  }

  public function testGetEntryExpiry3PerMinute()
  {
    $entryMapper = $this->createMock(EntryMapper::class);
    $rootFolder = $this->createMock(IRootFolder::class);

    $frame = new Frame();
    $frame->setRotationUnit(FrameMapper::ROTATION_UNIT_MINUTE);
    $frame->setRotationsPerUnit(3);
    $frame->setStartDayAt('06:31');
    $frame->setEndDayAt('08:00');
    $frame->setTimezone(new \DateTimeZone('UTC'));

    $service = new PhotoFrameService($entryMapper, $rootFolder, $frame);

    $entry = new Entry();

    $testTimes = [
      ['01:02:00', '06:31:20'],
      ['06:31:20', '06:31:40'],
      ['07:59:45', '24:00'],
    ];

    foreach ($testTimes as $testTime) {
      $entry->setCreatedAt((new \DateTime)->modify($testTime[0]));
      $this->assertEquals((new \DateTime)->modify($testTime[1]), $service->getEntryExpiry($entry));
    }
  }

  public function testMidnightEndDayAt()
  {
    $entryMapper = $this->createMock(EntryMapper::class);
    $rootFolder = $this->createMock(IRootFolder::class);

    $frame = new Frame();
    $frame->setRotationUnit(FrameMapper::ROTATION_UNIT_DAY);
    $frame->setRotationsPerUnit(2);
    $frame->setStartDayAt('00:00');
    $frame->setEndDayAt('00:00');
    $frame->setTimezone(new \DateTimeZone('UTC'));

    $service = new PhotoFrameService($entryMapper, $rootFolder, $frame);

    $entry = new Entry();

    $testTimes = [
      ['00:00:20', '12:00'],
      ['15:00:23', '24:00'],
    ];

    foreach ($testTimes as $testTime) {
      $entry->setCreatedAt((new \DateTime)->modify($testTime[0]));
      $this->assertEquals((new \DateTime)->modify($testTime[1]), $service->getEntryExpiry($entry));
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
