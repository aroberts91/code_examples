<?php

namespace App\Domain\Settings\Service;

use App\Domain\Settings\Data\SettingsData;
use App\Domain\Settings\Repository\SettingsRepository;
use phpDocumentor\Reflection\Types\Boolean;
use Psr\Container\ContainerInterface;

/**
 * Settings service
 */
final class SettingsService
{
	/**
	 * @var SettingsRepository
	 */
	private $repository;

	/**
	 * The constructor
	 *
	 * @param ContainerInterface $container The repository
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->repository = new SettingsRepository($container);
	}

	/**
	 * Load an array of all settings
	 *
	 * @return array The settings data
	 */
	public function getSettings(): array
	{
		return $this->repository->getSettings();
	}

	/**
	 * Save settings to db
	 *
	 * @param array $data The settings data
	 *
	 * @return bool The success of save
	 */
	public function saveSettings($data): bool
	{
		return $this->repository->saveSettings($data);
	}

	/**
	 * Flush settings from memcache
	 *
	 * @return bool The success of the flush
	 */
	public function flushSettings(): bool
	{
		return $this->repository->flushSettings();
	}

	/**
	 * Update company logo
	 * @param string The file name
	 *
	 * @return bool Update success
	 */
	public function updateLogo($logo): bool
	{
		$logo = $logo['company_logo'];
		return $this->repository->updateLogo($logo);
	}

	/**
	 * Get headline stats
	 * @return array the data
	 */
	public function getHeadlineStats(): array
	{
		return $this->repository->getHeadlineStats();
	}
}
