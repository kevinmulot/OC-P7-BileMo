<?php

namespace App\Service;

use App\Repository\ClientRepository;
use App\Repository\PhoneRepository;
use App\Repository\UserRepository;
use Psr\Cache\InvalidArgumentException;
use Symfony\Contracts\Cache\CacheInterface;

class CacheManager
{
    private $phoneRepo;
    private $userRepo;
    private $clientRepo;

    public function __construct(ClientRepository $clientRepository, UserRepository $userRepository, PhoneRepository $phoneRepository)
    {
        $this->clientRepo = $clientRepository;
        $this->userRepo = $userRepository;
        $this->phoneRepo = $phoneRepository;
    }

    /**
     * @param CacheInterface $cache
     * @param null $id
     * @param $data
     * @throws InvalidArgumentException
     */
    public function deleteCache(CacheInterface $cache, $id = null, $data): void
    {
        if ($id) {
            $cache->delete($data . $id);
        }

        if ($data === 'user') {
            $Count = count($this->userRepo->findAll());
        }

        if ($data === 'client') {
            $Count = count($this->clientRepo->findAll());
        }

        if ($data === 'phone') {
            $Count = count($this->phoneRepo->findAll());
        }

        $itemsPerPage = 10;
        $pageCount = (int)ceil($Count / $itemsPerPage);

        for ($i = 1; $i < $pageCount; $i++) {
            $cache->delete($data . 's_list' . $i);
        }
    }

    /**
     * @param CacheInterface $cache
     * @param null $clientId
     * @throws InvalidArgumentException
     */
    public function deleteCustomerCache(CacheInterface $cache, $clientId = null): void
    {

        $userCount = count($this->userRepo->findAll());

        $itemsPerPage = 10;
        $pageCount = (int)ceil($userCount / $itemsPerPage);

        for ($i = 1; $i < $pageCount; $i++) {
            $cache->delete($clientId . 'users_list' . $i);
        }
    }
}
