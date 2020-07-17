<?php

namespace App\Domain\Preferences\Service;

use App\Domain\Preferences\Repository\PreferencesRepository;
use Psr\Container\ContainerInterface;

/**
 * Preferences Service (controller)
 */
final class PreferencesService
{
	/**
	 * @var PreferencesRepository $repository
	 */
	private $repository;

	/**
	 * The constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->repository = new PreferencesRepository($container);
	}

	public function getPreferences($data)
	{
		return $this->repository->getPreferences($data);
	}

	public function addUpdatePreferences($data)
	{
		return $this->repository->addUpdatePreferences($data);
	}

	public function getPreferenceData($data)
	{
		return $this->repository->getPreferenceData($data);
	}

	public function getBookingPreferences($data)
	{
		return $this->repository->getBookingPreferences($data);
	}

	public function getMatchedStaff($data)
	{
		return $this->repository->getMatchedStaff($data);
	}

	public function getVisitTypePreferences($data)
	{
		return $this->repository->getVisitTypePreferences($data);
	}

	public function getItem($data)
	{
		return $this->repository->getItem($data);
	}

	public function getItemsByType($data)
	{
		return $this->repository->getItemsByType($data);
	}

	public function addUpdatePreferenceVisitAssign($data)
	{
		return $this->repository->addUpdatePreferenceVisitAssign($data);
	}

	public function removeStaffPreferences($data)
	{
		return $this->repository->removeStaffPreferences($data);
	}

	public function removePreferenceVisitAssign($data)
	{
		return $this->repository->removePreferenceVisitAssign($data);
	}
}
